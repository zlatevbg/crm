@section('title')
    {{ $api->meta->title }}
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->path) }}" accept-charset="UTF-8" id="create-form" data-ajax>
        @csrf

        <div class="form-group">
            <label for="input-status_id">@lang('labels.status')</label>
            <select id="input-status_id" class="form-control" name="status_id">
                <option value="" selected="selected">@lang('placeholders.status')</option>
                @foreach ($api->model->selectStatus() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection
