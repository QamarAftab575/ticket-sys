<x-mail::message>
# Password Changed Successfully

Hello {{ $user->name }},

Your password has been successfully changed.

If you did not make this change, please contact our support team immediately or reset your password.

<x-mail::button url="{{ config('app.url') }}/profile">
Go to Profile
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
