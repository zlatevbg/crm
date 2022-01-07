@section('resources')
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
            <label for="input-name">@lang('labels.name')</label>
            <input id="input-name" class="form-control" autofocus required placeholder="@lang('placeholders.name')" name="name" type="text" value="{{ $api->model->name }}">
        </div>

        <div class="form-group">
            <label for="input-slug">@lang('labels.slug')</label>
            <input id="input-slug" class="form-control" placeholder="@lang('placeholders.slug')" name="slug" type="text" value="{{ $api->model->slug }}">
        </div>

        @if (!$api->model->parent)
            <div class="form-group">
                <label for="input-meta_title">@lang('labels.metaTitle')</label>
                <input id="input-meta_title" class="form-control title-characters-counter" maxlength="70" placeholder="@lang('placeholders.metaTitle')" name="meta_title" type="text" value="{{ $api->model->meta_title }}">
                <small class="form-text text-muted"><span class="title-characters-count">0</span> @lang('text.characters')</small>
            </div>

            <div class="form-group">
                <label for="input-meta_description">@lang('labels.metaDescription')</label>
                <textarea id="input-meta_description" class="form-control description-characters-counter" maxlength="160" rows="3" placeholder="@lang('placeholders.metaDescription')" name="meta_description">{{ $api->model->meta_description }}</textarea>
                <small class="form-text text-muted"><span class="description-characters-count">0</span> @lang('text.characters')</small>
            </div>
        @endif

        <div class="form-group">
            <label for="input-content">@lang('labels.content')</label>
            <textarea id="input-content" class="form-control ckeditor" {{ $api->model->parent ? 'required' : '' }} placeholder="@lang('placeholders.content')" name="content">{{ $api->model->content }}</textarea>
        </div>

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
    </form>
@endsection

@section('callback')
    @include('partials/ckeditor')

    @if (!$api->model->parent)
        const titleCharactersCounter = document.querySelector('.title-characters-counter');
        const titleCharactersCount = document.querySelector('.title-characters-count');

        @if ($api->model->meta_title)
            titleCharactersCount.textContent = titleCharactersCounter.value.length;
        @endif

        titleCharactersCounter.addEventListener('keydown', (e) => {
            titleCharactersCount.textContent = titleCharactersCounter.value.length;
        });

        titleCharactersCounter.addEventListener('keyup', (e) => {
            titleCharactersCount.textContent = titleCharactersCounter.value.length;
        });

        const descriptionCharactersCounter = document.querySelector('.description-characters-counter');
        const descriptionCharactersCount = document.querySelector('.description-characters-count');

        @if ($api->model->meta_description)
            descriptionCharactersCount.textContent = descriptionCharactersCounter.value.length;
        @endif

        descriptionCharactersCounter.addEventListener('keydown', (e) => {
            descriptionCharactersCount.textContent = descriptionCharactersCounter.value.length;
        });

        descriptionCharactersCounter.addEventListener('keyup', (e) => {
            descriptionCharactersCount.textContent = descriptionCharactersCounter.value.length;
        });
    @endif
@endsection
