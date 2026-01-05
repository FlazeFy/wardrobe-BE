<?php

namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewUserMail extends Mailable
{
    use Queueable, SerializesModels;
    public $username;
    public $token;

    public function __construct($username, $token)
    {
        $this->username = $username;
        $this->token = $token;
    }

    public function envelope()
    {
        return new Envelope(
            subject: '[Account] Welcome to Wardrobe',
        );
    }

    public function build()
    {
        return $this->view('components.email.new_user')
            ->with([
                'token' => $this->token,
                'username' => $this->username,
            ]);
    }

    public function attachments()
    {
        return [];
    }
}
