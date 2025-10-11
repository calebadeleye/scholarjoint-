<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    /**
     * Display all submissions.
     */
    public function index()
    {
        $submissions = Submission::with(['user','conference','authors'])->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $submissions
        ]);
    }

    /**
     * Store a new submission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'conference_id' => 'required|exists:conferences,id',
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'abstract' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    $wordCount = str_word_count(strip_tags($value));
                    if ($wordCount > 350) {
                        $fail('The abstract may not exceed 350 words.');
                    }
                },
            ],
            'authors' => 'required|array|min:1',
            'authors.*.name' => 'required|string|max:255',
            'authors.*.email' => 'nullable|email',
            'authors.*.institution' => 'nullable|string|max:255',
            'authors.*.department' => 'nullable|string|max:255',
            'authors.*.is_corresponding' => 'boolean',
        ]);

        // Create submission
        $submission = Submission::create($validated);

        // Attach authors
        foreach ($validated['authors'] as $authorData) {
            $submission->authors()->create($authorData);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Submission created successfully!',
            'data' => $submission->load('authors')
        ], 201);
    }


    /**
     * Display a specific submission.
     */
    public function show($id)
    {
        $submission = Submission::with(['user', 'conference','authors'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $submission
        ]);
    }

    /**
     * Update a submission.
     */
    public function update(Request $request, $id)
    {
        $submission = Submission::findOrFail($id);

        $validated = $request->validate([
            'conference_id' => 'sometimes|exists:conferences,id',
            'title' => 'sometimes|string|max:255',
            'abstract' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    $wordCount = str_word_count(strip_tags($value));
                    if ($wordCount > 350) {
                        $fail('The abstract may not exceed 350 words.');
                    }
                },
            ],
            'status' => 'sometimes|in:pending,under_review,accepted,rejected,revision_required,awaiting_payment,paid',
            'authors' => 'sometimes|array',
            'authors.*.id' => 'sometimes|exists:submission_authors,id', // for existing authors
            'authors.*.name' => 'required_with:authors|string|max:255',
            'authors.*.email' => 'nullable|email',
            'authors.*.institution' => 'nullable|string|max:255',
            'authors.*.department' => 'nullable|string|max:255',
            'authors.*.is_corresponding' => 'boolean',
        ]);

        // Update submission fields
        $submission->update($validated);

        // Handle authors if provided
        if (isset($validated['authors'])) {
            foreach ($validated['authors'] as $authorData) {
                if (isset($authorData['id'])) {
                    // Update existing author
                    $author = $submission->authors()->find($authorData['id']);
                    if ($author) {
                        $author->update($authorData);
                    }
                } else {
                    // Create new author
                    $submission->authors()->create($authorData);
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Submission updated successfully!',
            'data' => $submission->load('authors', 'conference', 'user')
        ]);
    }


    /**
     * Delete a submission.
     */
    public function destroy($id)
    {
        $submission = Submission::findOrFail($id);
        $submission->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Submission deleted successfully.'
        ]);
    }
}
