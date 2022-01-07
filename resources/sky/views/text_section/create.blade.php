@section('resources')
{{-- {{ Helper::autover('/js/' . Domain::current() . '/vendor/ckfinder/ckfinder.js') }}, --}}
{{ Helper::autover('/js/' . Domain::current() . '/vendor/ckeditor/ckeditor.js') }},
@endsection

@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->path) }}" accept-charset="UTF-8" id="create-form" data-ajax>
        @csrf

        <div class="form-group">
            <label for="input-title">@lang('labels.title')</label>
            <input id="input-title" class="form-control" autofocus placeholder="@lang('placeholders.title')" name="title" type="text">
        </div>

        <div class="form-group">
            <label for="input-content">@lang('labels.content')</label>
            <textarea id="input-content" class="form-control ckeditor" placeholder="@lang('placeholders.content')" name="content"></textarea>
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

@section('callback')
    @include('partials/ckeditor')
@endsection
