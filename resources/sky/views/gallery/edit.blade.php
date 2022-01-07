@section('title')
    {{ $api->meta->title }} / @lang('buttons.edit')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.update', $api->path) }}" accept-charset="UTF-8" id="edit-form" data-ajax>
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="input-gallery">@lang('labels.gallery')</label>
            <input id="input-gallery" class="form-control" required placeholder="@lang('placeholders.gallery')" name="gallery" type="text" value="{{ $api->model->gallery }}">
        </div>

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
    </form>
@endsection
