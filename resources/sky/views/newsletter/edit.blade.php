@section('title')
    {{ $api->meta->title }} / @lang('buttons.edit')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.update', $api->path) }}" accept-charset="UTF-8" id="edit-form" data-ajax novalidate>
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="input-template">@lang('labels.template')</label>
            <select required id="input-template" class="form-control" name="template">
                @foreach ($api->model->selectTemplate() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->template == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-group">@lang('labels.group')</label>
            <select required id="input-group" class="form-control" name="group">
                @foreach ($api->model->selectGroup() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->group == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        @if (in_array($api->model->group, ['agent-contacts', 'clients', 'investors', 'guests']))
            <div id="client-projects" class="form-group">
                <label for="input-projects">@lang('labels.projects')</label>
                <select multiple id="input-projects" class="form-control" name="projects[]">
                    @php $selectedProjects = $api->model->projects; @endphp
                    @foreach ($api->model->selectProjects() as $key => $value)
                        <option value="{{ $key }}" {!! in_array($key, $selectedProjects) ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div id="client-projects" class="form-group table-hidden">
                <label for="input-projects">@lang('labels.projects')</label>
                <select multiple disabled id="input-projects" class="form-control" name="projects[]"></select>
            </div>
        @endif

        @if ($api->model->group == 'clients')
            <div id="client-status" class="form-group">
                <label for="input-status">@lang('labels.status')</label>
                <select multiple id="input-status" class="form-control" name="status[]">
                    @php $selectedStatus = $api->model->status; @endphp
                    @foreach ($api->model->selectStatus() as $key => $value)
                        <option value="{{ $key }}" {!! in_array($key, $selectedStatus) ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div id="client-status" class="form-group table-hidden">
                <label for="input-status">@lang('labels.status')</label>
                <select multiple disabled id="input-status" class="form-control" name="status[]"></select>
            </div>
        @endif

        @if (in_array($api->model->group, ['gvcontacts', 'mespil', 'ph', 'pgv']))
            <div id="source" class="form-group">
                <label for="input-source">@lang('labels.source')</label>
                <select multiple id="input-source" class="form-control" name="source[]">
                    @php $selectedSource = $api->model->source; @endphp
                    @foreach ($api->model->selectSource() as $key => $value)
                        <option value="{{ $key }}" {!! in_array($key, $selectedSource) ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div id="source" class="form-group table-hidden">
                <label for="input-source">@lang('labels.source')</label>
                <select multiple disabled id="input-source" class="form-control" name="source[]"></select>
            </div>
        @endif

        <div id="goldenvisa" class="form-group {{ $api->model->group != 'agent-contacts' ? 'table-hidden' : '' }}">
            <label for="input-goldenvisa">@lang('labels.goldenVisaAgent')</label>
            <select id="input-goldenvisa" class="form-control" name="goldenvisa">
                @foreach ($api->model->selectGoldenVisa() as $key => $value)
                    <option value="{{ $key }}" {!! $key === $api->model->goldenvisa ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-recipients">@lang('labels.recipients')</label>
            <select multiple required id="input-recipients" class="form-control" name="recipients[]" {{ $api->model->group ? '' : 'disabled' }}>
                @php $selectedRecipients = $api->model->recipients; @endphp
                @foreach ($api->model->selectRecipients($api->model->status, $api->model->projects, $api->model->source, $api->model->goldenvisa) as $key => $value)
                    <option value="{{ $key }}" {!! in_array($key, $selectedRecipients) ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-include_team">@lang('labels.includeTeam')</label>
            <select id="input-include_team" class="form-control" name="include_team">
                @foreach ($api->model->selectIncludeTeam() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->include_team == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-subject">@lang('labels.subject')</label>
            <input id="input-subject" class="form-control" autofocus required placeholder="@lang('placeholders.subject')" name="subject" type="text" value="{{ $api->model->subject }}">
        </div>

        <div class="form-group">
            <label for="input-teaser">@lang('labels.teaser')</label>
            <input id="input-teaser" class="form-control" placeholder="@lang('placeholders.teaser')" name="teaser" type="text" value="{{ $api->model->teaser }}">
        </div>

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
    </form>
@endsection

@section('callback')
    @include('partials/js-newsletter')
@endsection
