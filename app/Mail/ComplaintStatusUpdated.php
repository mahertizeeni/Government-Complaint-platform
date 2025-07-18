<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Complaint;

class ComplaintStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $complaint;

    public function __construct(Complaint $complaint)
    {
        $this->complaint = $complaint;
    }

  public function build()
{
    $body = "السلام عليكم،\n\n"
        . "تم تحديث حالة الشكوى رقم: {$this->complaint->id} إلى الحالة: {$this->complaint->status}.\n\n"
        . "شكراً لاستخدامك منصتنا.";

    return $this->subject('تم تحديث حالة شكواك')
                ->html(nl2br(e($body)));
}

}
