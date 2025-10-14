@component('mail::message')
# Invitation to Review: {{ $title }}

Dear {{ $name }},

You have been invited to review the following abstract:

> "{{ $abstract }}"

Please indicate your availability below:

@component('mail::button', ['url' => $agree])
Agree to Review
@endcomponent

@component('mail::button', ['url' => $decline])
Decline Invitation
@endcomponent

@component('mail::button', ['url' => $unavailable])
Unavailable
@endcomponent

Thanks,  
**{{ config('app.name') }}**
@endcomponent
