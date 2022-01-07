@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->path) }}" accept-charset="UTF-8" id="create-form" data-ajax>
        @csrf

        <div class="form-group">
            <label for="input-activity_id">@lang('labels.activity')</label>
            <select autofocus id="input-activity_id" class="form-control" name="activity_id">
                <option value="" selected="selected">@lang('placeholders.activity')</option>
                @foreach ($api->model->selectActivities() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-started_at">@lang('labels.startedAt')</label>
            <input id="input-started_at" class="form-control" required placeholder="@lang('placeholders.startedAt')" name="started_at" type="text">
        </div>

        <div class="form-group">
            <label for="input-finished_at">@lang('labels.finishedAt')</label>
            <input id="input-finished_at" class="form-control" disabled placeholder="@lang('placeholders.finishedAt')" name="finished_at" type="text">
        </div>

        <div class="form-group">
            <label for="input-description">@lang('labels.description')</label>
            <textarea id="input-description" class="form-control" placeholder="@lang('placeholders.description')" name="description"></textarea>
        </div>

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection

@section('callback')
    $.datepicker.regional.{{ App::getLocale() }} = unikatSettings.datepicker.{{ App::getLocale() }};
    $.datepicker.setDefaults($.datepicker.regional.{{ App::getLocale() }});
    $('#input-started_at').datepicker({
        onSelect: function(date) {
            var d = new Date(Date.parse($("#input-started_at").datepicker("getDate")));
            $('#input-finished_at').datepicker('option', 'minDate', d);
            $('#input-finished_at').removeAttr('disabled');
        },
    });
    $('#input-finished_at').datepicker();
@endsection
