<?php

namespace App\Http\Controllers;

use App\Models\TaskFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskFileController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file'        => ['required','file','max:20480'], // 20MB
            'draft_token' => ['required','string','max:100'],
        ]);

        $stored = $request->file('file')->store('attachments', 'public');

        $file = TaskFile::create([
            'user_id'       => $request->user()->id,
            'task_id'       => null, // пока не связана
            'draft_token'   => $request->string('draft_token'),
            'original_name' => $request->file('file')->getClientOriginalName(),
            'path'          => $stored,
            'size'          => $request->file('file')->getSize(),
            'mime'          => $request->file('file')->getMimeType(),
        ]);

        return response()->json([
            'id'   => $file->id,
            'name' => $file->original_name,
            'url'  => $file->url,
            'size' => $file->size,
            'mime' => $file->mime,
        ]);
    }

    public function destroy(TaskFile $attachment)
    {
        abort_unless($attachment->user_id === auth()->id(), 403);

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return response()->json(['ok' => true]);
    }
}
