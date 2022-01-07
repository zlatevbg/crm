@section('resources')
{{-- {{ Helper::autover('/js/' . Domain::current() . '/vendor/ckfinder/ckfinder.js') }}, --}}
{{ Helper::autover('/js/' . Domain::current() . '/vendor/ckeditor/ckeditor.js') }},
@endsection

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
                <select id="input-project_id" class="form-control" name="project_id">
                    <option value="" selected="selected">@lang('placeholders.project')</option>
                    @foreach ($api->model->selectProjects() as $key => $value)
                        <option value="{{ $key }}" {!! $api->model->project_id == $key ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="row">
            <div class="form-group col-2">
                <label for="input-priority_id">@lang('labels.priority')</label>
                <select required id="input-priority_id" class="form-control" name="priority_id">
                    @foreach ($api->model->selectPriorities() as $priority)
                        <option value="{{ $priority->id }}" {!! $api->model->priority_id == $priority->id ? 'selected="selected"' : '' !!}>{{ $priority->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-2">
                <label for="input-department_id">@lang('labels.department')</label>
                <select required id="input-department_id" class="form-control" name="department_id">
                    @foreach ($api->model->selectDepartments() as $department)
                        <option value="{{ $department->id }}" {!! $api->model->department_id == $department->id ? 'selected="selected"' : '' !!}>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="input-users">@lang('labels.assignedTo')</label>
            <select multiple required id="input-users" class="form-control" name="users[]">
                @php $selectedUsers = $api->model->users->pluck('id')->all(); @endphp
                @foreach ($api->model->selectUsers() as $key => $value)
                    <option value="{{ $key }}" {!! in_array($key, $selectedUsers) ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="input-name">@lang('labels.name')</label>
            <input id="input-name" class="form-control" autofocus required placeholder="@lang('placeholders.name')" name="name" type="text" value="{{ $api->model->name }}">
        </div>

        <div class="form-group">
            <label for="input-end_at">@lang('labels.deadline')</label>
            <input id="input-end_at" class="form-control" placeholder="@lang('placeholders.deadline')" name="end_at" type="text" value="{{ $api->model->end_at }}">
        </div>

        <div class="form-group">
            <label for="input-description">@lang('labels.description')</label>
            <textarea id="input-description" class="form-control ckeditor" placeholder="@lang('placeholders.description')" name="description">{{ $api->model->description }}</textarea>
        </div>

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
    </form>
@endsection

@section('callback')
    @include('partials/ckeditor')

    $.datepicker.regional.{{ App::getLocale() }} = unikatSettings.datepicker.{{ App::getLocale() }};
    $.datepicker.setDefaults($.datepicker.regional.{{ App::getLocale() }});
    $('#input-end_at').datepicker({
        minDate: 0,
    });

    var users = document.querySelector('#input-users');
    $(users).multiselect();
@endsection
