@inject('carbon', '\Carbon\Carbon')

@section('title')
    {{ $api->meta->title }} / @lang('buttons.edit')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.update', $api->path) }}" accept-charset="UTF-8" id="edit-form" data-ajax>
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="input-activity_id">@lang('labels.activity')</label>
            <select autofocus id="input-activity_id" class="form-control" name="activity_id">
                @foreach ($api->model->selectActivities() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->activity_id == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-started_at">@lang('labels.startedAt')</label>
            <input id="input-started_at" class="form-control" required placeholder="@lang('placeholders.startedAt')" name="started_at" type="text" value="{{ $api->model->started_at }}">
        </div>

        <div class="form-group">
            <label for="input-finished_at">@lang('labels.finishedAt')</label>
            <input id="input-finished_at" class="form-control" placeholder="@lang('placeholders.finishedAt')" name="finished_at" type="text" value="{{ $api->model->finished_at }}" {{ $api->model->started_at ? '' : 'disabled' }}>
        </div>

        <div class="form-group">
            <label for="input-description">@lang('labels.description')</label>
            <textarea id="input-description" class="form-control" placeholder="@lang('placeholders.description')" name="description">{{ $api->model->description }}</textarea>
        </div>

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
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
    $('#input-finished_at').datepicker({
        {!! $api->model->started_at ? 'minDate: new Date("' . $carbon->parse($api->model->started_at) . '"),' : '' !!}
    });
@endsection
