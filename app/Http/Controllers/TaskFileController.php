<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskFileController extends Controller
{
    /**
     * Загрузка файлов сразу в задачу (множественная).
     * Маршрут: POST /tasks/{task}/files  ->  tasks.files.store
     */
    public function store(Request $request, Task $task)
    {
        // поддержим и один файл (file), и массив (files[])
        $hasMany = $request->hasFile('files');
        $hasOne  = $request->hasFile('file');

        if (!$hasMany && !$hasOne) {
            return response()->json(['message' => 'Файл(ы) не переданы'], 422);
        }

        $rules = $hasMany
            ? ['files' => ['required','array'], 'files.*' => ['file','max:20480']]     // 20 МБ на файл
            : ['file'  => ['required','file','max:20480']];

        $validated = $request->validate($rules);

        $files = $hasMany ? $request->file('files') : [$request->file('file')];

        $result = [];
        foreach ($files as $uploaded) {
            $stored = $uploaded->store('attachments/'.$task->id, 'public');

            $rec = TaskFile::create([
                'user_id'       => $request->user()->id,
                'task_id'       => $task->id,
                'draft_token'   => null,
                'original_name' => $uploaded->getClientOriginalName(),
                'path'          => $stored,
                'size'          => $uploaded->getSize(),
                'mime'          => $uploaded->getMimeType(),
            ]);

            $result[] = [
                'id'   => $rec->id,
                'name' => $rec->original_name,
                'url'  => $rec->url, // из аксессора
                'size' => $rec->size,
                'mime' => $rec->mime,
            ];
        }

        return response()->json(['files' => $result]);
    }

    /**
     * Временная загрузка (драфт), когда задача ещё не создана.
     * Маршрут: POST /task-files/upload  -> task-files.upload
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file'        => ['required','file','max:20480'], // 20MB
            'draft_token' => ['required','string','max:100'],
        ]);

        $stored = $request->file('file')->store('attachments/drafts', 'public');

        $file = TaskFile::create([
            'user_id'       => $request->user()->id,
            'task_id'       => null,
            'draft_token'   => $request->string('draft_token'),
            'original_name' => $request->file('file')->getClientOriginalName(),
            'path'          => $stored,
            'size'          => $request->file('file')->getSize(),
            'mime'          => $request->file('file')->getMimeType(),
        ]);

        return response()->json([
            'files' => [[
                'id'   => $file->id,
                'name' => $file->original_name,
                'url'  => $file->url,
                'size' => $file->size,
                'mime' => $file->mime,
            ]],
        ]);
    }

    /**
     * Удаление файла.
     * Маршрут: DELETE /files/{file}  -> tasks.files.delete
     */
    public function destroy(TaskFile $file)
    {
        // Разрешим удалять владельцу файла; при желании — добавь политику/проверку прав

        if ($file->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        Storage::disk('public')->delete($file->path);
        $file->delete();

        return response()->json(['ok' => true]);
    }

    public function destroyDraft(Request $request, TaskFile $attachment)
    {
        // Разрешаем только черновые файлы
        if ($attachment->task_id) {
            return response()->json(['message' => 'Not a draft'], 409);
        }

        $userId    = (int) auth()->id();
        $isOwner   = (int) $attachment->user_id === $userId;

        // Авторизация по драфт-токену (берём из заголовка или из тела запроса)
        $tokenHeader = (string) $request->header('X-Draft-Token', '');
        $tokenInput  = (string) $request->input('draft_token', '');
        $token       = $tokenHeader !== '' ? $tokenHeader : $tokenInput;

        $hasDraftMatch = $attachment->draft_token
            && $token !== ''
            && hash_equals($attachment->draft_token, $token);

        if (!($isOwner || $hasDraftMatch)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (!empty($attachment->path)) {
            Storage::disk('public')->delete($attachment->path);
        }
        $attachment->delete();

        return response()->json(['ok' => true]);
    }
}
