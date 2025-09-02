<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exercise;

class ExerciseController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->exercises;
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'nullable|integer|min:1',
            'date' => 'required|date',
        ]);

        $exercise = $request->user()->exercises()->create($request->all());

        return response()->json($exercise, 201);
    }

    public function show(Request $request, $id)
    {
        return $request->user()->exercises()->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $exercise = $request->user()->exercises()->findOrFail($id);

        $exercise->update($request->all());

        return response()->json($exercise);
    }

    public function destroy(Request $request, $id)
    {
        $exercise = $request->user()->exercises()->findOrFail($id);

        $exercise->delete();

        return response()->json(null, 204);
    }
}