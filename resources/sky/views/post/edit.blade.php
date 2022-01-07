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
            <label for="input-published_at">@lang('labels.publishedAt')</label>
            <input id="input-published_at" class="form-control" required placeholder="@lang('placeholders.publishedAt')" name="published_at" type="text" value="{{ $api->model->published_at }}">
        </div>

        <div class="form-group">
            <label for="input-title">@lang('labels.title')</label>
            <input id="input-title" class="form-control" required placeholder="@lang('placeholders.title')" name="title" type="text" value="{{ $api->model->title }}">
        </div>

        <div class="form-group">
            <label for="input-description">@lang('labels.description')</label>
            <textarea id="input-description" class="form-control" placeholder="@lang('placeholders.description')" name="description">{{ $api->model->description }}</textarea>
        </div>

        <div class="form-group">
            <label for="input-content">@lang('labels.content')</label>
            <textarea id="input-content" class="form-control ckeditor" placeholder="@lang('placeholders.content')" name="content">{{ $api->model->content }}</textarea>
        </div>

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
    </form>
@endsection

@section('callback')
    @include('partials/ckeditor')

    $.datepicker.regional.{{ App::getLocale() }} = unikatSettings.datepicker.{{ App::getLocale() }};
    $.datepicker.setDefaults($.datepicker.regional.{{ App::getLocale() }});
    $('#input-published_at').datepicker({
        maxDate: 0,
    });
@endsection
