@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->path) }}" accept-charset="UTF-8" id="create-form" data-ajax>
        @csrf

        <div class="form-group">
            <label for="input-name">@lang('labels.' . ($api->id ? 'name' : 'year'))</label>
            <input id="input-name" class="form-control" autofocus required placeholder="@lang('placeholders.' . ($api->id ? 'name' : 'year'))" name="name" type="text">
        </div>

        @if ($api->id)
            <div class="form-group">
                <label for="input-start_at">@lang('labels.startAt')</label>
                <input id="input-start_at" class="form-control" required placeholder="@lang('placeholders.startAt')" name="start_at" type="text">
            </div>

            <div class="form-group">
                <label for="input-end_at">@lang('labels.endAt')</label>
                <input id="input-end_at" class="form-control" disabled required placeholder="@lang('placeholders.endAt')" name="end_at" type="text">
            </div>

            <div class="form-group">
                <label for="input-sales">@lang('labels.sales')</label>
                <input id="input-sales" class="form-control" required placeholder="@lang('placeholders.sales')" name="sales" type="text">
            </div>

            <div class="form-group">
                <label for="input-revenue">@lang('labels.revenue')</label>
                <input id="input-revenue" class="form-control" required placeholder="@lang('placeholders.revenue')" name="revenue" type="text">
            </div>
        @endif

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection

@if ($api->id)
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
        $('#input-end_at').datepicker();
    @endsection
@endif
