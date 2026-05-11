<?php

namespace App\Services;
use App\Models\Ticket;
use App\Models\Comment;
use App\Models\User;
use App\Enums\ActivityLogAction;
use App\Notifications\TicketCommentedNotification;
use Illuminate\Support\Facades\DB;
class CommentService
{
    // This function accepts an instance of a ticket, validated data, an array of files, and a user
    public function createComment(Ticket $ticket, array $data, $attachments, User $user)
    {
        return DB::transaction(function () use ($ticket, $data, $attachments, $user) {
            // 1. Execute the creation of the comment
            $comment = Comment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'body' => $data['body'],
                'is_internal' => $data['is_internal'],
            ]);

            // 2. If there are attachments, process their storage
            if ($attachments) {
                foreach ($attachments as $file) {
                    $path = $file->store('comments', 'public');
                    $comment->attachments()->create([
                        'path' => $path,
                        'stored_name' => $file->hashName(),
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'uploaded_by' => $user->id
                    ]);
                }
            }
            // 3. Execute the sending of notifications based on internal/public rules
            if ($comment->is_internal) {
                if ($user->id !== $ticket->assigned_agent_id) {
                    $ticket->assignedAgent?->notify(new TicketCommentedNotification($ticket));
                }
            } else {
                if ($user->hasRole('customer')) {
                    $ticket->assignedAgent?->notify(new TicketCommentedNotification($ticket));
                } else {
                    $ticket->creator?->notify(new TicketCommentedNotification($ticket));
                }
            }
            
            // 4. Log the activity using ActivityLogAction::ADD_COMMENT
            ActivityLogService::log(
                $ticket,
                $user,
                ActivityLogAction::ADD_COMMENT
            );
            return $comment;
        });
    }
}