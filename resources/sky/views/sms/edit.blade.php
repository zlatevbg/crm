@section('title')
    {{ $api->meta->title }} / @lang('buttons.edit')
@endsection

@section('resources')
{{ Helper::autover('/js/' . Domain::current() . '/vendor/split-sms.js') }},
{{ Helper::autover('/js/' . Domain::current() . '/components/split-sms.js') }},
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.update', $api->path) }}" accept-charset="UTF-8" id="edit-form" data-ajax novalidate>
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="input-group">@lang('labels.group')</label>
            <select required id="input-group" class="form-control" name="group">
                @foreach ($api->model->selectGroup() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->group == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        @if ($api->model->group == 'clients')
            <div class="form-group">
                <label for="input-projects">@lang('labels.projects')</label>
                <select multiple id="input-projects" class="form-control" name="projects[]">
                    @php $selectedProjects = $api->model->projects; @endphp
                    @foreach ($api->model->selectProjects() as $key => $value)
                        <option value="{{ $key }}" {!! in_array($key, $selectedProjects) ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div class="form-group table-hidden">
                <label for="input-projects">@lang('labels.projects')</label>
                <select multiple disabled id="input-projects" class="form-control" name="projects[]"></select>
            </div>
        @endif

        @if ($api->model->group == 'clients')
            <div class="form-group">
                <label for="input-status">@lang('labels.status')</label>
                <select multiple id="input-status" class="form-control" name="status[]">
                    @php $selectedStatus = $api->model->status; @endphp
                    @foreach ($api->model->selectStatus() as $key => $value)
                        <option value="{{ $key }}" {!! in_array($key, $selectedStatus) ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div class="form-group table-hidden">
                <label for="input-status">@lang('labels.status')</label>
                <select multiple disabled id="input-status" class="form-control" name="status[]"></select>
            </div>
        @endif

        <div class="form-group {{ $api->model->group ? ($api->model->group == 'custom' ? 'table-hidden' : '') : 'table-hidden' }}">
            <label for="input-recipients">@lang('labels.recipients')</label>
            <select multiple required id="input-recipients" class="form-control" name="recipients[]" {{ $api->model->group ? '' : 'disabled' }}>
                @php $selectedRecipients = $api->model->recipients; @endphp
                @foreach ($api->model->selectRecipients($api->model->status, $api->model->projects) as $key => $value)
                    <option value="{{ $key }}" {!! in_array($key, $selectedRecipients) ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group {{ $api->model->group ? ($api->model->group == 'custom' ? '' : 'table-hidden') : '' }}">
            <label for="input-numbers">@lang('labels.recipients')</label>
            <textarea id="input-numbers" class="form-control" placeholder="@lang('placeholders.customRecipients')" name="numbers">{{ $api->model->numbers }}</textarea>
        </div>

        <div class="form-group split-sms">
            <label for="input-message">@lang('labels.message')</label>
            <textarea id="input-message" class="form-control split-sms-message" autofocus required placeholder="@lang('placeholders.message')" name="message">{{ $api->model->message }}</textarea>
            <small class="form-text text-muted"><span class="split-sms-count"></span> @lang('text.sms') (<span class="split-sms-length"></span> @lang('text.charactersLeft'))</small>
        </div>

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
    </form>
@endsection

@section('callback')
    @include('partials/js-sms')
@endsection
