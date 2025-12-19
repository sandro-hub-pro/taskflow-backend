<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class VerifyEmailNotification extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Your Email Address - TaskFlow')
            ->view('emails.verify-email', ['verificationUrl' => $verificationUrl]);
    }

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable): string
    {
        $frontendUrl = config('app.frontend_url', config('app.url'));
        
        $id = $notifiable->getKey();
        $hash = sha1($notifiable->getEmailForVerification());
        $expires = Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60))->timestamp;

        // For SPA frontend, build URL with verification params
        return $frontendUrl . '/verify-email?' . http_build_query([
            'id' => $id,
            'hash' => $hash,
            'expires' => $expires,
        ]);
    }
}

