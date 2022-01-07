@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->path) }}" accept-charset="UTF-8" id="create-form" data-ajax>
        @csrf

        <div class="form-group">
            <label for="input-name">@lang('labels.name')</label>
            <input id="input-name" class="form-control" autofocus required placeholder="@lang('placeholders.name')" name="name" type="text">
        </div>

        <div class="form-group">
            <label for="input-score">@lang('labels.score')</label>
            <input id="input-score" class="form-control" required placeholder="@lang('placeholders.score')" name="score" min="0" max="100" type="number">
        </div>

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection
