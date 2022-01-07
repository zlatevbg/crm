@section('resources')
{{-- {{ Helper::autover('/js/' . Domain::current() . '/vendor/ckfinder/ckfinder.js') }}, --}}
{{ Helper::autover('/js/' . Domain::current() . '/vendor/ckeditor/ckeditor.js') }},
@endsection

@section('title')
    {{ $api->meta->title }} / @lang('buttons.edit')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.update', $api->path) }}" accept-charset="UTF-8" id="edit-form" data-ajax>
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="input-title">@lang('labels.title')</label>
            <input id="input-title" class="form-control" autofocus placeholder="@lang('placeholders.title')" name="title" type="text" value="{{ $api->model->title }}">
        </div>

        <div class="form-group">
            <label for="input-content">@lang('labels.content')</label>
            <textarea id="input-content" class="form-control ckeditor" placeholder="@lang('placeholders.content')" name="content">{{ $api->model->content }}</textarea>
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

@section('callback')
    @include('partials/ckeditor')
@endsection
