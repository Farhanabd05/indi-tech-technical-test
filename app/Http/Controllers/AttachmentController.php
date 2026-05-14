<?php

namespace App\Http\Controllers;

use App\Services\AttachmentService;
use Illuminate\Http\Request;
use App\Models\Attachment;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AttachmentController extends Controller
{
    public function show(Attachment $attachment)
    {
        Gate::authorize('view', $attachment);
        return response()->file(storage_path('app/public/' . $attachment->path));
    }

    public function store(Request $request, Ticket $ticket, AttachmentService $attachmentService)
    {
        $request->validate([
            'attachments' => 'required|array',
            'attachments.*' => 'required|file|max:2048|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $attachmentService->storeForTicket($ticket, $request->file('attachments'), $user);

        return redirect()->back();
    }

    public function destroy(Attachment $attachment, AttachmentService $attachmentService)
    {
        Gate::authorize('delete', $attachment);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $attachmentService->delete($attachment, $user);

        return redirect()->back();
    }
}
