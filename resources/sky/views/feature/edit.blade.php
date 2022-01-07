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
            <label for="input-score">@lang('labels.score')</label>
            <input id="input-score" class="form-control" required placeholder="@lang('placeholders.score')" name="score" min="0" max="100" type="number" value="{{ $api->model->score }}">
        </div>

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
    </form>
@endsection
