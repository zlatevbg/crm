@section('title')
    {{ $api->meta->title }} / @lang('buttons.move')
@endsection

@section('content')
    <form method="POST" action="{{ Helper::route('api.move-confirm', $api->path) }}" accept-charset="UTF-8" id="move-form" data-ajax data-table="datatable-{{ $api->meta->model }}">
        @csrf

        <input id="input-ids" name="ids" type="hidden">

        <div class="form-group">
            <label for="input-category">@lang('labels.category')</label>
            <select required id="input-category" class="form-control" name="category">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {!! $api->model->id == $category->id ? 'selected="selected"' : '' !!}>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-info fa-left"><i class="fas fa-exchange-alt"></i>@lang('buttons.move')</button>
    </form>
@endsection
