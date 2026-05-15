<x-mail::message>
# You're invited to join {{ $organizationName }}

**{{ $invitedByName }}** has invited you to join **{{ $organizationName }}** on {{ config('app.name') }}.

Click the button below to accept your invitation and get started.

<x-mail::button :url="$acceptUrl">
Accept Invitation
</x-mail::button>

This invitation expires on **{{ $expiresAt->format('F j, Y') }}**.

If you weren't expecting this invitation, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
