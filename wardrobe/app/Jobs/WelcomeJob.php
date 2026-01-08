<?php

namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

// Mail
use App\Mail\NewUserMail;
// Helper
use App\Helpers\Generator;
// Model
use App\Models\FailedJob;

class WelcomeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $username;
    protected $receiver;
    protected $token;

    public function __construct($username, $receiver, $token)
    {
        $this->username = $username;
        $this->receiver = $receiver;
        $this->token = $token;
    }

    public function handle()
    {
        try{
            // Mailer
            $email = new NewUserMail($this->username, $this->token);
            Mail::to($this->receiver)->send($email);
        } catch (\Exception $e) {
            // Consume error
            $obj = [
                'message' => Generator::getMessageTemplate("unknown_error", null), 
                'stack_trace' => $e->getTraceAsString(), 
                'file' => $e->getFile(), 
                'line' => $e->getLine(), 
            ];

            // Create failed job
            FailedJob::createFailedJob([
                'type' => "register", 
                'status' => "failed",  
                'payload' => json_encode($obj),
            ], null);
        }
    }
}
