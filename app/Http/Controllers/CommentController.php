<?php

namespace App\Http\Controllers;

use ActivityLog;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Ticket;
use App\Models\Comment;
use App\Notifications\TicketCommentedNotification;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    // Menangani penyimpanan komentar baru ke dalam pangkalan data
    public function store(StoreCommentRequest $request, Ticket $ticket)
    {
        // Data sudah tervalidasi dan user sudah terotorisasi
        $validated = $request->validated();
        // Buat komentar baru dan kaitkan dengan tiket dan pengguna yang berkomentar
        $comment = Comment::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'is_internal' => $validated['is_internal'] ?? false,
        ]);
        // Tentukan penerima notifikasi berdasarkan aturan bisnis
        if ($comment->is_internal) {
            if (Auth::id() !== $ticket->assigned_agent_id) {
                $ticket->assignedAgent?->notify(new TicketCommentedNotification($ticket));
            }

        } else {
            // Komentar publik, tentukan penerima notifikasi
            if (Auth::user()->role->slug === 'customer') {
                // Pelanggan yang berkomentar, notifikasi untuk agen penanggung jawab
                $ticket->assignedAgent?->notify(new TicketCommentedNotification($ticket));
            } else {
                // Agen yang berkomentar, notifikasi untuk pelanggan (pembuat tiket)
                $ticket->creator?->notify(new TicketCommentedNotification($ticket));
            }
        }

        ActivityLogService::log(
            $ticket,
            Auth::user(),
            'add_comment',
            null,
            $comment->content
        );

        return response()->json([
            'message' => 'Komentar berhasil ditambahkan.',
            'data'    => $comment
        ], 201);
    }
}
