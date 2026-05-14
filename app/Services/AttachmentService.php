<?php

namespace App\Services;

use App\Enums\ActivityLogAction;
use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AttachmentService
{
    public function storeForTicket(Ticket $ticket, array $attachments, User $user): void
    {
        $storedPaths = [];

        try {
            DB::transaction(function () use ($ticket, $attachments, $user, &$storedPaths) {
                foreach ($attachments as $attachment) {
                    $path = $attachment->store('attachments', 'public');
                    $storedPaths[] = $path;

                    $createdAttachment = $ticket->attachments()->create([
                        'path' => $path,
                        'stored_name' => $attachment->hashName(),
                        'original_name' => $attachment->getClientOriginalName(),
                        'mime_type' => $attachment->getMimeType(),
                        'size' => $attachment->getSize(),
                        'uploaded_by' => $user->id,
                    ]);

                    ActivityLogService::log(
                        $ticket,
                        $user,
                        ActivityLogAction::UPLOAD_ATTACHMENT,
                        null,
                        $createdAttachment->original_name
                    );
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);

            throw $exception;
        }
    }

    public function delete(Attachment $attachment, User $user): void
    {
        DB::transaction(function () use ($attachment, $user) {
            $ticket = $this->resolveTicket($attachment);
            $originalName = $attachment->original_name;

            if (
                Storage::disk('public')->exists($attachment->path)
                && ! Storage::disk('public')->delete($attachment->path)
            ) {
                throw new \RuntimeException('Failed to delete attachment file.');
            }

            $attachment->delete();

            ActivityLogService::log(
                $ticket,
                $user,
                ActivityLogAction::DELETE_ATTACHMENT,
                $originalName,
                null
            );
        });
    }

    private function resolveTicket(Attachment $attachment): Ticket
    {
        $attachable = $attachment->attachable;

        if ($attachable instanceof Ticket) {
            return $attachable;
        }

        if ($attachable instanceof Comment) {
            return $attachable->ticket;
        }

        throw new \RuntimeException('Attachment is not connected to a ticket.');
    }
}
