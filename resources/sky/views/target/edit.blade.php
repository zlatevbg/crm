@inject('carbon', '\Carbon\Carbon')

@section('title')
    {{ $api->meta->title }} / @lang('buttons.edit')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.update', $api->path) }}" accept-charset="UTF-8" id="edit-form" data-ajax>
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="input-name">@lang('labels.' . ($api->model->parent ? 'name' : 'year'))</label>
            <input id="input-name" class="form-control" autofocus required placeholder="@lang('placeholders.' . ($api->model->parent ? 'name' : 'year'))" name="name" type="text" value="{{ $api->model->name }}">
        </div>

        @if ($api->model->parent)
            <div class="form-group">
                <label for="input-start_at">@lang('labels.startAt')</label>
                <input id="input-start_at" class="form-control" required placeholder="@lang('placeholders.startAt')" name="start_at" type="text" value="{{ $api->model->start_at }}">
            </div>

            <div class="form-group">
                <label for="input-end_at">@lang('labels.endAt')</label>
                <input id="input-end_at" class="form-control" required placeholder="@lang('placeholders.endAt')" name="end_at" type="text" value="{{ $api->model->end_at }}" {{ $api->model->start_at ? '' : 'disabled' }}>
            </div>

            <div class="form-group">
                <label for="input-sales">@lang('labels.sales')</label>
                <input id="input-sales" class="form-control" required placeholder="@lang('placeholders.sales')" name="sales" type="text" value="{{ $api->model->sales }}">
            </div>

            <div class="form-group">
                <label for="input-revenue">@lang('labels.revenue')</label>
                <input id="input-revenue" class="form-control" required placeholder="@lang('placeholders.revenue')" name="revenue" type="text" value="{{ $api->model->revenue }}">
            </div>
        @endif

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
    </form>
@endsection

@if ($api->model->parent)
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
@endif
