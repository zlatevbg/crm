@section('resources')
    {{ Helper::autover('/js/' . Domain::current() . '/vendor/ckeditor/ckeditor.js') }},
@endsection

@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->path) }}" accept-charset="UTF-8" id="create-form" data-ajax>
        @csrf

        <input id="input-type" name="type" value="{{ Request::has('reply') ? 'reply' : 'new' }}" type="hidden">

        <div class="form-group">
            <label>@lang('labels.type'):</label>
            <p class="d-inline font-weight-bold">{{ $api->model->selectMessageType()[Request::has('reply') ? 'reply' : 'new'] }}</p>
        </div>

        <div class="form-group">
            <label for="input-from">@lang('labels.from')</label>
            @if (Request::has('reply'))
                <select required id="input-from" class="form-control" name="from">
                    @foreach ($api->model->selectUsers() as $key => $value)
                        <option value="{{ $key }}" {!! $key == Auth::user()->id ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
            @else
                <p class="d-inline font-weight-bold">@lang('labels.lead')</p>
            @endif
        </div>

        <div class="form-group">
            <label for="input-created_at">@lang('labels.date')</label>
            <input id="input-created_at" class="form-control" name="created_at" type="text">
        </div>

        <div class="form-group">
            <label for="input-message">@lang('labels.message')</label>
            <textarea id="input-message" class="form-control ckeditor" autofocus required placeholder="@lang('placeholders.message')" name="message"></textarea>
        </div>

        @can('Reply')
            @if (Request::has('reply'))
                <div class="form-group">
                    <label for="input-template">@lang('labels.template')</label>
                    <select id="input-template" class="form-control" name="template">
                        @foreach ($api->model->selectTemplate() as $key => $value)
                            <option value="{{ $key }}" {!! in_array($key, $api->model->_parent->sources->pluck('name')->toArray()) ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="input-history">@lang('labels.history')</label>
                    <select id="input-history" class="form-control" name="history">
                        @foreach ($api->model->selectHistory() as $key => $value)
                            <option value="{{ $key }}" {!! !$key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        @endcan

        <div class="modal-buttons form-inline">
            @can('Reply')
                @if (Request::has('reply'))
                    <button type="button" class="btn btn-info fa-left" data-ajax data-form="create-form" data-ajax-alert="#create-form" @if (Request::has('overview')) data-overview @endif data-action="{{ Helper::route('api.reply', $api->path, false) }}"><i class="fas fa-reply"></i>@lang('buttons.replyAndSave')</button>
                    <button type="button" class="btn btn-success fa-left ml-2" data-ajax data-form="create-form" data-ajax-alert="#create-form" @if (Request::has('overview')) data-overview @endif data-query="complete" data-action="{{ Helper::route('api.store', $api->path, false) }}"><i class="fas fa-check"></i>@lang('buttons.replyAndComplete')</button>
                    <button type="button" class="btn btn-warning fa-left ml-2" data-ajax data-form="create-form" data-ajax-alert="#create-form" @if (Request::has('overview')) data-overview @endif data-query="test" data-action="{{ Helper::route('api.reply', $api->path, false) }}"><i class="fas fa-envelope"></i>@lang('buttons.testSend')</button>
                @endif

                <button type="button" class="btn btn-success fa-left ml-auto" data-ajax data-form="create-form" data-ajax-alert="#create-form" @if (Request::has('overview')) data-overview @endif data-query="complete" data-action="{{ Helper::route('api.store', $api->path, false) }}"><i class="fas fa-check"></i>@lang('buttons.markAsCompleted')</button>
            @endcan

            <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
        </div>
    </form>
@endsection

@section('callback')
    $.datepicker.regional.{{ App::getLocale() }} = unikatSettings.datepicker.{{ App::getLocale() }};
    $.datepicker.setDefaults($.datepicker.regional.{{ App::getLocale() }});
    $('#input-created_at').datepicker();

    var globalCKEditorConfig = {
        extraPlugins: 'templates,placeholder_elements',
        templates: 'replyToLeads',
        templates_files: ['{{ Helper::autover('/js/' . Domain::current() . '/components/templates.js') }}'],
        removeButtons: 'Cut,Copy,Anchor,Styles,Font',
        startupFocus: true,
        placeholder_elements: {
            css: '.cke_placeholder_element { background: #ffff00; } a .cke_placeholder_element { text-decoration: underline }',
            draggable: true,
            placeholders: [
                { label: 'Name', value: 'NAME' },
            ],
            startDelimiter: '[[',
            endDelimiter: ']]',
            uiType: 'combo',
        },
    };

    @include('partials/ckeditor')

    @if (Request::has('reply'))
        var users = document.querySelector('#input-from');
        $(users).multiselect({
            multiple: false,
        });
    @endif
@endsection
