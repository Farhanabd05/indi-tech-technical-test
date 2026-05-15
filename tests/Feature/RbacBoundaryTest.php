<?php

use App\Enums\TicketStatus;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Priority;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;

beforeEach(function () {
    $this->category = Category::create(['name' => 'RBAC Category']);
    $this->priority = Priority::create(['name' => 'RBAC Priority', 'level' => 2]);
});

function rbacUser(string $role, ?Team $team = null): User
{
    return User::factory()->create([
        'role_id' => getRoleBySlug($role)->id,
        'team_id' => $team?->id,
    ]);
}

function rbacTicket(array $attributes = []): Ticket
{
    static $sequence = 1;

    $customer = $attributes['creator'] ?? rbacUser('customer');
    unset($attributes['creator']);

    return Ticket::create(array_merge([
        'ticket_number' => sprintf('RBAC-%s-%04d', now()->format('YmdHis'), $sequence++),
        'title' => 'RBAC Ticket ' . $sequence,
        'description' => 'RBAC boundary test ticket',
        'category_id' => Category::first()->id,
        'priority_id' => Priority::first()->id,
        'status' => TicketStatus::OPEN,
        'created_by' => $customer->id,
        'due_at' => now()->addDays(3),
    ], $attributes));
}

it('prevents customers from viewing tickets created by other users', function () {
    $owner = rbacUser('customer');
    $otherCustomer = rbacUser('customer');

    $ownTicket = rbacTicket([
        'creator' => $owner,
        'title' => 'Visible customer owned ticket',
    ]);

    $otherTicket = rbacTicket([
        'creator' => $otherCustomer,
        'title' => 'Hidden other customer ticket',
    ]);

    $this->actingAs($owner)
        ->get(route('tickets.index'))
        ->assertOk()
        ->assertSee($ownTicket->title)
        ->assertDontSee($otherTicket->title);

    $this->actingAs($owner)
        ->get(route('tickets.show', $otherTicket))
        ->assertForbidden();
});

it('prevents agents from viewing tickets assigned to another agent', function () {
    $agent = rbacUser('agent');
    $otherAgent = rbacUser('agent');

    $assignedTicket = rbacTicket([
        'title' => 'Visible assigned agent ticket',
        'status' => TicketStatus::ASSIGNED,
        'assigned_agent_id' => $agent->id,
    ]);

    $otherTicket = rbacTicket([
        'title' => 'Hidden other agent ticket',
        'status' => TicketStatus::ASSIGNED,
        'assigned_agent_id' => $otherAgent->id,
    ]);

    $this->actingAs($agent)
        ->get(route('tickets.index'))
        ->assertOk()
        ->assertSee($assignedTicket->title)
        ->assertDontSee($otherTicket->title);

    $this->actingAs($agent)
        ->get(route('tickets.show', $otherTicket))
        ->assertForbidden();
});

it('enforces supervisor team visibility boundaries', function () {
    $teamOne = Team::factory()->create();
    $teamTwo = Team::factory()->create();
    $supervisor = rbacUser('supervisor', $teamOne);
    $sameTeamAgent = rbacUser('agent', $teamOne);
    $otherTeamAgent = rbacUser('agent', $teamTwo);

    $sameTeamTicket = rbacTicket([
        'title' => 'Visible same team ticket',
        'status' => TicketStatus::ASSIGNED,
        'assigned_agent_id' => $sameTeamAgent->id,
    ]);

    $crossTeamTicket = rbacTicket([
        'title' => 'Hidden cross team ticket',
        'status' => TicketStatus::ASSIGNED,
        'assigned_agent_id' => $otherTeamAgent->id,
    ]);

    $this->actingAs($supervisor)
        ->get(route('tickets.index'))
        ->assertOk()
        ->assertSee($sameTeamTicket->title)
        ->assertDontSee($crossTeamTicket->title);

    $this->actingAs($supervisor)
        ->get(route('tickets.show', $crossTeamTicket))
        ->assertForbidden();
});

it('blocks supervisors from assigning tickets to agents outside their team', function () {
    $teamOne = Team::factory()->create();
    $teamTwo = Team::factory()->create();
    $supervisor = rbacUser('supervisor', $teamOne);
    $sameTeamAgent = rbacUser('agent', $teamOne);
    $otherTeamAgent = rbacUser('agent', $teamTwo);

    $ticket = rbacTicket([
        'status' => TicketStatus::ASSIGNED,
        'assigned_agent_id' => $sameTeamAgent->id,
    ]);

    $this->actingAs($supervisor)
        ->post(route('tickets.assign', $ticket), [
            'assigned_agent_id' => $otherTeamAgent->id,
        ])
        ->assertForbidden();

    expect($ticket->fresh()->assigned_agent_id)->toBe($sameTeamAgent->id);
});

