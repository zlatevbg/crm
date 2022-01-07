@section('title')
    {{ $api->meta->title }} / @lang('buttons.edit')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.update', $api->path) }}" accept-charset="UTF-8" id="edit-form" data-ajax>
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="input-project_id">@lang('labels.project')</label>
            <select autofocus id="input-project_id" class="form-control" name="project_id">
                @foreach ($api->model->selectProject($api) as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->project_id == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-signed_at">@lang('labels.signedAt')</label>
            <input id="input-signed_at" class="form-control" placeholder="@lang('placeholders.signedAt')" name="signed_at" type="text" value="{{ $api->model->signed_at }}">
        </div>

        <div class="form-group">
            <label for="input-commission">@lang('labels.commission')</label>
            <input id="input-commission" class="form-control" placeholder="@lang('placeholders.commission')" name="commission" type="text" value="{{ $api->model->commission }}">
        </div>

        <div class="form-group">
            <label for="input-sub_commission">@lang('labels.subCommission')</label>
            <input id="input-sub_commission" class="form-control" placeholder="@lang('placeholders.subCommission')" name="sub_commission" type="text" value="{{ $api->model->sub_commission }}">
        </div>

        <div class="form-group">
            <label for="input-territory">@lang('labels.territory')</label>
            <input id="input-territory" class="form-control" placeholder="@lang('placeholders.territory')" name="territory" type="text" value="{{ $api->model->territory }}">
        </div>

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
    </form>
@endsection

@section('callback')
    $.datepicker.regional.{{ App::getLocale() }} = unikatSettings.datepicker.{{ App::getLocale() }};
    $.datepicker.setDefaults($.datepicker.regional.{{ App::getLocale() }});
    $('#input-signed_at').datepicker();
@endsection
