<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Submission;
use Illuminate\Support\Str;
use App\Mail\ReviewInviteMail;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReviewNotificationMail;

class ReviewController extends Controller
{

    public function assignReviewer(Request $request, $id)
    {
        $request->validate([
            'reviewer_name' => 'required|string|max:255',
            'reviewer_email' => 'required|email',
        ]);

        $submission = Submission::findOrFail($id);

        // Create a unique token for tracking this review invitation
        $token = Str::uuid();

        // Create the review record
        $review = Review::create([
            'submission_id'  => $submission->id,
            'reviewer_name'  => $request->reviewer_name,
            'reviewer_email' => $request->reviewer_email,
            'token'          => $token, // make sure your reviews table has a token column
            'status'         => 'assigned',
        ]);

        // Update submission status
        $submission->update(['status' => 'under_review']);

        // Queue the review invitation email
        Mail::to($review->reviewer_email)->queue(new ReviewInviteMail($review, $submission));

        return response()->json([
            'status' => 'success',
            'message' => 'Review invitation sent successfully!',
            'data' => $review,
        ]);
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

    public function respond($token, $decision)
    {
        $review = Review::where('token', $token)->firstOrFail();

        if (!in_array($decision, ['agreed', 'declined', 'unavailable'])) {
            abort(400, 'Invalid decision');
        }

        // Update the decision
        $review->update(['decision' => $decision]);

        // If reviewer is unavailable, just show a thank you page
        if ($decision === 'unavailable') {
            return view('reviews.unavailable', compact('review'));
        }

        // For agree or decline, show form to leave comment
        return view('reviews.comment_form', [
            'review' => $review,
            'decision' => $decision
        ]);
    }


    public function comment(Request $request, $token)
    {
        $review = Review::with('submission.conference.user')->where('token', $token)->firstOrFail();

        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        try {
            // Update review comment
            $review->update(['comment' => $request->comment]);

            // Update submission status
            $review->submission->update(['status' => 'under_review']);

            // Get the admin (conference creator)
            $admin = $review->submission->conference->user;

            // Queue the email to admin
            Mail::to($admin->email)->queue(new ReviewNotificationMail($review));

            return redirect()
                ->back()
                ->with('success', 'Comment saved and notification sent to the conference admin successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

}
