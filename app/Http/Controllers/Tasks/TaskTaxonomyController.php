<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskTaxonomyController extends Controller
{
    public function sync(Request $request, Task $task)
    {
        $data = $request->validate([
            'randlables'   => ['sometimes','array'],
            'randlables.*' => ['integer','min:1'],
            'grade_id'     => ['sometimes','nullable','integer','min:1'],
        ]);

        return DB::transaction(function () use ($task, $data) {
            // синк произвольных меток (если передали ключ)
            if (array_key_exists('randlables', $data)) {
                $ids = array_values(array_unique(array_map('intval', $data['randlables'] ?? [])));
                $task->randlables()->sync($ids);
            }

            // обновление оценки (если передали ключ)
            if (array_key_exists('grade_id', $data)) {
                $task->grade_id = $data['grade_id'] ?? null;
                $task->save();
            }

            // вернуть актуальные значения (для UI)
            $labels = $task->randlables()->orderBy('name')->get(['id','name','color']);
            $grade  = $task->grade ? [
                'id'    => $task->grade->id,
                'name'  => $task->grade->name,
                'color' => $task->grade->color,
            ] : null;

            return response()->json([
                'ok'         => true,
                'randlables' => $labels,
                'grade'      => $grade,
            ]);
        });
    }
}
