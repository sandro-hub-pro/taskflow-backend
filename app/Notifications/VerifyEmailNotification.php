<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

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
        $appUrl = config('app.url');
        
        // Generate the signed verification URL
        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // If frontend URL is the same as app URL (Blade templates), use direct link
        if ($frontendUrl === $appUrl) {
            return $signedUrl;
        }

        // For SPA frontend, redirect to frontend with the signed URL as parameter
        return $frontendUrl . '/verify-email?' . http_build_query([
            'url' => $signedUrl,
        ]);
    }
}

