<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attachment;
class AttachmentController extends Controller
{
    public function show(Attachment $attachment)
    {
        return response()->file(storage_path('app/public/' . $attachment->path));
    }
}
