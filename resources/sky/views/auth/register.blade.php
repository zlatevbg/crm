@extends('layouts.auth')

@section('content')
    <h1 class="card-title text-center">@lang('text.registerTitle')</h1>

    <form method="POST" action="{{ Helper::route('register') }}" accept-charset="UTF-8" id="register-form" data-ajax>
        @csrf

        <div class="form-group floatl">
            <label for="input-name" class="floatl__label">@lang('labels.name')</label>
            <input id="input-name" class="form-control floatl__input" required autofocus placeholder="@lang('placeholders.name')" name="name" type="text">
        </div>

        <div class="form-group floatl">
            <label for="input-email" class="floatl__label">@lang('labels.email')</label>
            <input id="input-email" class="form-control floatl__input" required placeholder="@lang('placeholders.email')" name="email" type="email">
        </div>

        <div class="form-group floatl">
            <label for="input-password" class="floatl__label">@lang('labels.password')</label>
            <input id="input-password" class="form-control floatl__input" required placeholder="@lang('placeholders.password')" name="password" type="password">
        </div>

        <div class="form-group floatl">
            <label for="input-password_confirmation" class="floatl__label">@lang('labels.passwordConfirmation')</label>
            <input id="input-password_confirmation" class="form-control floatl__input" required placeholder="@lang('placeholders.passwordConfirmation')" name="password_confirmation" type="password">
        </div>

        <button type="submit" class="btn btn-primary btn-block fa-left"><i class="fas fa-user-plus"></i>@lang('buttons.register')</button>
    </form>
@endsection
