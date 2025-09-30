<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Journal;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    public function index($journalId)
    {
        $journal = Journal::with('issues')->findOrFail($journalId);
        return response()->json($journal->issues);
    }

    public function store(Request $request, $journalId)
    {
        if (!in_array($request->user()->role, ['admin', 'editor'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'volume' => 'required|string',
            'issue_number' => 'required|string',
            'publication_date' => 'nullable|date',
        ]);

        $journal = Journal::findOrFail($journalId);
        $issue = $journal->issues()->create($data);

        return response()->json($issue, 201);
    }

    public function show($id)
    {
        $issue = Issue::with('journal')->findOrFail($id);
        return response()->json($issue);
    }
}
