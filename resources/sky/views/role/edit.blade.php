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
            <label for="input-permissions">@lang('labels.permissions')</label>
            <select multiple id="input-permissions" class="form-control" name="permissions[]">
                @php $selectedPermissions = $api->model->permissions->pluck('id')->all(); @endphp
                @foreach ($api->model->selectPermissions() as $key => $value)
                    <option value="{{ $key }}" {!! in_array($key, $selectedPermissions) ? 'selected="selected"' : '' !!}>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-warning fa-left"><i class="fas fa-save"></i>@lang('buttons.update')</button>
    </form>
@endsection

@section('callback')
    $('#input-permissions').multiselect();
@endsection
