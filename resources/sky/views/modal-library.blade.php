@section('title')
    @lang('buttons.library')
@endsection

@section('content')
    @isset($datatables)
        @include('partials/modal-datatables')
    @endisset

    <button type="submit" class="btn btn-success fa-left"><i class="fas fa-check-square"></i>@lang('buttons.select')</button>
@endsection

@section('callback')
    datatable.setup(@json($datatables));
@endsection
