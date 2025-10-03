@component('mail::message')
# Hello {{ $user->name }},

Thanks for registering on {{ config('app.name') }}.

Please verify your email address by clicking the button below:

@component('mail::button', ['url' => $verificationUrl])
Verify Email
@endcomponent

If you didn’t create an account, no further action is required.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
