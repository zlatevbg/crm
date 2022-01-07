@extends('layouts.auth')

@section('content')
    <h1 class="card-title text-center">@lang('text.resetPasswordTitle')</h1>

    <form method="POST" action="{{ Helper::route('password.reset') }}" accept-charset="UTF-8" id="reset-form" data-ajax>
        @csrf
        <input name="token" type="hidden" value="{{ $token }}">

        <div class="form-group floatl">
            <label for="input-email" class="floatl__label">@lang('labels.email')</label>
            <input id="input-email" class="form-control floatl__input" required placeholder="@lang('placeholders.email')" name="email" type="email" value="{{ $email }}">
        </div>

        <div class="form-group floatl">
            <label for="input-password" class="floatl__label">@lang('labels.password')</label>
            <input id="input-password" class="form-control floatl__input" required autofocus placeholder="@lang('placeholders.password')" name="password" type="password">
        </div>

        <div class="form-group floatl">
            <label for="input-password_confirmation" class="floatl__label">@lang('labels.passwordConfirmation')</label>
            <input id="input-password_confirmation" class="form-control floatl__input" required placeholder="@lang('placeholders.passwordConfirmation')" name="password_confirmation" type="password">
        </div>

        <button type="submit" class="btn btn-primary btn-block fa-left"><i class="fas fa-undo"></i>@lang('buttons.resetPassword')</button>
    </form>
@endsection
