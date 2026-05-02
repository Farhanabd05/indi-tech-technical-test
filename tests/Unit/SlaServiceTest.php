<?php

use App\Models\Priority;
use App\Models\SlaRule;
use App\Services\TicketService;
use App\Models\Category;
use App\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('SlaService', function () {
    beforeEach(function () {
        // Seed roles
        Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator']);
        Role::firstOrCreate(['slug' => 'customer'], ['name' => 'Customer']);

        // Seed category
        Category::create(['name' => 'Technical Issue']);

        // Seed priorities
        $this->lowPriority = Priority::create(['name' => 'Low', 'level' => 1]);
        $this->mediumPriority = Priority::create(['name' => 'Medium', 'level' => 2]);
        $this->highPriority = Priority::create(['name' => 'High', 'level' => 3]);
        $this->criticalPriority = Priority::create(['name' => 'Critical', 'level' => 4]);

        // Seed SLA rules
        SlaRule::create(['priority_id' => $this->lowPriority->id, 'response_hours' => 24, 'resolution_hours' => 120]);
        SlaRule::create(['priority_id' => $this->mediumPriority->id, 'response_hours' => 12, 'resolution_hours' => 72]);
        SlaRule::create(['priority_id' => $this->highPriority->id, 'response_hours' => 4, 'resolution_hours' => 24]);
        SlaRule::create(['priority_id' => $this->criticalPriority->id, 'response_hours' => 1, 'resolution_hours' => 8]);
    });

    it('calculates due date based on SLA rule for low priority', function () {
        $service = new TicketService();
        $dueDate = $service->calculateDueDate($this->lowPriority->id);

        // Low priority: 120 hours resolution (5 days)
        expect(now()->diffInHours($dueDate))->toBeGreaterThanOrEqual(119)
            ->and(now()->diffInHours($dueDate))->toBeLessThanOrEqual(121);
    });

    it('calculates due date based on SLA rule for medium priority', function () {
        $service = new TicketService();
        $dueDate = $service->calculateDueDate($this->mediumPriority->id);

        // Medium priority: 72 hours resolution
        expect(now()->diffInHours($dueDate))->toBeGreaterThanOrEqual(71)
            ->and(now()->diffInHours($dueDate))->toBeLessThanOrEqual(73);
    });

    it('calculates due date based on SLA rule for high priority', function () {
        $service = new TicketService();
        $dueDate = $service->calculateDueDate($this->highPriority->id);

        // High priority: 24 hours resolution
        expect(now()->diffInHours($dueDate))->toBeGreaterThanOrEqual(23)
            ->and(now()->diffInHours($dueDate))->toBeLessThanOrEqual(25);
    });

    it('calculates due date based on SLA rule for critical priority', function () {
        $service = new TicketService();
        $dueDate = $service->calculateDueDate($this->criticalPriority->id);

        // Critical priority: 8 hours resolution
        expect(now()->diffInHours($dueDate))->toBeGreaterThanOrEqual(7)
            ->and(now()->diffInHours($dueDate))->toBeLessThanOrEqual(9);
    });

    it('throws exception when SLA rule not found for priority', function () {
        $service = new TicketService();

        // Create a priority without SLA rule
        $noSlaPriority = Priority::create(['name' => 'No SLA', 'level' => 99]);

        expect(fn() => $service->calculateDueDate($noSlaPriority->id))
            ->toThrow(Exception::class);
    });

    it('returns future date for due date', function () {
        $service = new TicketService();
        $dueDate = $service->calculateDueDate($this->mediumPriority->id);

        expect($dueDate)->toBeGreaterThan(now());
    });
});
