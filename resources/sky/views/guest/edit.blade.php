@section('title')
    {{ $api->meta->title }} / @lang('buttons.edit')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.update', $api->path) }}" accept-charset="UTF-8" id="edit-form" data-ajax novalidate>
        @csrf
        @method('PATCH')

        @if (session('project'))
            <input id="input-project_id" name="project_id" value="{{ session('project') }}" type="hidden">
        @else
            <div class="form-group">
                <label for="input-project_id">@lang('labels.project')</label>
                <select required id="input-project_id" class="form-control" name="project_id">
                    @foreach ($api->model->selectProjects() as $key => $value)
                        <option value="{{ $key }}" {!! $api->model->project_id == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="form-group">
            <label for="input-source_id">@lang('labels.source')</label>
            <select id="input-source_id" class="form-control" name="source_id">
                <option value="">@lang('placeholders.source')</option>
                @foreach ($api->model->selectSources() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->source_id == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-first_name">@lang('labels.firstName')</label>
            <input id="input-first_name" class="form-control" autofocus required placeholder="@lang('placeholders.firstName')" name="first_name" type="text" value="{{ $api->model->first_name }}">
        </div>

        <div class="form-group">
            <label for="input-last_name">@lang('labels.lastName')</label>
            <input id="input-last_name" class="form-control" placeholder="@lang('placeholders.lastName')" name="last_name" type="text" value="{{ $api->model->last_name }}">
        </div>

        <div class="form-group">
            <label for="input-gender">@lang('labels.gender')</label>
            <select required id="input-gender" class="form-control" name="gender">
                @foreach ($api->model->selectGender() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->gender == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-email">@lang('labels.email')</label>
            <input id="input-email" class="form-control" placeholder="@lang('placeholders.email')" name="email" type="email" value="{{ $api->model->email }}">
        </div>

        <div class="form-group">
            <label for="input-phone_number">@lang('labels.phone')</label>
            <div class="input-group">
                <select id="input-phone_code" class="form-control" name="phone_code">
                    <option value="">@lang('placeholders.phoneCode')</option>
                    @foreach ($api->model->selectPhoneCodes() as $key => $value)
                        <option value="{{ $key }}" {!! $api->model->phone_code == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
                <input id="input-phone_number" class="form-control" {{ $api->model->phone_code ? '' : 'disabled' }} placeholder="@lang('placeholders.phoneNumber')" name="phone_number" type="tel" value="{{ $api->model->phone_number }}">
            </div>
        </div>

        <div class="form-group">
            <label for="input-country_id">@lang('labels.country')</label>
            <select id="input-country_id" class="form-control" name="country_id">
                <option value="">@lang('placeholders.country')</option>
                @foreach ($api->model->selectCountries() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->country_id == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-newsletters">@lang('labels.newslettersSubscription')</label>
            <select id="input-newsletters" class="form-control" name="newsletters">
                @foreach ($api->model->selectNewslettersSubscription() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->newsletters == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
    </form>
@endsection

@section('callback')
    @include('partials/js-phone')
    var country_id = document.querySelector('#input-country_id');
    var source_id = document.querySelector('#input-source_id');

    $(country_id).multiselect({
        multiple: false,
    });

    $(source_id).multiselect({
        multiple: false,
        close: function() {
            if (parseInt(this.value) == 1) {
                agent_id.closest('.form-group').classList.remove('table-hidden');
                agent_id.disabled = false;
                $(agent_id).multiselect('enable');
            } else {
                agent_id.closest('.form-group').classList.add('table-hidden');
                agent_id.value = '';
                agent_id.disabled = true;
                $(agent_id).multiselect('disable');
                $(agent_id).multiselect('refresh');
            }
        },
    });
@endsection
