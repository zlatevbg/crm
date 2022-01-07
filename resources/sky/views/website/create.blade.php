@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->meta->slug) }}" accept-charset="UTF-8" id="create-form" data-ajax>
        @csrf

        <div class="form-group">
            <label for="input-name">@lang('labels.name')</label>
            <input id="input-name" class="form-control" autofocus required placeholder="@lang('placeholders.name')" name="name" type="text">
        </div>

        <div class="form-group">
            <label for="input-website">@lang('labels.website')</label>
            <input id="input-website" class="form-control" autofocus required placeholder="@lang('placeholders.website')" name="website" type="text">
        </div>

        <div class="form-group">
            <label for="input-analytics">@lang('labels.analyticsViewId')</label>
            <input id="input-analytics" class="form-control" autofocus required placeholder="@lang('placeholders.analyticsViewId')" name="analytics" type="text">
        </div>

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection
