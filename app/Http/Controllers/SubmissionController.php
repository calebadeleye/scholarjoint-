<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Submission;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Submission::where('user_id', $request->user()->id)->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'abstract' => 'required|string',
            'file'     => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

        $path = $request->file('file')->store('submissions', 'public');

        $submission = Submission::create([
            'title'    => $request->title,
            'abstract' => $request->abstract,
            'file'     => $path,
            'user_id'  => $request->user()->id,
        ]);

        return response()->json($submission, 201);
    }

    public function show($id)
    {
        $submission = Submission::findOrFail($id);
        return response()->json($submission);
    }
}
