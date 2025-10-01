<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\TaskRequest;
use App\Http\Resources\Task\TaskListResource;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index()
    {
        $data = Task::orderBy('id', 'desc')->with('keywords')->get();
        $task = TaskListResource::collection($data);

        return response()->json([
            'code' => 200,
            'data' => $task
        ]);
    }

    public function store(TaskRequest $request)
    {
        DB::beginTransaction();
        try {
            $task = new Task();
            $task->title = $request->title;
            $task->save();

            // Asignar keywords existentes por IDs
            if ($request->has('keyword_ids') && !empty($request->keyword_ids)) {
                $task->keywords()->attach($request->keyword_ids);
            }

            DB::commit();

            $task->load('keywords');

            return response()->json([
                'code' => 200,
                'message' => "Tarea creada correctamente.",
                'data' => $task,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'message' => "Se evidencia algunos errores.",
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ], 500);
        }
    }

    public function toggle($id)
    {
        DB::beginTransaction();
        try {
            $task = Task::findOrFail($id);
            $task->is_done = !$task->is_done;
            $task->save();

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => "Estado de la Tarea cambiado correctamente.",
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'message' => "Se evidencia algunos errores.",
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ], 500);
        }
    }
}
