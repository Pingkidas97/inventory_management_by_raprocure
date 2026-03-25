<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class CustomMail extends Mailable
{
    public $subject;
    public $view;
    public $data;
    public $attachments;
    public $cc;
    public $bcc;

    public function __construct($subject, $view, $body, $attachments = [], $cc = [], $bcc = [])
    {
        $this->subject = $subject;
        $this->view = $view;
        $this->data = $body;
        $this->attachments = $attachments;
        $this->cc = $cc;
        $this->bcc = $bcc;
    }

    public function build()
    {
        $email = $this->subject($this->subject)
                      ->view($this->view)
                      ->with($this->data);

        // Add CC if provided
        if (!empty($this->cc)) {
            $email->cc($this->cc);
        }

        // Add BCC if provided
        if (!empty($this->bcc)) {
            $email->bcc($this->bcc);
        }

        // Attach files if provided
        if (!empty($this->attachments)) {
            foreach ($this->attachments as $attachment) {
                $email->attach($attachment);
            }
        }
        return $email;
    }

    public function attachments(): array
    {
        // Return the list of attachments (optional)
        return $this->attachments;
    }
}
