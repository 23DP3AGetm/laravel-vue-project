<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        VerifyEmail::createUrlUsing(function (object $notifiable): string {
            $id = $notifiable->getKey();
            $hash = sha1($notifiable->getEmailForVerification());

            $signedUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(config('auth.verification.expire', 60)),
                [
                    'id' => $id,
                    'hash' => $hash,
                ],
                false
            );

            parse_str(parse_url($signedUrl, PHP_URL_QUERY) ?: '', $signedQuery);

            $query = http_build_query([
                'token' => $signedQuery['signature'] ?? '',
                'id' => $id,
                'hash' => $hash,
                'expires' => $signedQuery['expires'] ?? '',
            ]);

            return rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/').'/verify-email?'.$query;
        });

        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            return (new MailMessage)
                ->subject('Apstiprini savu e-pastu')
                ->view('emails.verify-email', [
                    'url' => $url,
                ]);
        });

        ResetPassword::createUrlUsing(function (object $user, string $token): string {
            $query = http_build_query([
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]);

            return rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/').'/reset-password?'.$query;
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $query = http_build_query([
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);

            $url = rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/').'/reset-password?'.$query;

            return (new MailMessage)
                ->subject('Atjauno paroli')
                ->view('emails.reset-password', [
                    'url' => $url,
                ]);
        });
    }
}
