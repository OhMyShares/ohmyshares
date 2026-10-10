@extends('layouts.mail')

@section('content')
    {{ __('Hello') }}, {{ $user->name }}!<br>
    {{ __('messages.reset_password_notice') }}
    <div style="padding: 15px 0;">
        <a href="{{ route('app', ['url' => 'reset-password', 'token' => $token]) }}"
           class="btn btn-primary"
           target="_blank"
           rel="nofollow noopener noreferrer">
            {{ __('Reset password') }}
        </a>
    </div>
    <p class="text-small text-muted">
        {{ __('messages.reset_password_link') }}:<br>
        {{ route('app', ['url' => 'reset-password', 'token' => $token]) }}<br>
    </p>
@endsection
