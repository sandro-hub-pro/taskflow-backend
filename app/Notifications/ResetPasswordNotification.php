<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The password reset token.
     */
    public string $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url', config('app.url'));
        $appUrl = config('app.url');
        
        // Build the reset URL
        $email = $notifiable->getEmailForPasswordReset();
        
        // If frontend URL is the same as app URL (Blade templates), use Laravel's route
        if ($frontendUrl === $appUrl) {
            $resetUrl = url(route('password.reset', [
                'token' => $this->token,
                'email' => $email,
            ], false));
        } else {
            // For SPA frontend, redirect to frontend reset page
            $resetUrl = $frontendUrl . '/reset-password?' . http_build_query([
                'token' => $this->token,
                'email' => $email,
            ]);
        }

        return (new MailMessage)
            ->subject('Reset Your Password - TaskFlow')
            ->view('emails.reset-password', ['resetUrl' => $resetUrl]);
    }
}

