<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Mailback extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct($status,$reason){
        $this->status = $status;
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->view('emails.backMail',[
            'status'=>  $this->status,
            'reason'=> $this->reason
        ]);
    }
}
