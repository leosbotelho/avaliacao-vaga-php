<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Password;

class PasswordResetMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // pra abrir espaço pra uma lógica mais complexa
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public string $email)
    {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $status = Password::sendResetLink(['email' => $this->email]);
      if ($status !== Password::RESET_LINK_SENT) {
        $this->fail($status);
      }
    }
}
