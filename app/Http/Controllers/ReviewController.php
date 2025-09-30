<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Submission;

class ReviewController extends Controller
{
    public function assignReviewer(Request $request, $id)
    {
        $request->validate([
            'reviewer_id' => 'required|integer|exists:users,id',
        ]);

        $review = Review::create([
            'submission_id' => $id,
            'reviewer_id'   => $request->reviewer_id,
            'status'        => 'assigned',
        ]);

        return response()->json($review, 201);
    }

    public function submitReview(Request $request, $id)
    {
        $request->validate([
            'comments' => 'required|string',
            'score'    => 'required|integer|min:1|max:10',
        ]);

        $review = Review::where('submission_id', $id)
            ->where('reviewer_id', $request->user()->id)
            ->firstOrFail();

        $review->update([
            'comments' => $request->comments,
            'score'    => $request->score,
            'status'   => 'completed',
        ]);

        return response()->json($review);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,accepted,rejected',
        ]);

        $submission = Submission::findOrFail($id);
        $submission->status = $request->status;
        $submission->save();

        return response()->json($submission);
    }
}
