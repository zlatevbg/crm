@section('title')
    {{ $api->meta->title }} / @lang('buttons.delete')
@endsection

@section('content')
    <p class="lead text-center">@lang('text.' . ($reload ? 'confirmDelete' : 'confirmDeleteRows'))</p>

    <form method="POST" action="{{ Helper::route('api.destroy', $api->path) }}" accept-charset="UTF-8" id="delete-form" data-ajax data-table="datatable-{{ $api->meta->model . ($overview ? '-overview' : '') }}">
        @csrf

        <input name="_method" value="DELETE" type="hidden">
        @if (!$reload)
            <input id="input-ids" name="ids" type="hidden">
        @endif
        <button type="submit" autofocus class="btn btn-danger fa-left"><i class="fas fa-trash"></i>@lang('buttons.delete')</button>
    </form>
@endsection
