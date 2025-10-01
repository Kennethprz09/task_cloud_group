<?php

namespace App\Http\Controllers;

use App\Http\Requests\Keyword\KeywordRequest;
use App\Http\Resources\Keyword\KeywordListResource;
use App\Models\Keyword;
use Illuminate\Support\Facades\DB;

class KeywordController extends Controller
{
    public function index()
    {
        $data = Keyword::orderBy('id', 'desc')->get();
        $Keyword = KeywordListResource::collection($data);

        return response()->json([
            'code' => 200,
            'data' => $Keyword
        ]);
    }

    public function store(KeywordRequest $request)
    {
        DB::beginTransaction();
        try {
            $task = new Keyword();
            $task->name = $request->name;
            $task->save();

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => "Palabra clave creada correctamente.",
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
}