it('allows supervisors to assign tickets to agents inside their team', function () {
    $team = Team::factory()->create();
    $supervisor = rbacUser('supervisor', $team);
    $firstAgent = rbacUser('agent', $team);
    $secondAgent = rbacUser('agent', $team);

    $ticket = rbacTicket([
        'status' => TicketStatus::ASSIGNED,
        'assigned_agent_id' => $firstAgent->id,
    ]);

    $this->actingAs($supervisor)
        ->post(route('tickets.assign', $ticket), [
            'assigned_agent_id' => $secondAgent->id,
        ])
        ->assertRedirect();

    expect($ticket->fresh()->assigned_agent_id)->toBe($secondAgent->id);
});

it('blocks agents and customers from assigning tickets', function () {
    $agent = rbacUser('agent');
    $customer = rbacUser('customer');
    $targetAgent = rbacUser('agent');

    $ticket = rbacTicket([
        'creator' => $customer,
        'status' => TicketStatus::ASSIGNED,
        'assigned_agent_id' => $agent->id,
    ]);

    $this->actingAs($agent)
        ->post(route('tickets.assign', $ticket), [
            'assigned_agent_id' => $targetAgent->id,
        ])
        ->assertForbidden();

    $this->actingAs($customer)
        ->post(route('tickets.assign', $ticket), [
            'assigned_agent_id' => $targetAgent->id,
        ])
        ->assertForbidden();

    expect($ticket->fresh()->assigned_agent_id)->toBe($agent->id);
});

it('allows an assigned agent to make a valid status transition', function () {
    $agent = rbacUser('agent');
    $ticket = rbacTicket([
        'status' => TicketStatus::ASSIGNED,
        'assigned_agent_id' => $agent->id,
    ]);

    $this->actingAs($agent)
        ->patch(route('tickets.status.update', $ticket), [
            'status' => TicketStatus::IN_PROGRESS->value,
        ])
        ->assertRedirect();

    expect($ticket->fresh()->status)->toBe(TicketStatus::IN_PROGRESS);
});

it('blocks agents from changing status on tickets assigned to another agent', function () {
    $agent = rbacUser('agent');
    $otherAgent = rbacUser('agent');
    $ticket = rbacTicket([
        'status' => TicketStatus::ASSIGNED,
        'assigned_agent_id' => $otherAgent->id,
    ]);

    $this->actingAs($agent)
        ->patch(route('tickets.status.update', $ticket), [
            'status' => TicketStatus::IN_PROGRESS->value,
        ])
        ->assertForbidden();

    expect($ticket->fresh()->status)->toBe(TicketStatus::ASSIGNED);
});

it('rejects invalid status transitions without changing the ticket', function () {
    $agent = rbacUser('agent');
    $ticket = rbacTicket([
        'status' => TicketStatus::IN_PROGRESS,
        'assigned_agent_id' => $agent->id,
    ]);

    $this->actingAs($agent)
        ->patch(route('tickets.status.update', $ticket), [
            'status' => TicketStatus::ASSIGNED->value,
        ])
        ->assertSessionHasErrors('status');

    expect($ticket->fresh()->status)->toBe(TicketStatus::IN_PROGRESS);
});

it('hides internal notes from customers while showing public comments', function () {
    $agent = rbacUser('agent');
    $customer = rbacUser('customer');
    $ticket = rbacTicket([
        'creator' => $customer,
        'status' => TicketStatus::ASSIGNED,
        'assigned_agent_id' => $agent->id,
    ]);

    Comment::create([
        'ticket_id' => $ticket->id,
        'user_id' => $agent->id,
        'body' => 'RBAC secret internal note',
        'is_internal' => true,
    ]);

    Comment::create([
        'ticket_id' => $ticket->id,
        'user_id' => $agent->id,
        'body' => 'RBAC public customer update',
        'is_internal' => false,
    ]);

    $this->actingAs($customer)
        ->get(route('tickets.show', $ticket))
        ->assertOk()
        ->assertSee('RBAC public customer update')
        ->assertDontSee('RBAC secret internal note');
});

it('restricts activity logs to administrators and supervisors', function () {
    $admin = rbacUser('administrator');
    $supervisor = rbacUser('supervisor', Team::factory()->create());
    $agent = rbacUser('agent');
    $customer = rbacUser('customer');

    $this->actingAs($admin)
        ->get(route('activity_logs.index'))
        ->assertOk();

    $this->actingAs($supervisor)
        ->get(route('activity_logs.index'))
        ->assertOk();

    $this->actingAs($agent)
        ->get(route('activity_logs.index'))
        ->assertForbidden();

    $this->actingAs($customer)
        ->get(route('activity_logs.index'))
        ->assertForbidden();
});

it('blocks guests from protected ticket and admin routes', function () {
    $ticket = rbacTicket();

    $this->get(route('tickets.index'))->assertRedirect(route('login'));
    $this->get(route('tickets.show', $ticket))->assertRedirect(route('login'));
    $this->get(route('activity_logs.index'))->assertRedirect(route('login'));
    $this->get(route('admin.users.index'))->assertRedirect(route('login'));
});
