@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->path) }}" accept-charset="UTF-8" id="create-form" data-ajax novalidate>
        @csrf

        <input id="input-project_id" name="project_id" value="{{ $api->model->_parent->id }}" type="hidden">

        <div class="form-group">
            <label for="input-offered_at">@lang('labels.offeredAt')</label>
            <input id="input-offered_at" class="form-control" autofocus required placeholder="@lang('placeholders.offeredAt')" name="offered_at" type="text" value="{{ date('d.m.Y') }}">
        </div>

        <div class="form-group">
            <label for="input-price">@lang('labels.price')</label>
            <input id="input-price" class="form-control" required placeholder="0.00" min="0" max="999999999.99" name="price" step=".01" type="number">
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
    $('#input-offered_at').datepicker({
        maxDate: 0,
    });
@endsection
