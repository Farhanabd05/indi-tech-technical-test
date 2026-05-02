<?php

use App\Models\Ticket;
use App\Models\Category;
use App\Models\Priority;
use App\Models\Role;
use App\Models\User;
use App\Models\Team;
use App\Enums\TicketStatus;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Ticket Assignment', function () {
    beforeEach(function () {
        // Seed roles
        Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator']);
        Role::firstOrCreate(['slug' => 'supervisor'], ['name' => 'Supervisor']);
        Role::firstOrCreate(['slug' => 'agent'], ['name' => 'Agent']);
        Role::firstOrCreate(['slug' => 'customer'], ['name' => 'Customer']);

        // Seed category and priority
        $this->category = Category::create(['name' => 'Technical Issue']);
        $this->priority = Priority::create(['name' => 'Medium', 'level' => 2]);
    });

    describe('Admin can assign agents to tickets', function () {
        it('allows admin to assign an agent to a ticket', function () {
            $admin = createUserWithRole('administrator');
            $agent = createUserWithRole('agent');
            $customer = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Unassigned Ticket',
                'description' => 'This ticket needs an agent',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::OPEN,
                'created_by' => $customer->id,
                'assigned_agent_id' => null,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($admin)->patch(route('tickets.assign', $ticket), [
                'assigned_agent_id' => $agent->id,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'assigned_agent_id' => $agent->id,
            ]);
        });

        it('allows admin to reassign ticket from one agent to another', function () {
            $admin = createUserWithRole('administrator');
            $agent1 = createUserWithRole('agent');
            $agent2 = createUserWithRole('agent');
            $customer = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Reassign Ticket',
                'description' => 'This ticket needs reassignment',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::ASSIGNED,
                'created_by' => $customer->id,
                'assigned_agent_id' => $agent1->id,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($admin)->patch(route('tickets.assign', $ticket), [
                'assigned_agent_id' => $agent2->id,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'assigned_agent_id' => $agent2->id,
            ]);
        });

        it('allows admin to unassign a ticket', function () {
            $admin = createUserWithRole('administrator');
            $agent = createUserWithRole('agent');
            $customer = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Unassign Ticket',
                'description' => 'This ticket will be unassigned',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::ASSIGNED,
                'created_by' => $customer->id,
                'assigned_agent_id' => $agent->id,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($admin)->patch(route('tickets.assign', $ticket), [
                'assigned_agent_id' => null,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'assigned_agent_id' => null,
            ]);
        });
    });

    describe('Supervisor can assign agents within their team', function () {
        it('allows supervisor to assign agent from their team', function () {
            $team = Team::create(['name' => 'Support Team']);

            $supervisor = createUserWithRole('supervisor');
            $supervisor->update(['team_id' => $team->id]);

            $agent = createUserWithRole('agent');
            $agent->update(['team_id' => $team->id]);

            $customer = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Team Ticket',
                'description' => 'Ticket for team assignment',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::OPEN,
                'created_by' => $customer->id,
                'assigned_agent_id' => null,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($supervisor)->patch(route('tickets.assign', $ticket), [
                'assigned_agent_id' => $agent->id,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'assigned_agent_id' => $agent->id,
            ]);
        });

        it('denies supervisor from assigning agent outside their team', function () {
            $team1 = Team::create(['name' => 'Team 1']);
            $team2 = Team::create(['name' => 'Team 2']);

            $supervisor = createUserWithRole('supervisor');
            $supervisor->update(['team_id' => $team1->id]);

            $agentInTeam = createUserWithRole('agent');
            $agentInTeam->update(['team_id' => $team1->id]);

            $agentOutsideTeam = createUserWithRole('agent');
            $agentOutsideTeam->update(['team_id' => $team2->id]);

            $customer = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Team Restriction Ticket',
                'description' => 'Ticket with team restriction',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::OPEN,
                'created_by' => $customer->id,
                'assigned_agent_id' => null,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($supervisor)->patch(route('tickets.assign', $ticket), [
                'assigned_agent_id' => $agentOutsideTeam->id,
            ]);

            $response->assertStatus(403);
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'assigned_agent_id' => null,
            ]);
        });

        it('allows supervisor to reassign ticket within their team', function () {
            $team = Team::create(['name' => 'Support Team']);

            $supervisor = createUserWithRole('supervisor');
            $supervisor->update(['team_id' => $team->id]);

            $agent1 = createUserWithRole('agent');
            $agent1->update(['team_id' => $team->id]);

            $agent2 = createUserWithRole('agent');
            $agent2->update(['team_id' => $team->id]);

            $customer = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Reassign Within Team',
                'description' => 'Reassign within team',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::ASSIGNED,
                'created_by' => $customer->id,
                'assigned_agent_id' => $agent1->id,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($supervisor)->patch(route('tickets.assign', $ticket), [
                'assigned_agent_id' => $agent2->id,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'assigned_agent_id' => $agent2->id,
            ]);
        });
    });

    describe('Unauthorized assignment', function () {
        it('denies agent from assigning tickets', function () {
            $agent = createUserWithRole('agent');
            $otherAgent = createUserWithRole('agent');
            $customer = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Agent Assignment Test',
                'description' => 'Agent cannot assign',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::OPEN,
                'created_by' => $customer->id,
                'assigned_agent_id' => null,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($agent)->patch(route('tickets.assign', $ticket), [
                'assigned_agent_id' => $otherAgent->id,
            ]);

            $response->assertStatus(403);
        });

        it('denies customer from assigning tickets', function () {
            $customer = createUserWithRole('customer');
            $agent = createUserWithRole('agent');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Customer Assignment Test',
                'description' => 'Customer cannot assign',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::OPEN,
                'created_by' => $customer->id,
                'assigned_agent_id' => null,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($customer)->patch(route('tickets.assign', $ticket), [
                'assigned_agent_id' => $agent->id,
            ]);

            $response->assertStatus(403);
        });
    });

    describe('Ticket status changes on assignment', function () {
        it('automatically changes status to assigned when agent is assigned', function () {
            $admin = createUserWithRole('administrator');
            $agent = createUserWithRole('agent');
            $customer = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Auto Status Change',
                'description' => 'Status should change on assignment',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::OPEN,
                'created_by' => $customer->id,
                'assigned_agent_id' => null,
                'due_at' => now()->addDays(3),
            ]);

            $this->actingAs($admin)->patch(route('tickets.assign', $ticket), [
                'assigned_agent_id' => $agent->id,
            ]);

            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'assigned_agent_id' => $agent->id,
                'status' => TicketStatus::ASSIGNED->value,
            ]);
        });
    });
});
