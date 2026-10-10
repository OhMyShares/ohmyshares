@extends('layouts.mail')

@section('content')
    {{ __('Hello') }}, {{ $user->name }}!<br>
    {{ __('messages.signup_thanks') }}
    <p>
        {{ __('messages.signup_password') }}: <code>{{ $password }}</code>
    </p>
    <p>
        {{ __('messages.signup_password_change') }}.
    </p>
@endsection
