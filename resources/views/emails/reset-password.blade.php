<x-mail::message>
# Reset Your Password

Hello {{ $user->name }},

You requested to reset your password. Click the button below to set a new password.

<x-mail::button :url="$resetUrl">
Reset Password
</x-mail::button>

**Security Notice:** If you did not request this password reset, please ignore this email. Your account remains secure.

This link will expire in 60 minutes.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
