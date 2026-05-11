<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Ticket;
use App\Services\CommentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Ticket $ticket)
    {
        try {
            $validated = $request->validated();
            /** @var \App\Models\User */
            $user = Auth::user();
            $commentService = new CommentService();
            $commentService->createComment($ticket, $validated, $request->file('attachments'), $user);
            return redirect()->back();
        } catch (\Exception $e) {
            Log::error('An error occurred while storing a comment: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while storing a comment. Please try again later.'], 500);
        }
    }
}