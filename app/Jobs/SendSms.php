<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $phone;
    public $message;
    public $origin;
    public $sms_id;

    public function __construct($phone, $message,  $origin, $sms_id)
    {
        //

        $this->phone = $phone;
        $this->message = $message;
        $this->origin = $origin;
        $this->sms_id = $sms_id;
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
      send_sms($this->phone, $this->message, $this->origin, $this->sms_id);
    }
}
