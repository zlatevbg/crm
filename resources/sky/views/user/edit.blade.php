@section('title')
    {{ $api->meta->title }} / @lang('buttons.edit')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.update', $api->path) }}" accept-charset="UTF-8" id="edit-form" data-ajax>
        @csrf
        @method('PATCH')
        <input id="input-domain_id" name="domain_id" value="{{ Domain::id() }}" type="hidden">

        @can('Edit: Admins')
            <div class="form-group">
                <label for="input-projects">@lang('labels.projects')</label>
                <select multiple id="input-projects" class="form-control" name="projects[]">
                    @php $selectedProjects = $api->model->projects->pluck('id')->all(); @endphp
                    @foreach ($api->model->selectProjects() as $key => $value)
                        <option value="{{ $key }}" {!! in_array($key, $selectedProjects) ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="input-websites">@lang('labels.websites')</label>
                <select multiple id="input-websites" class="form-control" name="websites[]">
                    @php $selectedWebsites = $api->model->websites->pluck('id')->all(); @endphp
                    @foreach ($api->model->selectWebsites() as $key => $value)
                        <option value="{{ $key }}" {!! in_array($key, $selectedWebsites) ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @endcan

        <div class="form-group">
            <label for="input-first_name">@lang('labels.firstName')</label>
            <input id="input-first_name" class="form-control" autofocus required placeholder="@lang('placeholders.firstName')" name="first_name" type="text" value="{{ $api->model->first_name }}">
        </div>

        <div class="form-group">
            <label for="input-last_name">@lang('labels.lastName')</label>
            <input id="input-last_name" class="form-control" required placeholder="@lang('placeholders.lastName')" name="last_name" type="text" value="{{ $api->model->last_name }}">
        </div>

        <div class="form-group">
            <label for="input-gender">@lang('labels.gender')</label>
            <select id="input-gender" class="form-control" name="gender">
                @foreach ($api->model->selectGender() as $key => $value)
                    <option value="{{ $key }}" {!! $api->model->gender == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-email">@lang('labels.email')</label>
            <input id="input-email" class="form-control" required placeholder="@lang('placeholders.email')" name="email" type="email" value="{{ $api->model->email }}">
        </div>

        <div class="form-group">
            <label for="input-phone">@lang('labels.phone')</label>
            <input id="input-phone" class="form-control" placeholder="@lang('placeholders.phone')" name="phone" type="tel" value="{{ $api->model->phone }}">
        </div>

        <div class="form-group">
            <label for="input-password">@lang('labels.password')</label>
            <input id="input-password" class="form-control" placeholder="@lang('placeholders.password')" name="password" type="password">
        </div>

        <div class="form-group">
            <label for="input-password_confirmation">@lang('labels.passwordConfirmation')</label>
            <input id="input-password_confirmation" class="form-control" placeholder="@lang('placeholders.passwordConfirmation')" name="password_confirmation" type="password">
        </div>

        @can('Edit: Admins')
            <div class="form-group">
                <label for="input-roles">@lang('labels.roles')</label>
                <select multiple id="input-roles" class="form-control" name="roles[]">
                    @php $selectedRoles = $api->model->roles->pluck('id')->all(); @endphp
                    @foreach ($api->model->selectRoles() as $key => $value)
                        <option value="{{ $key }}" {!! in_array($key, $selectedRoles) ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="input-permissions">@lang('labels.permissions')</label>
                <select multiple id="input-permissions" class="form-control" name="permissions[]">
                    @php $selectedPermissions = $api->model->permissions->pluck('id')->all(); @endphp
                    @foreach ($api->model->selectPermissions() as $key => $value)
                        <option value="{{ $key }}" {!! in_array($key, $selectedPermissions) ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @endcan

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
    </form>
@endsection

@section('callback')
    @can('Edit: Admins')
        $('#input-projects').multiselect();
        $('#input-websites').multiselect();
        $('#input-roles').multiselect();
        $('#input-permissions').multiselect();
    @endcan
@endsection
