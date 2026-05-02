<?php

use App\Models\Ticket;
use App\Models\Category;
use App\Models\Priority;
use App\Models\Role;
use App\Models\User;
use App\Enums\TicketStatus;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('OverdueService', function () {
    beforeEach(function () {
        // Seed roles
        Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator']);
        Role::firstOrCreate(['slug' => 'customer'], ['name' => 'Customer']);

        // Seed category and priority
        $this->category = Category::create(['name' => 'Technical Issue']);
        $this->priority = Priority::create(['name' => 'Medium', 'level' => 2]);

        // Create customer user
        $this->customer = User::factory()->create([
            'role_id' => Role::where('slug', 'customer')->first()->id,
        ]);
    });

    it('identifies ticket as overdue when due_at is in the past', function () {
        $ticket = Ticket::create([
            'ticket_number' => 'TCK-' . date('Y') . '-000001',
            'title' => 'Overdue Ticket',
            'description' => 'This ticket is overdue',
            'category_id' => $this->category->id,
            'priority_id' => $this->priority->id,
            'status' => TicketStatus::OPEN,
            'created_by' => $this->customer->id,
            'due_at' => now()->subHours(2),
        ]);

        expect($ticket->fresh()->isOverdue())->toBeTrue();
    });

    it('identifies ticket as not overdue when due_at is in the future', function () {
        $ticket = Ticket::create([
            'ticket_number' => 'TCK-' . date('Y') . '-000002',
            'title' => 'Future Ticket',
            'description' => 'This ticket is not due yet',
            'category_id' => $this->category->id,
            'priority_id' => $this->priority->id,
            'status' => TicketStatus::OPEN,
            'created_by' => $this->customer->id,
            'due_at' => now()->addHours(2),
        ]);

        expect($ticket->fresh()->isOverdue())->toBeFalse();
    });

    it('identifies ticket as not overdue when status is resolved', function () {
        $ticket = Ticket::create([
            'ticket_number' => 'TCK-' . date('Y') . '-000003',
            'title' => 'Resolved Ticket',
            'description' => 'This ticket is resolved',
            'category_id' => $this->category->id,
            'priority_id' => $this->priority->id,
            'status' => TicketStatus::RESOLVED,
            'created_by' => $this->customer->id,
            'due_at' => now()->subHours(2),
        ]);

        expect($ticket->fresh()->isOverdue())->toBeFalse();
    });

    it('identifies ticket as not overdue when status is closed', function () {
        $ticket = Ticket::create([
            'ticket_number' => 'TCK-' . date('Y') . '-000004',
            'title' => 'Closed Ticket',
            'description' => 'This ticket is closed',
            'category_id' => $this->category->id,
            'priority_id' => $this->priority->id,
            'status' => TicketStatus::CLOSED,
            'created_by' => $this->customer->id,
            'due_at' => now()->subHours(2),
        ]);

        expect($ticket->fresh()->isOverdue())->toBeFalse();
    });

    it('identifies ticket as overdue when status is in_progress and due_at is past', function () {
        $ticket = Ticket::create([
            'ticket_number' => 'TCK-' . date('Y') . '-000005',
            'title' => 'In Progress Overdue',
            'description' => 'This ticket is in progress but overdue',
            'category_id' => $this->category->id,
            'priority_id' => $this->priority->id,
            'status' => TicketStatus::IN_PROGRESS,
            'created_by' => $this->customer->id,
            'due_at' => now()->subHours(2),
        ]);

        expect($ticket->fresh()->isOverdue())->toBeTrue();
    });

    it('identifies ticket as overdue when status is assigned and due_at is past', function () {
        $ticket = Ticket::create([
            'ticket_number' => 'TCK-' . date('Y') . '-000006',
            'title' => 'Assigned Overdue',
            'description' => 'This ticket is assigned but overdue',
            'category_id' => $this->category->id,
            'priority_id' => $this->priority->id,
            'status' => TicketStatus::ASSIGNED,
            'created_by' => $this->customer->id,
            'due_at' => now()->subHours(2),
        ]);

        expect($ticket->fresh()->isOverdue())->toBeTrue();
    });

    it('identifies ticket as not overdue when status is waiting_for_customer', function () {
        $ticket = Ticket::create([
            'ticket_number' => 'TCK-' . date('Y') . '-000007',
            'title' => 'Waiting for Customer',
            'description' => 'This ticket is waiting for customer',
            'category_id' => $this->category->id,
            'priority_id' => $this->priority->id,
            'status' => TicketStatus::WAITING_FOR_CUSTOMER,
            'created_by' => $this->customer->id,
            'due_at' => now()->subHours(2),
        ]);

        expect($ticket->fresh()->isOverdue())->toBeFalse();
    });

    it('works with scope overdue', function () {
        // Create overdue ticket
        Ticket::create([
            'ticket_number' => 'TCK-' . date('Y') . '-000008',
            'title' => 'Overdue Ticket 1',
            'description' => 'This ticket is overdue',
            'category_id' => $this->category->id,
            'priority_id' => $this->priority->id,
            'status' => TicketStatus::OPEN,
            'created_by' => $this->customer->id,
            'due_at' => now()->subHours(2),
        ]);

        // Create non-overdue ticket (future due date)
        Ticket::create([
            'ticket_number' => 'TCK-' . date('Y') . '-000009',
            'title' => 'Future Ticket',
            'description' => 'This ticket is not due yet',
            'category_id' => $this->category->id,
            'priority_id' => $this->priority->id,
            'status' => TicketStatus::OPEN,
            'created_by' => $this->customer->id,
            'due_at' => now()->addHours(2),
        ]);

        // Create resolved overdue ticket (should not be in overdue scope)
        Ticket::create([
            'ticket_number' => 'TCK-' . date('Y') . '-000010',
            'title' => 'Resolved Overdue',
            'description' => 'This ticket is resolved',
            'category_id' => $this->category->id,
            'priority_id' => $this->priority->id,
            'status' => TicketStatus::RESOLVED,
            'created_by' => $this->customer->id,
            'due_at' => now()->subHours(2),
        ]);

        $overdueTickets = Ticket::overdue()->get();

        expect($overdueTickets)->toHaveCount(1)
            ->and($overdueTickets->first()->title)->toBe('Overdue Ticket 1');
    });
});
