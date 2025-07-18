<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\CyberComplaint;

class CyberComplaintStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $Cybercomplaint;

    public function __construct(CyberComplaint $Cybercomplaint)
    {
        $this->Cybercomplaint = $Cybercomplaint;
    }

  public function build()
{
    $body = "السلام عليكم،\n\n"
        . "تم تحديث حالة الشكوى رقم: {$this->Cybercomplaint->id} إلى الحالة: {$this->Cybercomplaint->status}.\n\n"
        . "شكراً لاستخدامك منصتنا.";

    return $this->subject('تم تحديث حالة شكواك')
                ->html(nl2br(e($body)));
}

}
