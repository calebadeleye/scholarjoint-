<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\IssueController;

// ====================== AUTH ======================
// Register new user
Route::post('/register', [AuthController::class, 'register']);
// Login user
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Logout authenticated user
    Route::post('/logout', [AuthController::class, 'logout']);
    // Get current authenticated user
    Route::get('/user', [AuthController::class, 'user']);

    // ====================== SUBMISSIONS ======================
    // List all submissions for logged-in user
    Route::get('/submissions', [SubmissionController::class, 'index']);
    // Create a new submission
    Route::post('/submissions', [SubmissionController::class, 'store']);
    // Show a single submission (by ID)
    Route::get('/submissions/{id}', [SubmissionController::class, 'show']);

    // ====================== REVIEWS ======================
    // Assign a reviewer to a submission (Admin/Editor only)
    Route::post('/submissions/{id}/assign-reviewer', [ReviewController::class, 'assignReviewer']);
    // Submit review for a submission
    Route::post('/submissions/{id}/review', [ReviewController::class, 'submitReview']);
    // Update submission status (accepted/rejected etc.)
    Route::post('/submissions/{id}/status', [ReviewController::class, 'updateStatus']);

    // ====================== PAYMENTS ======================
    // Create a new payment record
    Route::post('/payments', [PaymentController::class, 'store']);
    // List all payments for logged-in user
    Route::get('/payments', [PaymentController::class, 'index']);
    // Verify payment with external gateway
    Route::post('/payments/verify', [PaymentController::class, 'verify']);

    // ====================== JOURNALS ======================
    // List all journals with issues
    Route::get('/journals', [JournalController::class, 'index']);
    // Create a new journal (Admin/Editor only)
    Route::post('/journals', [JournalController::class, 'store']);
    // Show single journal details with issues
    Route::get('/journals/{id}', [JournalController::class, 'show']);

    // ====================== ISSUES ======================
    // List all issues for a specific journal
    Route::get('/journals/{journalId}/issues', [IssueController::class, 'index']);
    // Create a new issue for a journal (Admin/Editor only)
    Route::post('/journals/{journalId}/issues', [IssueController::class, 'store']);
    // Show single issue details with its journal
    Route::get('/issues/{id}', [IssueController::class, 'show']);
});


Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])
    ->name('verification.send');

