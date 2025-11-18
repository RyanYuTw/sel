<?php

namespace App\Http\Controllers;

use App\Models\Handbook;
use Illuminate\Http\Request;

class HandbookController extends Controller
{
    public function index()
    {
        return Handbook::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'grade' => 'required|integer',
            'semester' => 'required|string',
            'lesson' => 'required|string',
            'content' => 'nullable|string',
        ]);

        return Handbook::create($request->all());
    }

    public function show(Handbook $handbook)
    {
        return $handbook;
    }

    public function update(Request $request, Handbook $handbook)
    {
        $request->validate([
            'year' => 'required|integer',
            'grade' => 'required|integer',
            'semester' => 'required|string',
            'lesson' => 'required|string',
            'content' => 'nullable|string',
        ]);

        $handbook->update($request->all());
        return $handbook;
    }

    public function destroy(Handbook $handbook)
    {
        $handbook->delete();
        return response()->json(['message' => '刪除成功']);
    }
}