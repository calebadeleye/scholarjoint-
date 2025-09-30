<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function index()
    {
        return response()->json(Journal::with('issues')->get());
    }

    public function store(Request $request)
    {
        if (!in_array($request->user()->role, ['admin', 'editor'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|unique:journals',
            'description' => 'nullable|string'
        ]);

        $journal = Journal::create($data);
        return response()->json($journal, 201);
    }

    public function show($id)
    {
        $journal = Journal::with('issues')->findOrFail($id);
        return response()->json($journal);
    }
}

