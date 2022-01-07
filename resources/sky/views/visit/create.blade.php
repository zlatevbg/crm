@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->path) }}" accept-charset="UTF-8" id="create-form" data-ajax novalidate>
        @csrf

        <input id="input-project_id" name="project_id" value="{{ $api->model->_parent->id }}" type="hidden">

        <div class="form-group">
            <label for="input-visited_at">@lang('labels.visitedAt')</label>
            <input id="input-visited_at" class="form-control" autofocus required placeholder="@lang('placeholders.visitedAt')" name="visited_at" type="text" value="{{ date('d.m.Y') }}">
        </div>

        <div class="form-group">
            <label for="input-name">@lang('labels.name')</label>
            <input id="input-name" class="form-control" required placeholder="@lang('placeholders.name')" name="name" type="text">
        </div>

        <div class="form-group">
            <label for="input-description">@lang('labels.comments')</label>
            <textarea id="input-description" class="form-control" placeholder="@lang('placeholders.comments')" name="description"></textarea>
        </div>

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection

@section('callback')
    $.datepicker.regional.{{ App::getLocale() }} = unikatSettings.datepicker.{{ App::getLocale() }};
    $.datepicker.setDefaults($.datepicker.regional.{{ App::getLocale() }});
    $('#input-visited_at').datepicker({
        maxDate: 0,
    });
@endsection
