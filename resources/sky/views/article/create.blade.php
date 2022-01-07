@section('resources')
{{ Helper::autover('/js/' . Domain::current() . '/vendor/ckeditor/ckeditor.js') }},
@endsection

@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->path) }}" accept-charset="UTF-8" id="create-form" data-ajax>
        @csrf

        <div class="form-group">
            <label for="input-published_at">@lang('labels.publishedAt')</label>
            <input id="input-published_at" class="form-control" required autofocus placeholder="@lang('placeholders.publishedAt')" name="published_at" type="text">
        </div>

        <div class="form-group">
            <label for="input-link">@if ($api->model->_parent->id) @lang('labels.externalLink') @lang('labels.optional') @else @lang('labels.link') @endif</label>
            <input id="input-link" class="form-control" placeholder="@lang('placeholders.link')" name="link" type="url">
        </div>

        <div class="form-group">
            <label for="input-title">@lang('labels.title')</label>
            <input id="input-title" class="form-control" required placeholder="@lang('placeholders.title')" name="title" type="text">
        </div>

        <div class="form-group">
            <label for="input-slug">@lang('labels.slug')</label>
            <input id="input-slug" class="form-control" placeholder="@lang('placeholders.slug')" name="slug" type="text">
        </div>

        <div class="form-group">
            <label for="input-meta_title">@lang('labels.metaTitle')</label>
            <input id="input-meta_title" class="form-control description-characters-counter" maxlength="70" placeholder="@lang('placeholders.metaTitle')" name="meta_title" type="text">
            <small class="form-text text-muted"><span class="description-characters-count">0</span> @lang('text.characters')</small>
        </div>

        <div class="form-group">
            <label for="input-meta_description">@lang('labels.metaDescription')</label>
            <textarea id="input-meta_description" class="form-control title-characters-counter" maxlength="160" rows="3" placeholder="@lang('placeholders.metaDescription')" name="meta_description"></textarea>
            <small class="form-text text-muted"><span class="title-characters-count">0</span> @lang('text.characters')</small>
        </div>

        <div class="form-group">
            <label for="input-content">@lang('labels.content')</label>
            <textarea id="input-content" class="form-control ckeditor" placeholder="@lang('placeholders.content')" name="content"></textarea>
        </div>

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection

@section('callback')
    @include('partials/ckeditor')

    $.datepicker.regional.{{ App::getLocale() }} = unikatSettings.datepicker.{{ App::getLocale() }};
    $.datepicker.setDefaults($.datepicker.regional.{{ App::getLocale() }});
    $('#input-published_at').datepicker();

    const titleCharactersCounter = document.querySelector('.title-characters-counter');
    const titleCharactersCount = document.querySelector('.title-characters-count');

    titleCharactersCounter.addEventListener('keydown', (e) => {
        titleCharactersCount.textContent = titleCharactersCounter.value.length;
    });

    titleCharactersCounter.addEventListener('keyup', (e) => {
        titleCharactersCount.textContent = titleCharactersCounter.value.length;
    });

    const descriptionCharactersCounter = document.querySelector('.description-characters-counter');
    const descriptionCharactersCount = document.querySelector('.description-characters-count');

    descriptionCharactersCounter.addEventListener('keydown', (e) => {
        descriptionCharactersCount.textContent = descriptionCharactersCounter.value.length;
    });

    descriptionCharactersCounter.addEventListener('keyup', (e) => {
        descriptionCharactersCount.textContent = descriptionCharactersCounter.value.length;
    });
@endsection
