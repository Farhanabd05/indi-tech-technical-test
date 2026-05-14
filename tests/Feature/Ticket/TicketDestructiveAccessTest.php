<?php

use App\Enums\ActivityLogAction;
use App\Enums\TicketStatus;
use App\Models\Attachment;
use App\Models\Category;
use App\Models\Priority;
use App\Models\Role;
use App\Models\Ticket;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Ticket destructive access and attachment management', function () {
    beforeEach(function () {
        Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator']);
        Role::firstOrCreate(['slug' => 'supervisor'], ['name' => 'Supervisor']);
        Role::firstOrCreate(['slug' => 'agent'], ['name' => 'Agent']);
        Role::firstOrCreate(['slug' => 'customer'], ['name' => 'Customer']);

        $this->category = Category::create(['name' => 'Technical Issue']);
        $this->priority = Priority::create(['name' => 'Medium', 'level' => 2]);
    });

    it('allows administrator to soft delete a ticket and writes an activity log', function () {
        $admin = createUserWithRole('administrator');
        $customer = createUserWithRole('customer');
        $ticket = createBasicTicket($this->category->id, $this->priority->id, $customer->id);

        $response = $this->actingAs($admin)->delete(route('tickets.destroy', $ticket));

        $response->assertRedirect(route('tickets.index'));
        $this->assertSoftDeleted('tickets', ['id' => $ticket->id]);
        $this->assertDatabaseHas('activity_logs', [
            'ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'action' => ActivityLogAction::DELETE_TICKET->value,
        ]);
    });

    it('prevents non administrators from deleting a ticket', function () {
        $agent = createUserWithRole('agent');
        $customer = createUserWithRole('customer');
        $ticket = createBasicTicket($this->category->id, $this->priority->id, $customer->id, [
            'assigned_agent_id' => $agent->id,
        ]);

        $response = $this->actingAs($agent)->delete(route('tickets.destroy', $ticket));

        $response->assertStatus(403);
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'deleted_at' => null,
        ]);
    });

    it('stores follow-up attachments through the service and writes upload logs', function () {
        Storage::fake('public');

        $customer = createUserWithRole('customer');
        $ticket = createBasicTicket($this->category->id, $this->priority->id, $customer->id);
        $file = UploadedFile::fake()->create('evidence.pdf', 32, 'application/pdf');

        $response = $this->actingAs($customer)->post(route('tickets.attachments.store', $ticket), [
            'attachments' => [$file],
        ]);

        $response->assertRedirect();

        $attachment = Attachment::firstOrFail();
        Storage::disk('public')->assertExists($attachment->path);

        $this->assertDatabaseHas('attachments', [
            'id' => $attachment->id,
            'attachable_id' => $ticket->id,
            'attachable_type' => Ticket::class,
            'uploaded_by' => $customer->id,
            'original_name' => 'evidence.pdf',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'ticket_id' => $ticket->id,
            'user_id' => $customer->id,
            'action' => ActivityLogAction::UPLOAD_ATTACHMENT->value,
            'new_value' => 'evidence.pdf',
        ]);
    });

    it('allows the original uploader to delete an attachment and writes a delete log', function () {
        Storage::fake('public');

        $customer = createUserWithRole('customer');
        $ticket = createBasicTicket($this->category->id, $this->priority->id, $customer->id);
        $attachment = createStoredAttachment($ticket, $customer->id, 'customer-note.pdf');

        $response = $this->actingAs($customer)->delete(route('attachments.destroy', $attachment));

        $response->assertRedirect();
        Storage::disk('public')->assertMissing($attachment->path);
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
        $this->assertDatabaseHas('activity_logs', [
            'ticket_id' => $ticket->id,
            'user_id' => $customer->id,
            'action' => ActivityLogAction::DELETE_ATTACHMENT->value,
            'old_value' => 'customer-note.pdf',
        ]);
    });

    it('allows administrator to delete another users attachment', function () {
        Storage::fake('public');

        $admin = createUserWithRole('administrator');
        $customer = createUserWithRole('customer');
        $ticket = createBasicTicket($this->category->id, $this->priority->id, $customer->id);
        $attachment = createStoredAttachment($ticket, $customer->id, 'customer-file.pdf');

        $response = $this->actingAs($admin)->delete(route('attachments.destroy', $attachment));

        $response->assertRedirect();
        Storage::disk('public')->assertMissing($attachment->path);
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
    });

    it('prevents other users from deleting someone elses attachment', function () {
        Storage::fake('public');

        $customer = createUserWithRole('customer');
        $otherCustomer = createUserWithRole('customer');
        $ticket = createBasicTicket($this->category->id, $this->priority->id, $customer->id);
        $attachment = createStoredAttachment($ticket, $customer->id, 'private-file.pdf');

        $response = $this->actingAs($otherCustomer)->delete(route('attachments.destroy', $attachment));

        $response->assertStatus(403);
        Storage::disk('public')->assertExists($attachment->path);
        $this->assertDatabaseHas('attachments', ['id' => $attachment->id]);
    });
});

function createBasicTicket(int $categoryId, int $priorityId, int $customerId, array $overrides = []): Ticket
{
    return Ticket::create(array_merge([
        'ticket_number' => sprintf('TCK-%s-%06d', date('Y'), Ticket::count() + 1),
        'title' => 'Destructive Access Test Ticket',
        'description' => 'Ticket used for destructive access testing',
        'category_id' => $categoryId,
        'priority_id' => $priorityId,
        'status' => TicketStatus::OPEN,
        'created_by' => $customerId,
        'due_at' => now()->addDays(3),
    ], $overrides));
}

function createStoredAttachment(Ticket $ticket, int $uploadedBy, string $originalName): Attachment
{
    $path = 'attachments/' . $originalName;
    Storage::disk('public')->put($path, 'test-content');

    return $ticket->attachments()->create([
        'uploaded_by' => $uploadedBy,
        'original_name' => $originalName,
        'stored_name' => $originalName,
        'path' => $path,
        'mime_type' => 'application/pdf',
        'size' => 12,
    ]);
}
