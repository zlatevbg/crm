@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->meta->slug) }}" accept-charset="UTF-8" id="create-form" data-ajax>
        @csrf

        <div class="form-group">
            <label for="input-domain">@lang('labels.domain')</label>
            <input id="input-domain" class="form-control" autofocus required placeholder="@lang('placeholders.domain')" name="domain" type="text">
        </div>

        <div class="form-group">
            <label for="input-name">@lang('labels.name')</label>
            <input id="input-name" class="form-control" required placeholder="@lang('placeholders.name')" name="name" type="text">
        </div>

        <div class="form-group">
            <label for="input-auth">@lang('labels.authRoute')</label>
            <input id="input-auth" class="form-control" required placeholder="@lang('placeholders.authRoute')" name="auth" type="text">
        </div>

        <div class="form-group">
            <label for="input-guest">@lang('labels.guestRoute')</label>
            <input id="input-guest" class="form-control" required placeholder="@lang('placeholders.guestRoute')" name="guest" type="text">
        </div>

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection
