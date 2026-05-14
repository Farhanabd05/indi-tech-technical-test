<?php

use App\Models\Ticket;
use App\Models\Category;
use App\Models\Priority;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Enums\TicketStatus;
use App\Services\TicketStatusService;
use Carbon\Carbon;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Ticket Status Transition', function () {
    beforeEach(function () {
        // Seed roles
        Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator']);
        Role::firstOrCreate(['slug' => 'supervisor'], ['name' => 'Supervisor']);
        Role::firstOrCreate(['slug' => 'agent'], ['name' => 'Agent']);
        Role::firstOrCreate(['slug' => 'customer'], ['name' => 'Customer']);

        // Seed category and priority
        $this->category = Category::create(['name' => 'Technical Issue']);
        $this->priority = Priority::create(['name' => 'Medium', 'level' => 2]);

        $this->statusService = new TicketStatusService();
    });

    afterEach(function () {
        Carbon::setTestNow();
    });

    describe('Valid status transitions', function () {
        it('allows open to closed transition', function () {
            $ticket = createTicket(['status' => TicketStatus::OPEN]);
            $admin = createUserWithRole('administrator');

            $response = $this->actingAs($admin)->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::CLOSED->value,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'status' => TicketStatus::CLOSED->value,
            ]);
        });

        it('allows assigned to in_progress transition', function () {
            $agent = createUserWithRole('agent');
            $ticket = createTicket(['status' => TicketStatus::ASSIGNED, 'assigned_agent_id' => $agent->id]);

            $response = $this->actingAs($agent)->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::IN_PROGRESS->value,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'status' => TicketStatus::IN_PROGRESS->value,
            ]);
        });

        it('allows supervisor to change status for tickets assigned to agents in their team', function () {
            $team = Team::create(['name' => 'Supervisor Status Team']);
            $supervisor = createUserWithRole('supervisor');
            $supervisor->update(['team_id' => $team->id]);

            $agent = createUserWithRole('agent');
            $agent->update(['team_id' => $team->id]);

            $ticket = createTicket([
                'status' => TicketStatus::ASSIGNED,
                'assigned_agent_id' => $agent->id,
            ]);

            $response = $this->actingAs($supervisor)->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::IN_PROGRESS->value,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'status' => TicketStatus::IN_PROGRESS->value,
            ]);
        });

        it('allows in_progress to resolved transition', function () {
            $agent = createUserWithRole('agent');
            $ticket = createTicket(['status' => TicketStatus::IN_PROGRESS, 'assigned_agent_id' => $agent->id]);

            $response = $this->actingAs($agent)->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::RESOLVED->value,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'status' => TicketStatus::RESOLVED->value,
            ]);
        });

        it('allows resolved to closed transition', function () {
            $ticket = createTicket(['status' => TicketStatus::RESOLVED]);
            $admin = createUserWithRole('administrator');

            $response = $this->actingAs($admin)->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::CLOSED->value,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'status' => TicketStatus::CLOSED->value,
            ]);
        });

        it('allows resolved to reopened transition', function () {
            $customer = createUserWithRole('customer');
            $ticket = createTicket(['status' => TicketStatus::RESOLVED, 'created_by' => $customer->id]);

            $response = $this->actingAs($customer)->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::REOPENED->value,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'status' => TicketStatus::REOPENED->value,
            ]);
        });

        it('allows closed to reopened transition', function () {
            $customer = createUserWithRole('customer');
            $ticket = createTicket(['status' => TicketStatus::CLOSED, 'created_by' => $customer->id]);

            $response = $this->actingAs($customer)->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::REOPENED->value,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'status' => TicketStatus::REOPENED->value,
            ]);
        });
    });

    describe('Invalid status transitions', function () {
        it('denies open to in_progress transition', function () {
            $ticket = createTicket(['status' => TicketStatus::OPEN]);
            $admin = createUserWithRole('administrator');

            $response = $this->actingAs($admin)->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::IN_PROGRESS->value,
            ]);

            $response->assertSessionHasErrors('status');
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'status' => TicketStatus::OPEN->value,
            ]);
        });

        it('denies assigned to open transition', function () {
            $ticket = createTicket(['status' => TicketStatus::ASSIGNED]);
            $admin = createUserWithRole('administrator');

            $response = $this->actingAs($admin)->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::OPEN->value,
            ]);

            $response->assertSessionHasErrors('status');
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'status' => TicketStatus::ASSIGNED->value,
            ]);
        });

        it('denies resolved to in_progress transition', function () {
            $ticket = createTicket(['status' => TicketStatus::RESOLVED]);
            $admin = createUserWithRole('administrator');

            $response = $this->actingAs($admin)->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::IN_PROGRESS->value,
            ]);

            $response->assertSessionHasErrors('status');
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'status' => TicketStatus::RESOLVED->value,
            ]);
        });

        it('denies closed to assigned transition', function () {
            $ticket = createTicket(['status' => TicketStatus::CLOSED]);
            $admin = createUserWithRole('administrator');

            $response = $this->actingAs($admin)->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::ASSIGNED->value,
            ]);

            $response->assertSessionHasErrors('status');
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'status' => TicketStatus::CLOSED->value,
            ]);
        });

        it('denies closed to resolved transition', function () {
            $ticket = createTicket(['status' => TicketStatus::CLOSED]);
            $admin = createUserWithRole('administrator');

            $response = $this->actingAs($admin)->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::RESOLVED->value,
            ]);

            $response->assertSessionHasErrors('status');
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'status' => TicketStatus::CLOSED->value,
            ]);
        });

        it('denies in_progress to open transition', function () {
            $ticket = createTicket(['status' => TicketStatus::IN_PROGRESS]);
            $admin = createUserWithRole('administrator');

            $response = $this->actingAs($admin)->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::OPEN->value,
            ]);

            $response->assertSessionHasErrors('status');
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'status' => TicketStatus::IN_PROGRESS->value,
            ]);
        });

        it('prevents supervisor from changing status for tickets outside their team', function () {
            $supervisorTeam = Team::create(['name' => 'Supervisor Team']);
            $otherTeam = Team::create(['name' => 'Other Team']);

            $supervisor = createUserWithRole('supervisor');
            $supervisor->update(['team_id' => $supervisorTeam->id]);

            $agent = createUserWithRole('agent');
            $agent->update(['team_id' => $otherTeam->id]);

            $ticket = createTicket([
                'status' => TicketStatus::ASSIGNED,
                'assigned_agent_id' => $agent->id,
            ]);

            $response = $this->actingAs($supervisor)->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::IN_PROGRESS->value,
            ]);

            $response->assertStatus(403);
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'status' => TicketStatus::ASSIGNED->value,
            ]);
        });
    });

    describe('Status transition service validation', function () {
        it('validates open can transition to assigned', function () {
            expect($this->statusService->isValidTransition(TicketStatus::OPEN, TicketStatus::ASSIGNED))
                ->toBeTrue();
        });

        it('validates open cannot transition to in_progress', function () {
            expect($this->statusService->isValidTransition(TicketStatus::OPEN, TicketStatus::IN_PROGRESS))
                ->toBeFalse();
        });

        it('validates assigned cannot transition to open', function () {
            expect($this->statusService->isValidTransition(TicketStatus::ASSIGNED, TicketStatus::OPEN))
                ->toBeFalse();
        });

        it('validates resolved cannot transition to in_progress', function () {
            expect($this->statusService->isValidTransition(TicketStatus::RESOLVED, TicketStatus::IN_PROGRESS))
                ->toBeFalse();
        });

        it('validates closed cannot transition to assigned', function () {
            expect($this->statusService->isValidTransition(TicketStatus::CLOSED, TicketStatus::ASSIGNED))
                ->toBeFalse();
        });
    });

    describe('SLA lifecycle', function () {
        it('pauses SLA when ticket waits for customer', function () {
            $now = Carbon::parse('2026-05-14 10:00:00');
            Carbon::setTestNow($now);

            $agent = createUserWithRole('agent');
            $ticket = createTicket([
                'status' => TicketStatus::IN_PROGRESS,
                'assigned_agent_id' => $agent->id,
            ]);

            $response = $this->actingAs($agent)->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::WAITING_FOR_CUSTOMER->value,
            ]);

            $response->assertRedirect();

            $ticket = $ticket->fresh();

            expect($ticket->sla_paused_at?->equalTo($now))->toBeTrue()
                ->and($ticket->total_paused_duration_minutes)->toBe(0);
        });

        it('resumes SLA and shifts due date after waiting for customer', function () {
            $pausedAt = Carbon::parse('2026-05-14 10:00:00');
            $now = Carbon::parse('2026-05-14 10:45:00');
            $dueAt = Carbon::parse('2026-05-14 12:00:00');
            Carbon::setTestNow($now);

            $agent = createUserWithRole('agent');
            $ticket = createTicket([
                'status' => TicketStatus::WAITING_FOR_CUSTOMER,
                'assigned_agent_id' => $agent->id,
                'due_at' => $dueAt,
                'sla_paused_at' => $pausedAt,
                'total_paused_duration_minutes' => 10,
            ]);

            $response = $this->actingAs($agent)->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::IN_PROGRESS->value,
            ]);

            $response->assertRedirect();

            $ticket = $ticket->fresh();

            expect($ticket->sla_paused_at)->toBeNull()
                ->and($ticket->total_paused_duration_minutes)->toBe(55)
                ->and($ticket->due_at?->equalTo($dueAt->copy()->addMinutes(45)))->toBeTrue();
        });

        it('restarts SLA due date when ticket is reopened', function () {
            $now = Carbon::parse('2026-05-14 10:00:00');
            Carbon::setTestNow($now);

            $customer = createUserWithRole('customer');
            $ticket = createTicket([
                'status' => TicketStatus::RESOLVED,
                'created_by' => $customer->id,
                'due_at' => $now->copy()->subDay(),
                'resolved_at' => $now->copy()->subHour(),
                'closed_at' => $now->copy()->subMinutes(30),
                'sla_paused_at' => $now->copy()->subHours(2),
                'total_paused_duration_minutes' => 120,
                'overdue_notified_at' => $now->copy()->subHour(),
            ]);

            $response = $this->actingAs($customer)->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::REOPENED->value,
            ]);

            $response->assertRedirect();

            $ticket = $ticket->fresh();

            expect($ticket->due_at?->equalTo($now->copy()->addHours(72)))->toBeTrue()
                ->and($ticket->resolved_at)->toBeNull()
                ->and($ticket->closed_at)->toBeNull()
                ->and($ticket->sla_paused_at)->toBeNull()
                ->and($ticket->total_paused_duration_minutes)->toBe(0)
                ->and($ticket->overdue_notified_at)->toBeNull();
        });
    });
});
