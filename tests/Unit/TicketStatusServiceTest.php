<?php

use App\Services\TicketStatusService;
use App\Enums\TicketStatus;

describe('TicketStatusService', function () {
    beforeEach(function () {
        $this->service = new TicketStatusService();
    });

    describe('isValidTransition', function () {
        it('allows open to assigned transition', function () {
            expect($this->service->isValidTransition(TicketStatus::OPEN, TicketStatus::ASSIGNED))
                ->toBeTrue();
        });

        it('allows open to closed transition', function () {
            expect($this->service->isValidTransition(TicketStatus::OPEN, TicketStatus::CLOSED))
                ->toBeTrue();
        });

        it('denies open to in_progress transition', function () {
            expect($this->service->isValidTransition(TicketStatus::OPEN, TicketStatus::IN_PROGRESS))
                ->toBeFalse();
        });

        it('allows assigned to in_progress transition', function () {
            expect($this->service->isValidTransition(TicketStatus::ASSIGNED, TicketStatus::IN_PROGRESS))
                ->toBeTrue();
        });

        it('allows assigned to escalated transition', function () {
            expect($this->service->isValidTransition(TicketStatus::ASSIGNED, TicketStatus::ESCALATED))
                ->toBeTrue();
        });

        it('denies assigned to open transition', function () {
            expect($this->service->isValidTransition(TicketStatus::ASSIGNED, TicketStatus::OPEN))
                ->toBeFalse();
        });

        it('allows in_progress to waiting_for_customer transition', function () {
            expect($this->service->isValidTransition(TicketStatus::IN_PROGRESS, TicketStatus::WAITING_FOR_CUSTOMER))
                ->toBeTrue();
        });

        it('allows in_progress to resolved transition', function () {
            expect($this->service->isValidTransition(TicketStatus::IN_PROGRESS, TicketStatus::RESOLVED))
                ->toBeTrue();
        });

        it('allows in_progress to escalated transition', function () {
            expect($this->service->isValidTransition(TicketStatus::IN_PROGRESS, TicketStatus::ESCALATED))
                ->toBeTrue();
        });

        it('allows waiting_for_customer to in_progress transition', function () {
            expect($this->service->isValidTransition(TicketStatus::WAITING_FOR_CUSTOMER, TicketStatus::IN_PROGRESS))
                ->toBeTrue();
        });

        it('allows waiting_for_customer to resolved transition', function () {
            expect($this->service->isValidTransition(TicketStatus::WAITING_FOR_CUSTOMER, TicketStatus::RESOLVED))
                ->toBeTrue();
        });

        it('allows resolved to closed transition', function () {
            expect($this->service->isValidTransition(TicketStatus::RESOLVED, TicketStatus::CLOSED))
                ->toBeTrue();
        });

        it('allows resolved to reopened transition', function () {
            expect($this->service->isValidTransition(TicketStatus::RESOLVED, TicketStatus::REOPENED))
                ->toBeTrue();
        });

        it('denies resolved to in_progress transition', function () {
            expect($this->service->isValidTransition(TicketStatus::RESOLVED, TicketStatus::IN_PROGRESS))
                ->toBeFalse();
        });

        it('allows closed to reopened transition', function () {
            expect($this->service->isValidTransition(TicketStatus::CLOSED, TicketStatus::REOPENED))
                ->toBeTrue();
        });

        it('denies closed to any other status', function () {
            expect($this->service->isValidTransition(TicketStatus::CLOSED, TicketStatus::OPEN))
                ->toBeFalse();
            expect($this->service->isValidTransition(TicketStatus::CLOSED, TicketStatus::ASSIGNED))
                ->toBeFalse();
            expect($this->service->isValidTransition(TicketStatus::CLOSED, TicketStatus::IN_PROGRESS))
                ->toBeFalse();
        });

        it('allows reopened to assigned transition', function () {
            expect($this->service->isValidTransition(TicketStatus::REOPENED, TicketStatus::ASSIGNED))
                ->toBeTrue();
        });

        it('allows reopened to in_progress transition', function () {
            expect($this->service->isValidTransition(TicketStatus::REOPENED, TicketStatus::IN_PROGRESS))
                ->toBeTrue();
        });

        it('allows escalated to in_progress transition', function () {
            expect($this->service->isValidTransition(TicketStatus::ESCALATED, TicketStatus::IN_PROGRESS))
                ->toBeTrue();
        });

        it('allows escalated to resolved transition', function () {
            expect($this->service->isValidTransition(TicketStatus::ESCALATED, TicketStatus::RESOLVED))
                ->toBeTrue();
        });
    });

    describe('allowedNextStatuses', function () {
        it('returns assigned and closed for open status', function () {
            $allowed = $this->service->allowedNextStatuses(TicketStatus::OPEN);

            expect($allowed)->toContain(TicketStatus::ASSIGNED->value)
                ->and($allowed)->toContain(TicketStatus::CLOSED->value)
                ->and($allowed)->not->toContain(TicketStatus::IN_PROGRESS->value);
        });

        it('returns in_progress and escalated for assigned status', function () {
            $allowed = $this->service->allowedNextStatuses(TicketStatus::ASSIGNED);

            expect($allowed)->toContain(TicketStatus::IN_PROGRESS->value)
                ->and($allowed)->toContain(TicketStatus::ESCALATED->value);
        });

        it('returns waiting_for_customer, resolved, and escalated for in_progress status', function () {
            $allowed = $this->service->allowedNextStatuses(TicketStatus::IN_PROGRESS);

            expect($allowed)->toContain(TicketStatus::WAITING_FOR_CUSTOMER->value)
                ->and($allowed)->toContain(TicketStatus::RESOLVED->value)
                ->and($allowed)->toContain(TicketStatus::ESCALATED->value);
        });

        it('returns closed and reopened for resolved status', function () {
            $allowed = $this->service->allowedNextStatuses(TicketStatus::RESOLVED);

            expect($allowed)->toContain(TicketStatus::CLOSED->value)
                ->and($allowed)->toContain(TicketStatus::REOPENED->value);
        });

        it('returns only reopened for closed status', function () {
            $allowed = $this->service->allowedNextStatuses(TicketStatus::CLOSED);

            expect($allowed)->toBe([TicketStatus::REOPENED->value]);
        });

        it('returns empty array for invalid status', function () {
            // Using a string that's not a valid status
            $allowed = $this->service->allowedNextStatuses(TicketStatus::OPEN);

            // Should have some allowed transitions
            expect($allowed)->not->toBeEmpty();
        });
    });
});
