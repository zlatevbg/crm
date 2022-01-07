@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->path) }}" accept-charset="UTF-8" id="create-form" data-ajax>
        @csrf

        <div class="form-group">
            <label for="input-position">@lang('labels.position')</label>
            <select required id="input-position" class="form-control" name="position">
                @foreach ($api->model->selectPosition() as $key => $value)
                    <option value="{{ $key }}" {!! $key == 'header' ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-title">@lang('labels.title')</label>
            <input id="input-title" class="form-control" autofocus placeholder="@lang('placeholders.title')" name="title" type="text">
        </div>

        <div class="form-group">
            <label for="input-description">@lang('labels.description')</label>
            <textarea id="input-description" class="form-control" placeholder="@lang('placeholders.description')" name="description"></textarea>
        </div>

        <div class="form-group">
            <label for="input-button_text">@lang('labels.buttonText')</label>
            <input id="input-button_text" class="form-control" placeholder="@lang('placeholders.buttonText')" name="button_text" type="text">
        </div>

        <div class="form-group">
            <label for="input-button_link">@lang('labels.buttonLink')</label>
            <input id="input-button_link" class="form-control" placeholder="@lang('placeholders.buttonLink')" name="button_link" type="url">
        </div>

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection
