@extends('layouts.auth')

@section('content')
    <h1 class="card-title text-center">@lang('text.signInTitle')</h1>

    <form method="POST" action="{{ Helper::route() }}" accept-charset="UTF-8" id="login-form" data-ajax>
        @csrf

        <div class="form-group floatl">
            <label for="input-email" class="floatl__label">@lang('labels.email')</label>
            <input id="input-email" class="form-control floatl__input" required autofocus placeholder="@lang('placeholders.email')" name="email" type="email">
        </div>

        <div class="form-group floatl">
            <label for="input-password" class="floatl__label">@lang('labels.password')</label>
            <input id="input-password" class="form-control floatl__input" required placeholder="@lang('placeholders.password')" name="password" type="password">
        </div>

        <div class="form-group">
            <input id="input-remember" class="checkbox-inline" name="remember" type="checkbox" value="1">
            <label for="input-remember">@lang('labels.rememberMe')</label>
        </div>

        <button type="submit" class="btn btn-primary btn-block fa-left"><i class="fas fa-sign-in-alt"></i>@lang('buttons.signIn')</button>
    </form>

    <p class="mb-0 mt-1 text-center">
        <a href="{{ Helper::route('password.reset') }}">@lang('text.passwordReset')</a>
    </p>
@endsection
