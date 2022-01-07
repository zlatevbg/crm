@extends('layouts.auth')

@section('content')
    <h1 class="card-title text-center">@lang('text.resetPasswordTitle')</h1>

    <form method="POST" action="{{ Helper::route('password.email') }}" accept-charset="UTF-8" id="forgot-password-form" data-ajax>
        @csrf

        <div class="form-group floatl">
            <label for="input-email" class="floatl__label">@lang('labels.email')</label>
            <input id="input-email" class="form-control floatl__input" required autofocus placeholder="@lang('placeholders.email')" name="email" type="email">
        </div>

        <button type="submit" class="btn btn-primary btn-block fa-left"><i class="fas fa-paper-plane"></i>@lang('buttons.emailPassword')</button>
    </form>

    <p class="mb-0 mt-1 text-center">
        <a href="{{ Helper::route() }}">@lang('text.signIn')</a>
    </p>
@endsection
