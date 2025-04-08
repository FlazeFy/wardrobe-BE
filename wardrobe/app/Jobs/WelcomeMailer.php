<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Mail\NewUserMail;
use Illuminate\Support\Facades\Mail;

use App\Helpers\Generator;
use App\Models\FailedJob;

class WelcomeMailer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $username;
    protected $receiver;
    protected $token;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($username, $receiver, $token)
    {
        $this->username = $username;
        $this->receiver = $receiver;
        $this->token = $token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $email = new NewUserMail($this->username, $this->token);
            Mail::to($this->receiver)->send($email);
        } catch (\Exception $e) {
            $obj = [
                'message' => Generator::getMessageTemplate("unknown_error", null), 
                'stack_trace' => $e->getTraceAsString(), 
                'file' => $e->getFile(), 
                'line' => $e->getLine(), 
            ];
            FailedJob::create([
                'id' => Generator::getUUID(), 
                'type' => "register", 
                'status' => "failed",  
                'payload' => json_encode($obj),
                'created_at' => date("Y-m-d H:i:s"), 
                'faced_by' => '1'
            ]);
        }
    }
}
