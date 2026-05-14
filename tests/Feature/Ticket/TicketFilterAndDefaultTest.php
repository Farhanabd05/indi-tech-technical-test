<?php

use App\Enums\TicketStatus;
use App\Models\Category;
use App\Models\Priority;
use App\Models\Role;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Ticket filters and defaults', function () {
    beforeEach(function () {
        Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator']);
        Role::firstOrCreate(['slug' => 'supervisor'], ['name' => 'Supervisor']);
        Role::firstOrCreate(['slug' => 'agent'], ['name' => 'Agent']);
        Role::firstOrCreate(['slug' => 'customer'], ['name' => 'Customer']);

        $this->category = Category::create(['name' => 'Technical Issue']);
        $this->priority = Priority::create(['name' => 'Medium', 'level' => 2]);
    });

    it('uses lowercase open as the database default ticket status', function () {
        $customer = createUserWithRole('customer');

        DB::table('tickets')->insert([
            'ticket_number' => 'TCK-' . date('Y') . '-900001',
            'title' => 'Default Status Ticket',
            'description' => 'Inserted without explicit status',
            'category_id' => $this->category->id,
            'priority_id' => $this->priority->id,
            'created_by' => $customer->id,
            'due_at' => now()->addDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('tickets', [
            'ticket_number' => 'TCK-' . date('Y') . '-900001',
            'status' => TicketStatus::OPEN->value,
        ]);
    });

    it('validates extended filter parameters', function () {
        $admin = createUserWithRole('administrator');

        $response = $this->actingAs($admin)->get(route('tickets.index', [
            'label_id' => 999999,
            'due_from' => '2026-05-20',
            'due_to' => '2026-05-14',
            'overdue' => 'not-a-boolean',
            'sort_by' => 'title',
            'sort_direction' => 'sideways',
        ]));

        $response->assertSessionHasErrors([
            'label_id',
            'due_to',
            'overdue',
            'sort_by',
            'sort_direction',
        ]);
    });

    it('sorts tickets without requiring a search parameter', function () {
        $customer = createUserWithRole('customer');

        createFilterTicket($this->category->id, $this->priority->id, $customer->id, [
            'ticket_number' => 'TCK-' . date('Y') . '-900002',
            'title' => 'Later Due Ticket',
            'due_at' => now()->addDays(5),
        ]);

        createFilterTicket($this->category->id, $this->priority->id, $customer->id, [
            'ticket_number' => 'TCK-' . date('Y') . '-900003',
            'title' => 'Earlier Due Ticket',
            'due_at' => now()->addDay(),
        ]);

        $tickets = Ticket::query()
            ->filter([
                'sort_by' => 'due_at',
                'sort_direction' => 'asc',
            ])
            ->pluck('title')
            ->all();

        expect($tickets)->toBe([
            'Earlier Due Ticket',
            'Later Due Ticket',
        ]);
    });
});

function createFilterTicket(int $categoryId, int $priorityId, int $customerId, array $overrides = []): Ticket
{
    return Ticket::create(array_merge([
        'ticket_number' => 'TCK-' . date('Y') . '-999999',
        'title' => 'Filter Test Ticket',
        'description' => 'Ticket used for filter testing',
        'category_id' => $categoryId,
        'priority_id' => $priorityId,
        'status' => TicketStatus::OPEN,
        'created_by' => $customerId,
        'due_at' => now()->addDays(3),
    ], $overrides));
}
