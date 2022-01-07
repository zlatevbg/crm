@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('resources')
{{ Helper::autover('/js/' . Domain::current() . '/vendor/split-sms.js') }},
{{ Helper::autover('/js/' . Domain::current() . '/components/split-sms.js') }},
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->meta->slug) }}" accept-charset="UTF-8" id="create-form" data-ajax novalidate>
        @csrf

        <div class="form-group">
            <label for="input-group">@lang('labels.group')</label>
            <select required id="input-group" class="form-control" name="group">
                @foreach ($api->model->selectGroup() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div id="client-projects" class="form-group table-hidden">
            <label for="input-projects">@lang('labels.projects')</label>
            <select multiple disabled id="input-projects" class="form-control" name="projects[]"></select>
        </div>

        <div class="form-group table-hidden">
            <label for="input-status">@lang('labels.status')</label>
            <select multiple disabled id="input-status" class="form-control" name="status[]"></select>
        </div>

        <div class="form-group table-hidden">
            <label for="input-recipients">@lang('labels.recipients')</label>
            <select multiple disabled id="input-recipients" class="form-control" name="recipients[]"></select>
        </div>

        <div class="form-group table-hidden">
            <label for="input-numbers">@lang('labels.recipients')</label>
            <textarea disabled id="input-numbers" class="form-control" placeholder="@lang('placeholders.customRecipients')" name="numbers"></textarea>
        </div>

        <div class="form-group split-sms">
            <label for="input-message">@lang('labels.message')</label>
            <textarea id="input-message" class="form-control split-sms-message" autofocus required placeholder="@lang('placeholders.message')" name="message"></textarea>
            <small class="form-text text-muted"><span class="split-sms-count"></span> @lang('text.sms') (<span class="split-sms-length"></span> @lang('text.charactersLeft'))</small>
        </div>

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection

@section('callback')
    @include('partials/js-sms')
@endsection
