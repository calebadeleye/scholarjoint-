<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReviewInviteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $review;
    public $submission;

    /**
     * Create a new message instance.
     */
    public function __construct($review, $submission)
    {
        $this->review = $review;
        $this->submission = $submission;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invitation to Review: {$this->submission->title}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $agree = url("/review/response/{$this->review->token}/agreed");
        $decline = url("/review/response/{$this->review->token}/declined");
        $unavailable = url("/review/response/{$this->review->token}/unavailable");

        return new Content(
            markdown: 'emails.review_invite',
            with: [
                'name' => $this->review->reviewer_name,
                'title' => $this->submission->title,
                'abstract' => $this->submission->abstract,
                'agree' => $agree,
                'decline' => $decline,
                'unavailable' => $unavailable,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
