@inject('carbon', '\Carbon\Carbon')

@section('title')
    {{ $api->meta->title }} / @lang('buttons.edit')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.update', $api->path) }}" accept-charset="UTF-8" id="edit-form" data-ajax>
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="input-title">@lang('labels.title')</label>
            <input id="input-title" class="form-control" required placeholder="@lang('placeholders.title')" name="title" type="text" value="{{ $api->model->title }}">
        </div>

        <div class="form-group">
            <label for="input-month">@lang('labels.month')</label>
            <select autofocus required id="input-month" class="form-control" name="month">
                <option selected="selected">@lang('placeholders.month')</option>
                @foreach ($api->model->selectMonths() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->month == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-start_at">@lang('labels.startAt')</label>
            <input id="input-start_at" class="form-control" placeholder="@lang('placeholders.startAt')" name="start_at" type="text" value="{{ $api->model->start_at }}">
        </div>

        <div class="form-group">
            <label for="input-end_at">@lang('labels.endAt')</label>
            <input id="input-end_at" class="form-control" placeholder="@lang('placeholders.endAt')" name="end_at" type="text" value="{{ $api->model->end_at }}">
        </div>

        <div class="form-group">
            <label for="input-company">@lang('labels.company')</label>
            <input id="input-company" class="form-control" placeholder="@lang('placeholders.company')" name="company" type="text" value="{{ $api->model->company }}">
        </div>

        <div class="form-group">
            <label for="input-time">@lang('labels.time')</label>
            <input id="input-time" class="form-control" placeholder="@lang('placeholders.time')" name="time" type="text" value="{{ $api->model->time }}">
        </div>

        <div class="form-group">
            <label for="input-address">@lang('labels.address')</label>
            <input id="input-address" class="form-control" placeholder="@lang('placeholders.address')" name="address" type="text" value="{{ $api->model->address }}">
        </div>

        <div class="form-group">
            <label for="input-type">@lang('labels.type')</label>
            <input id="input-type" class="form-control" placeholder="@lang('placeholders.type')" name="type" type="text" value="{{ $api->model->type }}">
        </div>

        <div class="form-group">
            <label for="input-link">@lang('labels.link')</label>
            <input id="input-link" class="form-control" required placeholder="@lang('placeholders.link')" name="link" type="url" value="{{ $api->model->link }}">
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

    $('#input-start_at').datepicker({
        onSelect: function(date) {
            var d = new Date(Date.parse($("#input-start_at").datepicker("getDate")));
            $('#input-end_at').datepicker('option', 'minDate', d);
            $('#input-end_at').removeAttr('disabled');
        },
    });
    $('#input-end_at').datepicker({
        {!! $api->model->start_at ? 'minDate: new Date("' . $carbon->parse($api->model->start_at) . '"),' : '' !!}
    });
@endsection
