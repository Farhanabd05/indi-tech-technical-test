<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attachment;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class AttachmentController extends Controller
{
    public function show(Attachment $attachment)
    {
        return response()->file(storage_path('app/public/' . $attachment->path));
    }

    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'attachments' => 'required|array',
            'attachments.*' => 'required|file|max:2048', // Maksimal ukuran berkas adalah 2MB
        ]);

        $attachments = $request->file('attachments');

        foreach ($attachments as $attachment) {
            $path = $attachment->store('attachments', 'public');
            Attachment::create([
                'path' => $path,
                'attachable_id' => $ticket->id,
                'attachable_type' => Ticket::class,
                'stored_name' => $attachment->hashName(),
                'original_name' => $attachment->getClientOriginalName(),
                'mime_type' => $attachment->getMimeType(),
                'size' => $attachment->getSize(),
                'uploaded_by' => Auth::id()
            ]);
        }

        return redirect()->back();
    }
}
