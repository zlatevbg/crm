@section('title')
    {{ $api->meta->title }} / @lang('buttons.edit')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.update', $api->path) }}" accept-charset="UTF-8" id="edit-form" data-ajax>
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="input-name">@lang('labels.name')</label>
            <input id="input-name" class="form-control" autofocus required placeholder="@lang('placeholders.name')" name="name" type="text" value="{{ $api->model->name }}">
        </div>

        <div class="form-group">
            <label for="input-website">@lang('labels.website')</label>
            <input id="input-website" class="form-control" autofocus required placeholder="@lang('placeholders.website')" name="website" type="text" value="{{ $api->model->website }}">
        </div>

        <div class="form-group">
            <label for="input-analytics">@lang('labels.analyticsViewId')</label>
            <input id="input-analytics" class="form-control" autofocus required placeholder="@lang('placeholders.analyticsViewId')" name="analytics" type="text" value="{{ $api->model->analytics }}">
        </div>

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
    </form>
@endsection
