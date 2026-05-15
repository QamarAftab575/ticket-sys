<?php

namespace App\Mail;

use App\Models\LoginToken;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $loginToken;
    public string $loginUrl;

    public function __construct(
        public User $user,
        public bool $isNewRegistration = true
    ) {
        $token = Str::random(64);

        LoginToken::create([
            'user_id'    => $user->id,
            'token'      => $token,
            'expires_at' => now()->addDays(7),
        ]);

        // Store the token string — survives serialization
        $this->loginToken = $token;
        $this->loginUrl = route('auth.auto-login', ['token' => $token]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            to: $this->user->email,
            subject: 'Welcome to ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.welcome',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
