<?php

use App\Enums\ActivityLogAction;
use App\Enums\TicketStatus;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Priority;
use App\Models\Role;
use App\Models\Team;
use App\Models\Ticket;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Dashboard and activity log reporting', function () {
    beforeEach(function () {
        Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator']);
        Role::firstOrCreate(['slug' => 'supervisor'], ['name' => 'Supervisor']);
        Role::firstOrCreate(['slug' => 'agent'], ['name' => 'Agent']);
        Role::firstOrCreate(['slug' => 'customer'], ['name' => 'Customer']);

        $this->category = Category::create(['name' => 'Technical Issue']);
        $this->priority = Priority::create(['name' => 'Medium', 'level' => 2]);
    });

    it('renders admin dashboard with database aggregated average resolution minutes', function () {
        $admin = createUserWithRole('administrator');
        $customer = createUserWithRole('customer');

        createDashboardTicket($this->category->id, $this->priority->id, $customer->id, [
            'ticket_number' => 'TCK-' . date('Y') . '-700001',
            'status' => TicketStatus::RESOLVED,
            'created_at' => now()->subMinutes(60),
            'resolved_at' => now(),
        ]);

        createDashboardTicket($this->category->id, $this->priority->id, $customer->id, [
            'ticket_number' => 'TCK-' . date('Y') . '-700002',
            'status' => TicketStatus::RESOLVED,
            'created_at' => now()->subMinutes(180),
            'resolved_at' => now(),
            'total_paused_duration_minutes' => 60,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard.admin'));

        $response->assertStatus(200);
        $response->assertSee('Average Resolution Time: 90 minutes');
    });

    it('renders supervisor dashboard with per-agent database aggregated resolution minutes', function () {
        $team = Team::create(['name' => 'Reporting Team']);
        $supervisor = createUserWithRole('supervisor');
        $supervisor->update(['team_id' => $team->id]);

        $agent = createUserWithRole('agent');
        $agent->update(['team_id' => $team->id]);

        $customer = createUserWithRole('customer');

        createDashboardTicket($this->category->id, $this->priority->id, $customer->id, [
            'ticket_number' => 'TCK-' . date('Y') . '-700003',
            'status' => TicketStatus::IN_PROGRESS,
            'assigned_agent_id' => $agent->id,
        ]);

        createDashboardTicket($this->category->id, $this->priority->id, $customer->id, [
            'ticket_number' => 'TCK-' . date('Y') . '-700004',
            'status' => TicketStatus::RESOLVED,
            'assigned_agent_id' => $agent->id,
            'created_at' => now()->subMinutes(90),
            'resolved_at' => now(),
            'total_paused_duration_minutes' => 30,
        ]);

        $response = $this->actingAs($supervisor)->get(route('dashboard.supervisor'));

        $response->assertStatus(200);
        $response->assertSee((string) $agent->name);
        $response->assertSee('60');
    });

    it('renders activity log system actor and current action labels safely', function () {
        $admin = createUserWithRole('administrator');
        $customer = createUserWithRole('customer');
        $ticket = createDashboardTicket($this->category->id, $this->priority->id, $customer->id, [
            'ticket_number' => 'TCK-' . date('Y') . '-700005',
        ]);

        ActivityLog::create([
            'ticket_id' => $ticket->id,
            'user_id' => null,
            'action' => ActivityLogAction::SLA_OVERDUE->value,
        ]);

        ActivityLog::create([
            'ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'action' => ActivityLogAction::DELETE_ATTACHMENT->value,
            'old_value' => 'evidence.pdf',
        ]);

        $response = $this->actingAs($admin)->get(route('activity_logs.index'));

        $response->assertStatus(200);
        $response->assertSee('System');
        $response->assertSee('SLA tiket telah melewati batas waktu');
        $response->assertSee('Lampiran evidence.pdf telah dihapus');
    });
});

function createDashboardTicket(int $categoryId, int $priorityId, int $customerId, array $overrides = []): Ticket
{
    $ticket = Ticket::create([
        'ticket_number' => $overrides['ticket_number'] ?? sprintf('TCK-%s-%06d', date('Y'), Ticket::count() + 1),
        'title' => $overrides['title'] ?? 'Dashboard Reporting Ticket',
        'description' => $overrides['description'] ?? 'Ticket used for dashboard reporting tests',
        'category_id' => $categoryId,
        'priority_id' => $priorityId,
        'status' => $overrides['status'] ?? TicketStatus::OPEN,
        'created_by' => $customerId,
        'assigned_agent_id' => $overrides['assigned_agent_id'] ?? null,
        'due_at' => $overrides['due_at'] ?? now()->addDay(),
        'total_paused_duration_minutes' => $overrides['total_paused_duration_minutes'] ?? 0,
    ]);

    $ticket->forceFill([
        'created_at' => $overrides['created_at'] ?? $ticket->created_at,
        'resolved_at' => $overrides['resolved_at'] ?? null,
        'closed_at' => $overrides['closed_at'] ?? null,
    ])->save();

    return $ticket;
}
