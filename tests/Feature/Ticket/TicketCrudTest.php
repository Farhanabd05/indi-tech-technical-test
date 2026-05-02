<?php

use App\Models\Ticket;
use App\Models\Category;
use App\Models\Priority;
use App\Models\Role;
use App\Models\User;
use App\Enums\TicketStatus;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Ticket CRUD', function () {
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

    describe('Customer can create ticket', function () {
        it('allows customer to create a ticket', function () {
            $customer = createUserWithRole('customer');

            $response = $this->actingAs($customer)->post(route('tickets.store'), [
                'title' => 'Test Ticket Title',
                'description' => 'This is a test ticket description',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'title' => 'Test Ticket Title',
                'description' => 'This is a test ticket description',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::OPEN->value,
            ]);
        });

        it('generates ticket number automatically', function () {
            $customer = createUserWithRole('customer');

            $this->actingAs($customer)->post(route('tickets.store'), [
                'title' => 'Test Ticket',
                'description' => 'Test description',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
            ]);

            $ticket = Ticket::first();
            expect($ticket->ticket_number)->toMatch('/^TCK-\d{4}-\d{6}$/');
        });

        it('sets due date automatically based on priority', function () {
            $customer = createUserWithRole('customer');

            $this->actingAs($customer)->post(route('tickets.store'), [
                'title' => 'Test Ticket',
                'description' => 'Test description',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
            ]);

            $ticket = Ticket::first();
            expect($ticket->due_at)->toBeGreaterThan(now());
        });

        it('validates required fields', function () {
            $customer = createUserWithRole('customer');

            $response = $this->actingAs($customer)->post(route('tickets.store'), []);

            $response->assertSessionHasErrors(['title', 'description', 'category_id', 'priority_id']);
        });

        it('denies unauthenticated user from creating ticket', function () {
            $response = $this->post(route('tickets.store'), [
                'title' => 'Test Ticket',
                'description' => 'Test description',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
            ]);

            $response->assertRedirect(route('login'));
        });
    });

    describe('Customer can only view their own tickets', function () {
        it('allows customer to view their own tickets', function () {
            $customer = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'My Ticket',
                'description' => 'This is my ticket',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::OPEN,
                'created_by' => $customer->id,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($customer)->get(route('tickets.index'));

            $response->assertStatus(200);
            $response->assertSee('My Ticket');
        });

        it('prevents customer from viewing other customer tickets', function () {
            $customer1 = createUserWithRole('customer');
            $customer2 = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Other Customer Ticket',
                'description' => 'This belongs to another customer',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::OPEN,
                'created_by' => $customer2->id,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($customer1)->get(route('tickets.index'));

            $response->assertStatus(200);
            $response->assertDontSee('Other Customer Ticket');
        });

        it('prevents customer from viewing ticket detail they do not own', function () {
            $customer1 = createUserWithRole('customer');
            $customer2 = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Other Customer Ticket',
                'description' => 'This belongs to another customer',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::OPEN,
                'created_by' => $customer2->id,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($customer1)->get(route('tickets.show', $ticket));

            $response->assertStatus(403);
        });
    });

    describe('Agent can only view assigned tickets', function () {
        it('allows agent to view tickets assigned to them', function () {
            $agent = createUserWithRole('agent');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'My Assigned Ticket',
                'description' => 'This ticket is assigned to me',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::ASSIGNED,
                'created_by' => createUserWithRole('customer')->id,
                'assigned_agent_id' => $agent->id,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($agent)->get(route('tickets.index'));

            $response->assertStatus(200);
            $response->assertSee('My Assigned Ticket');
        });

        it('prevents agent from viewing tickets not assigned to them', function () {
            $agent = createUserWithRole('agent');
            $otherAgent = createUserWithRole('agent');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Other Agent Ticket',
                'description' => 'This ticket is assigned to another agent',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::ASSIGNED,
                'created_by' => createUserWithRole('customer')->id,
                'assigned_agent_id' => $otherAgent->id,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($agent)->get(route('tickets.index'));

            $response->assertStatus(200);
            $response->assertDontSee('Other Agent Ticket');
        });

        it('prevents agent from viewing ticket detail not assigned to them', function () {
            $agent = createUserWithRole('agent');
            $otherAgent = createUserWithRole('agent');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Other Agent Ticket',
                'description' => 'This ticket is assigned to another agent',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::ASSIGNED,
                'created_by' => createUserWithRole('customer')->id,
                'assigned_agent_id' => $otherAgent->id,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($agent)->get(route('tickets.show', $ticket));

            $response->assertStatus(403);
        });
    });

    describe('Admin can view all tickets', function () {
        it('allows admin to view all tickets', function () {
            $admin = createUserWithRole('administrator');
            $customer1 = createUserWithRole('customer');
            $customer2 = createUserWithRole('customer');

            Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Customer 1 Ticket',
                'description' => 'Ticket from customer 1',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::OPEN,
                'created_by' => $customer1->id,
                'due_at' => now()->addDays(3),
            ]);

            Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000002',
                'title' => 'Customer 2 Ticket',
                'description' => 'Ticket from customer 2',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::OPEN,
                'created_by' => $customer2->id,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($admin)->get(route('tickets.index'));

            $response->assertStatus(200);
            $response->assertSee('Customer 1 Ticket');
            $response->assertSee('Customer 2 Ticket');
        });

        it('allows admin to view any ticket detail', function () {
            $admin = createUserWithRole('administrator');
            $customer = createUserWithRole('customer');

            $ticket = Ticket::create([
                'ticket_number' => 'TCK-' . date('Y') . '-000001',
                'title' => 'Customer Ticket',
                'description' => 'Ticket from customer',
                'category_id' => $this->category->id,
                'priority_id' => $this->priority->id,
                'status' => TicketStatus::OPEN,
                'created_by' => $customer->id,
                'due_at' => now()->addDays(3),
            ]);

            $response = $this->actingAs($admin)->get(route('tickets.show', $ticket));

            $response->assertStatus(200);
            $response->assertSee('Customer Ticket');
        });
    });
});
