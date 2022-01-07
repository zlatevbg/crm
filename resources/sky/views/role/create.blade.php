@section('title')
    {{ $api->meta->title }} / @lang('buttons.create')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.store', $api->meta->slug) }}" accept-charset="UTF-8" id="create-form" data-ajax>
        @csrf

        <div class="form-group">
            <label for="input-name">@lang('labels.name')</label>
            <input id="input-name" class="form-control" autofocus required placeholder="@lang('placeholders.name')" name="name" type="text">
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
    $('#input-permissions').multiselect();
@endsection
