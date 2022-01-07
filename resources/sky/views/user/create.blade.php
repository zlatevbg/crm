@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->meta->slug) }}" accept-charset="UTF-8" id="create-form" data-ajax>
        @csrf
        <input id="input-domain_id" name="domain_id" value="{{ Domain::id() }}" type="hidden">

        <div class="form-group">
            <label for="input-projects">@lang('labels.projects')</label>
            <select multiple id="input-projects" class="form-control" name="projects[]">
                @foreach ($api->model->selectProjects() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-websites">@lang('labels.websites')</label>
            <select multiple id="input-websites" class="form-control" name="websites[]">
                @foreach ($api->model->selectWebsites() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-first_name">@lang('labels.firstName')</label>
            <input id="input-first_name" class="form-control" autofocus required placeholder="@lang('placeholders.firstName')" name="first_name" type="text">
        </div>

        <div class="form-group">
            <label for="input-last_name">@lang('labels.lastName')</label>
            <input id="input-last_name" class="form-control" required placeholder="@lang('placeholders.lastName')" name="last_name" type="text">
        </div>

        <div class="form-group">
            <label for="input-gender">@lang('labels.gender')</label>
            <select id="input-gender" class="form-control" name="gender">
                <option value="" selected="selected">@lang('placeholders.gender')</option>
                @foreach ($api->model->selectGender() as $key => $value)
                    <option value="{{ $key }}" {!! $key == 'not-known' ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-email">@lang('labels.email')</label>
            <input id="input-email" class="form-control" required placeholder="@lang('placeholders.email')" name="email" type="email">
        </div>

        <div class="form-group">
            <label for="input-phone">@lang('labels.phone')</label>
            <input id="input-phone" class="form-control" placeholder="@lang('placeholders.phone')" name="phone" type="tel">
        </div>

        <div class="form-group">
            <label for="input-password">@lang('labels.password')</label>
            <input id="input-password" class="form-control" required placeholder="@lang('placeholders.password')" name="password" type="password">
        </div>

        <div class="form-group">
            <label for="input-password_confirmation">@lang('labels.passwordConfirmation')</label>
            <input id="input-password_confirmation" class="form-control" required placeholder="@lang('placeholders.passwordConfirmation')" name="password_confirmation" type="password">
        </div>

        <div class="form-group">
            <label for="input-roles">@lang('labels.roles')</label>
            <select multiple id="input-roles" class="form-control" name="roles[]">
                @foreach ($api->model->selectRoles() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-permissions">@lang('labels.permissions')</label>
            <select multiple id="input-permissions" class="form-control" name="permissions[]">
                @foreach ($api->model->selectPermissions() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-success fa-left"><i class="fas fa-save"></i>@lang('buttons.save')</button>
    </form>
@endsection

@section('callback')
    $('#input-projects').multiselect();
    $('#input-websites').multiselect();
    $('#input-roles').multiselect();
    $('#input-permissions').multiselect();
@endsection
