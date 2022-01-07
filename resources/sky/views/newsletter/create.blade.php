@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->meta->slug) }}" accept-charset="UTF-8" id="create-form" data-ajax novalidate>
        @csrf

        <div class="form-group">
            <label for="input-template">@lang('labels.template')</label>
            <select required id="input-template" class="form-control" name="template">
                @foreach ($api->model->selectTemplate() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

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

        <div id="client-status" class="form-group table-hidden">
            <label for="input-status">@lang('labels.status')</label>
            <select multiple disabled id="input-status" class="form-control" name="status[]"></select>
        </div>

        <div id="source" class="form-group table-hidden">
            <label for="input-source">@lang('labels.source')</label>
            <select multiple disabled id="input-source" class="form-control" name="source[]"></select>
        </div>

        <div id="goldenvisa" class="form-group table-hidden">
            <label for="input-goldenvisa">@lang('labels.goldenVisaAgent')</label>
            <select id="input-goldenvisa" class="form-control" name="goldenvisa">
                @foreach ($api->model->selectGoldenVisa() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-recipients">@lang('labels.recipients')</label>
            <select multiple required disabled id="input-recipients" class="form-control" name="recipients[]"></select>
        </div>

        <div class="form-group">
            <label for="input-include_team">@lang('labels.includeTeam')</label>
            <select id="input-include_team" class="form-control" name="include_team">
                @foreach ($api->model->selectIncludeTeam() as $key => $value)
                    <option value="{{ $key }}" {!! $key == 1 ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-subject">@lang('labels.subject')</label>
            <input id="input-subject" class="form-control" autofocus required placeholder="@lang('placeholders.subject')" name="subject" type="text">
        </div>

        <div class="form-group">
            <label for="input-teaser">@lang('labels.teaser')</label>
            <input id="input-teaser" class="form-control" placeholder="@lang('placeholders.teaser')" name="teaser" type="text">
        </div>

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection

@section('callback')
    @include('partials/js-newsletter')
@endsection
