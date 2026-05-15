<x-mail::message>
# Verify Your Email Address

Hello {{ $user->name }},

Please verify your email address by clicking the button below to complete your registration.

<x-mail::button :url="$verificationUrl">
Verify Email
</x-mail::button>

If you did not create this account, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
