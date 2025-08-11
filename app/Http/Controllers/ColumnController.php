<?php

namespace App\Http\Controllers;

use App\Models\{Project, TaskBoard, TaskColumn};
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    public function store(Request $r, Project $project)
    {
        $r->validate([
            'name'  => 'required|string|max:255',
            'color' => 'nullable|string|max:16',
        ]);
        $board = $project->board;
        $order = ($board->columns()->max('sort_order') ?? 0) + 1;

        $col = $board->columns()->create([
            'name' => $r->name,
            'color'=> $r->color ?: '#94a3b8',
            'sort_order'=> $order,
        ]);

        return response()->json([
            'ok'=>true,
            'column'=> $col->only(['id','name','color','sort_order']),
        ]);
    }

    public function update(Request $r, TaskColumn $column)
    {
        $data = $r->validate([
            'name'  => 'sometimes|required|string|max:255',
            'color' => 'sometimes|nullable|string|max:16',
        ]);
        $column->update($data);
        return response()->json(['ok'=>true]);
    }

    public function destroy(TaskColumn $column)
    {
        $column->delete();
        return response()->json(['ok'=>true]);
    }

    public function reorder(Request $r, Project $project)
    {
        $r->validate(['order'=>'required|array']);
        foreach ($r->order as $idx => $id) {
            TaskColumn::where('id',$id)->where('board_id',$project->board->id)
                ->update(['sort_order' => $idx+1]);
        }
        return response()->json(['ok'=>true]);
    }
}
