<x-mail::message>
# {{ $notifSubject }}

{{ $body }}

<x-mail::button :url="config('app.url') . '/dashboard'">
Buka Dashboard
</x-mail::button>

Terima kasih,
{{ config('app.name') }}
</x-mail::message>
