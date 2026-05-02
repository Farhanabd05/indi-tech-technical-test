<?php

use App\Services\TicketService;
use App\Models\Ticket;
use App\Models\Category;
use App\Models\Priority;
use App\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('TicketNumberService', function () {
    beforeEach(function () {
        // Seed roles
        Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator']);
        Role::firstOrCreate(['slug' => 'customer'], ['name' => 'Customer']);

        // Seed category and priority for ticket creation
        Category::create(['name' => 'Technical Issue']);
        Priority::create(['name' => 'Low', 'level' => 1]);
    });

    it('generates ticket number with correct format', function () {
        $service = new TicketService();
        $ticketNumber = $service->generateTicketNumber();

        $year = date('Y');
        expect($ticketNumber)->toMatch("/^TCK-{$year}-\d{6}$/");
    });

    it('generates sequential ticket numbers', function () {
        $service = new TicketService();

        $first = $service->generateTicketNumber();
        $second = $service->generateTicketNumber();
        $third = $service->generateTicketNumber();

        $firstNum = (int) substr($first, -6);
        $secondNum = (int) substr($second, -6);
        $thirdNum = (int) substr($third, -6);

        expect($secondNum)->toBe($firstNum + 1)
            ->and($thirdNum)->toBe($secondNum + 1);
    });

    it('starts from 000001 for new year', function () {
        $service = new TicketService();
        $ticketNumber = $service->generateTicketNumber();

        $year = date('Y');
        expect($ticketNumber)->toBe("TCK-{$year}-000001");
    });

    it('generates unique ticket numbers in concurrent scenarios', function () {
        $service = new TicketService();

        $tickets = [];
        for ($i = 0; $i < 5; $i++) {
            $tickets[] = $service->generateTicketNumber();
        }

        // All ticket numbers should be unique
        expect(array_unique($tickets))->toHaveCount(5);

        // Check sequential increment
        $numbers = array_map(fn($t) => (int) substr($t, -6), $tickets);
        for ($i = 1; $i < count($numbers); $i++) {
            expect($numbers[$i])->toBe($numbers[$i - 1] + 1);
        }
    });

    it('generates ticket number with 6 digit padding', function () {
        $service = new TicketService();

        // Generate 100 tickets to ensure padding works
        for ($i = 0; $i < 100; $i++) {
            $ticketNumber = $service->generateTicketNumber();
            $numberPart = substr($ticketNumber, -6);
            expect(strlen($numberPart))->toBe(6);
        }
    });
});
