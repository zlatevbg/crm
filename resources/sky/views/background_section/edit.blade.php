@section('title')
    {{ $api->meta->title }} / @lang('buttons.edit')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.update', $api->path) }}" accept-charset="UTF-8" id="edit-form" data-ajax>
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="input-position">@lang('labels.position')</label>
            <select required id="input-position" class="form-control" name="position">
                @foreach ($api->model->selectPosition() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->position == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-title">@lang('labels.title')</label>
            <input id="input-title" class="form-control" autofocus placeholder="@lang('placeholders.title')" name="title" type="text" value="{{ $api->model->title }}">
        </div>

        <div class="form-group">
            <label for="input-description">@lang('labels.description')</label>
            <textarea id="input-description" class="form-control" placeholder="@lang('placeholders.description')" name="description">{{ $api->model->description }}</textarea>
        </div>

        <div class="form-group">
            <label for="input-button_text">@lang('labels.buttonText')</label>
            <input id="input-button_text" class="form-control" placeholder="@lang('placeholders.buttonText')" name="button_text" type="text" value="{{ $api->model->button_text }}">
        </div>

        <div class="form-group">
            <label for="input-button_link">@lang('labels.buttonLink')</label>
            <input id="input-button_link" class="form-control" placeholder="@lang('placeholders.buttonLink')" name="button_link" type="url" value="{{ $api->model->button_link }}">
        </div>

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
    </form>
@endsection
