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

    describe('Supervisor can reassign agents within their team', function () {
         it('allows supervisor to assign a ticket to an agent in the same team', function () {
            $supervisor = createUserWithRole('supervisor');
            $team = Team::create(['name' => 'Support Team A']);
            $supervisor->update(['team_id' => $team->id]);
 
            // 1. Buat agen awal untuk mensimulasikan kondisi sebelum reassign
            $initialAgent = createUserWithRole('agent');
            $initialAgent->update(['team_id' => $team->id]);
 
            // 2. Buat agen target penerima tugas baru
            $newAgent = createUserWithRole('agent');
            $newAgent->update(['team_id' => $team->id]);
 
            $customer = createUserWithRole('customer');
 
            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000002',
                'title' => 'Supervisor Assignment',
                'description' => 'Supervisor assigns this',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::ASSIGNED,
                'created_by' => $customer->id,
                'assigned_agent_id' => $initialAgent->id, 
                'due_at' => now()->addDays(3),
            ]);
 
            $this->actingAs($supervisor)->patch(route('tickets.assign', $ticket), [
                'assigned_agent_id' => $newAgent->id,
            ]);
 
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'assigned_agent_id' => $newAgent->id,
            ]);
        });

        it('prevents supervisor from assigning a ticket to an agent in a different team', function () {
            $supervisor = createUserWithRole('supervisor');
            $team1 = Team::create(['name' => 'Support Team A']);
            $supervisor->update(['team_id' => $team1->id]);

            $team2 = Team::create(['name' => 'Support Team B']);
            $agent = createUserWithRole('agent');
            $agent->update(['team_id' => $team2->id]);

            // Agen awal harus berada di tim Supervisor agar tiket bisa diakses
            $initialAgent = createUserWithRole('agent');
            $initialAgent->update(['team_id' => $team1->id]);

            $customer = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000003',
                'title' => 'Cross Team Assignment',
                'description' => 'Should fail',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::ASSIGNED,
                'created_by' => $customer->id,
                'assigned_agent_id' => $initialAgent->id,
                'due_at' => now()->addDays(3),
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
