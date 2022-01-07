@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->path) }}" accept-charset="UTF-8" id="create-form" data-ajax>
        @csrf

        <div class="form-group">
            <label for="input-project_id">@lang('labels.project')</label>
            <select autofocus id="input-project_id" class="form-control" name="project_id">
                <option selected="selected">@lang('placeholders.project')</option>
                @foreach ($api->model->selectProject() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-signed_at">@lang('labels.signedAt')</label>
            <input id="input-signed_at" class="form-control" placeholder="@lang('placeholders.signedAt')" name="signed_at" type="text">
        </div>

        <div class="form-group">
            <label for="input-commission">@lang('labels.commission')</label>
            <input id="input-commission" class="form-control" placeholder="@lang('placeholders.commission')" name="commission" type="text">
        </div>

        <div class="form-group">
            <label for="input-sub_commission">@lang('labels.subCommission')</label>
            <input id="input-sub_commission" class="form-control" placeholder="@lang('placeholders.subCommission')" name="sub_commission" type="text">
        </div>

        <div class="form-group">
            <label for="input-territory">@lang('labels.territory')</label>
            <input id="input-territory" class="form-control" placeholder="@lang('placeholders.territory')" name="territory" type="text">
        </div>

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection

@section('callback')
    $.datepicker.regional.{{ App::getLocale() }} = unikatSettings.datepicker.{{ App::getLocale() }};
    $.datepicker.setDefaults($.datepicker.regional.{{ App::getLocale() }});
    $('#input-signed_at').datepicker();
@endsection
