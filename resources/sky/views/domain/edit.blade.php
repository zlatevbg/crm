@section('title')
    {{ $api->meta->title }} / @lang('buttons.edit')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.update', $api->path) }}" accept-charset="UTF-8" id="edit-form" data-ajax>
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="input-domain">@lang('labels.domain')</label>
            <input id="input-domain" class="form-control" autofocus required placeholder="@lang('placeholders.domain')" name="domain" type="text" value="{{ $api->model->domain }}">
        </div>

        <div class="form-group">
            <label for="input-name">@lang('labels.name')</label>
            <input id="input-name" class="form-control" required placeholder="@lang('placeholders.name')" name="name" type="text" value="{{ $api->model->name }}">
        </div>

       <div class="form-group">
            <label for="input-auth">@lang('labels.authRoute')</label>
            <input id="input-auth" class="form-control" required placeholder="@lang('placeholders.authRoute')" name="auth" type="text" value="{{ $api->model->auth }}">
        </div>

        <div class="form-group">
            <label for="input-guest">@lang('labels.guestRoute')</label>
            <input id="input-guest" class="form-control" required placeholder="@lang('placeholders.guestRoute')" name="guest" type="text" value="{{ $api->model->guest }}">
        </div>

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
    </form>
@endsection
