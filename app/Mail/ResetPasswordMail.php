<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public function __construct($code)
    {
        $this->code=$code;
    }

public function build()
{
    $body = "Hello,\n\n";
    $body .= "You have requested to reset your password.\n";
    $body .= "👉 Your reset code is: {$this->code}\n\n";
    $body .= "This code is valid for 15 minutes.\n";
    $body .= "If you did not request this, please ignore this email.\n\n";
    $body .= "Regards,\n";
    $body .= config('app.name');

    return $this->subject('Reset Your Password')
                ->text('empty') // ملف وهمي فقط
                ->withSwiftMessage(function ($message) use ($body) {
                    $message->setBody($body, 'text/plain');
                });
}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Password Mail',
        );
    }

    /**
     * Get the message content definition.
     */
/*     public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
    } */

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
