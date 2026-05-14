<?php

use App\Models\Ticket;
use App\Models\Comment;
use App\Models\Category;
use App\Models\Priority;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Enums\TicketStatus;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Ticket Internal Note', function () {
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

    describe('Internal note visibility', function () {
        it('allows agent to create internal note', function () {
            $agent = createUserWithRole('agent');
            $customer = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Internal Note Test',
                'description' => 'Ticket for internal note testing',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::ASSIGNED,
                'created_by' => $customer->id,
                'assigned_agent_id' => $agent->id,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($agent)->post(route('tickets.comments.store', $ticket), [
                'body' => 'This is an internal note',
                'is_internal' => true,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('comments', [
                'ticket_id' => $ticket->id,
                'user_id' => $agent->id,
                'body' => 'This is an internal note',
                'is_internal' => true,
            ]);
        });

        it('allows admin to create internal note', function () {
            $admin = createUserWithRole('administrator');
            $customer = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Admin Internal Note',
                'description' => 'Ticket for admin internal note',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::OPEN,
                'created_by' => $customer->id,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($admin)->post(route('tickets.comments.store', $ticket), [
                'body' => 'Admin internal note',
                'is_internal' => true,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('comments', [
                'ticket_id' => $ticket->id,
                'is_internal' => true,
            ]);
        });

        it('allows supervisor to create internal note', function () {
            $supervisor = createUserWithRole('supervisor');
            $customer = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Supervisor Internal Note',
                'description' => 'Ticket for supervisor internal note',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::OPEN,
                'created_by' => $customer->id,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($supervisor)->post(route('tickets.comments.store', $ticket), [
                'body' => 'Supervisor internal note',
                'is_internal' => true,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('comments', [
                'ticket_id' => $ticket->id,
                'is_internal' => true,
            ]);
        });

        it('denies customer from creating internal note', function () {
            $customer = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Customer Internal Note Test',
                'description' => 'Customer cannot create internal note',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::OPEN,
                'created_by' => $customer->id,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($customer)->post(route('tickets.comments.store', $ticket), [
                'body' => 'Customer trying internal note',
                'is_internal' => true,
            ]);

            $response->assertStatus(403);
            $this->assertDatabaseMissing('comments', [
                'ticket_id' => $ticket->id,
                'is_internal' => true,
            ]);
        });

        it('allows customer to create public comment', function () {
            $customer = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Public Comment Test',
                'description' => 'Customer can create public comment',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::OPEN,
                'created_by' => $customer->id,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($customer)->post(route('tickets.comments.store', $ticket), [
                'body' => 'This is a public comment',
                'is_internal' => false,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('comments', [
                'ticket_id' => $ticket->id,
                'is_internal' => false,
            ]);
        });
    });

    describe('Internal note viewing restrictions', function () {
        it('hides internal notes from customer when viewing ticket', function () {
            $agent = createUserWithRole('agent');
            $customer = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Hidden Internal Notes',
                'description' => 'Customer cannot see internal notes',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::ASSIGNED,
                'created_by' => $customer->id,
                'assigned_agent_id' => $agent->id,
                'due_at' => now()->addDays(3),
            ]);

            // Create internal note by agent
            Comment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $agent->id,
                'body' => 'Secret internal note for agents only',
                'is_internal' => true,
            ]);

            // Create public comment
            Comment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $agent->id,
                'body' => 'Public response for customer',
                'is_internal' => false,
            ]);

            // Customer views ticket - should not see internal note body
            $response = $this->actingAs($customer)->get(route('tickets.show', $ticket));

            $response->assertStatus(200);
            $response->assertSee('Public response for customer');
            $response->assertDontSee('Secret internal note for agents only');
        });

        it('shows internal notes to agent viewing ticket', function () {
            $agent = createUserWithRole('agent');
            $customer = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Visible Internal Notes',
                'description' => 'Agent can see internal notes',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::ASSIGNED,
                'created_by' => $customer->id,
                'assigned_agent_id' => $agent->id,
                'due_at' => now()->addDays(3),
            ]);

            // Create internal note
            Comment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $agent->id,
                'body' => 'Internal note visible to agents',
                'is_internal' => true,
            ]);

            // Agent views ticket - should see internal note
            $response = $this->actingAs($agent)->get(route('tickets.show', $ticket));

            $response->assertStatus(200);
            $response->assertSee('Internal note visible to agents');
        });

        it('shows internal notes to admin viewing ticket', function () {
            $admin = createUserWithRole('administrator');
            $agent = createUserWithRole('agent');
            $customer = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Admin Internal Notes View',
                'description' => 'Admin can see all internal notes',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::ASSIGNED,
                'created_by' => $customer->id,
                'assigned_agent_id' => $agent->id,
                'due_at' => now()->addDays(3),
            ]);

            // Create internal note
            Comment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $agent->id,
                'body' => 'Admin can see this internal note',
                'is_internal' => true,
            ]);

            // Admin views ticket
            $response = $this->actingAs($admin)->get(route('tickets.show', $ticket));

            $response->assertStatus(200);
            $response->assertSee('Admin can see this internal note');
        });

        it('shows internal notes to supervisor viewing ticket', function () {
            $supervisor = createUserWithRole('supervisor');
            $agent = createUserWithRole('agent');
            $customer = createUserWithRole('customer');
            $team = Team::create(['name' => 'Internal Note Supervisor Team']);
            $supervisor->update(['team_id' => $team->id]);
            $agent->update(['team_id' => $team->id]);

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Supervisor Internal Notes View',
                'description' => 'Supervisor can see internal notes',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::ASSIGNED,
                'created_by' => $customer->id,
                'assigned_agent_id' => $agent->id,
                'due_at' => now()->addDays(3),
            ]);

            // Create internal note
            Comment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $agent->id,
                'body' => 'Supervisor can see this internal note',
                'is_internal' => true,
            ]);

            // Supervisor views ticket
            $response = $this->actingAs($supervisor)->get(route('tickets.show', $ticket));

            $response->assertStatus(200);
            $response->assertSee('Supervisor can see this internal note');
        });
    });

    describe('Internal note policy', function () {
        it('denies customer from viewing internal comments via policy', function () {
            $customer = createUserWithRole('customer');
            $agent = createUserWithRole('agent');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Policy Test',
                'description' => 'Testing comment policy',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::ASSIGNED,
                'created_by' => $customer->id,
                'assigned_agent_id' => $agent->id,
                'due_at' => now()->addDays(3),
            ]);

            $internalComment = Comment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $agent->id,
                'body' => 'Secret note',
                'is_internal' => true,
            ]);

            // Customer cannot view internal comment
            $canView = $customer->can('viewInternal', $internalComment);
            expect($canView)->toBeFalse();
        });

        it('allows agent to view internal comments via policy', function () {
            $agent = createUserWithRole('agent');
            $customer = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Agent Policy Test',
                'description' => 'Testing agent comment policy',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::ASSIGNED,
                'created_by' => $customer->id,
                'assigned_agent_id' => $agent->id,
                'due_at' => now()->addDays(3),
            ]);

            $internalComment = Comment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $agent->id,
                'body' => 'Agent can see this',
                'is_internal' => true,
            ]);

            // Agent can view internal comment
            $canView = $agent->can('viewInternal', $internalComment);
            expect($canView)->toBeTrue();
        });

        it('allows admin to view internal comments via policy', function () {
            $admin = createUserWithRole('administrator');

            // Tambahkan pembuatan tiket ini agar tidak terjadi 'phantom data'
            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000002',
                'title' => 'Admin Policy Test',
                'description' => 'Testing admin comment policy',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::ASSIGNED,
                'created_by' => $admin->id,
                'due_at' => now()->addDays(3),
            ]);

            $internalComment = Comment::create([
                'ticket_id' => $ticket->id, 
                'user_id' => $admin->id,
                'body' => 'Admin can see this',
                'is_internal' => true,
            ]);

            // Admin can view internal comment
            $canView = $admin->can('viewInternal', $internalComment);
            expect($canView)->toBeTrue();
        });
    });
});
