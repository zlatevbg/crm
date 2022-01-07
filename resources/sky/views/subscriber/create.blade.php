@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->path) }}" accept-charset="UTF-8" id="create-form" data-ajax novalidate>
        @csrf

        @if (!$api->model->_parent)
            <div class="form-group">
                <label for="input-website">@lang('labels.website')</label>
                <select id="input-website" class="form-control" name="website">
                    <option value="" selected="selected">@lang('placeholders.website')</option>
                    @foreach ($api->model->selectWebsite() as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="form-group">
            <label for="input-email">@lang('labels.email')</label>
            <input id="input-email" class="form-control" required autofocus placeholder="@lang('placeholders.email')" name="email" type="email">
        </div>

        <div class="form-group">
            <label for="input-source">@lang('labels.source')</label>
            <select id="input-source" class="form-control" name="source">
                <option value="" selected="selected">@lang('placeholders.source')</option>
                @foreach ($api->model->selectSource() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-is_subscribed">@lang('labels.newslettersSubscription')</label>
            <select id="input-is_subscribed" class="form-control" name="is_subscribed">
                @foreach ($api->model->selectNewslettersSubscription() as $key => $value)
                    <option value="{{ $key }}" {!! $key == 1 ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection
