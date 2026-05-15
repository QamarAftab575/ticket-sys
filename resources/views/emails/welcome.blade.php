<x-mail::message>
# Welcome to {{ config('app.name') }}

Hello {{ $user->fullName }},

@if($isNewRegistration)
Your account has been successfully created and is ready to use.
@else
You've accepted your invitation and your account is all set.
@endif

Click the button below to go to your dashboard.

<x-mail::button url="{{ config('app.url') }}">
Go to Dashboard
</x-mail::button>

If you didn't create this account, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
