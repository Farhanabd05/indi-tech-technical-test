<?php

use App\Models\Ticket;
use App\Models\Category;
use App\Models\Priority;
use App\Models\Role;
use App\Models\User;
use App\Enums\TicketStatus;
use App\Services\TicketStatusService;

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

    describe('Valid status transitions', function () {
        it('allows open to assigned transition', function () {
            $ticket = createTicket(['status' => TicketStatus::OPEN]);
            $agent = createUserWithRole('agent');

            $response = $this->actingAs($agent)->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::ASSIGNED->value,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'status' => TicketStatus::ASSIGNED->value,
            ]);
        });

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
            $ticket = createTicket(['status' => TicketStatus::ASSIGNED]);
            $agent = createUserWithRole('agent');

            $response = $this->actingAs($agent)->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::IN_PROGRESS->value,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'status' => TicketStatus::IN_PROGRESS->value,
            ]);
        });

        it('allows in_progress to resolved transition', function () {
            $ticket = createTicket(['status' => TicketStatus::IN_PROGRESS]);
            $agent = createUserWithRole('agent');

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
            $agent = createUserWithRole('agent');

            $response = $this->actingAs($agent)->patch(route('tickets.status.update', $ticket), [
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
            $agent = createUserWithRole('agent');

            $response = $this->actingAs($agent)->patch(route('tickets.status.update', $ticket), [
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
            $agent = createUserWithRole('agent');

            $response = $this->actingAs($agent)->patch(route('tickets.status.update', $ticket), [
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
            $agent = createUserWithRole('agent');

            $response = $this->actingAs($agent)->patch(route('tickets.status.update', $ticket), [
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
            $agent = createUserWithRole('agent');

            $response = $this->actingAs($agent)->patch(route('tickets.status.update', $ticket), [
                'status' => TicketStatus::OPEN->value,
            ]);

            $response->assertSessionHasErrors('status');
            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'status' => TicketStatus::IN_PROGRESS->value,
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
});
