@foreach ($datatables as $key => $datatable)
    <div class="datatable-sticky-header">
        @include('partials/breadcrumbs', ['breadcrumbs' => $datatable['title']])
        @if ($datatable['buttons'])
            @include('partials/buttons', ['buttons' => $datatable['buttons']])
        @endif
    </div>

    <table id="{{ $key }}" {!! $datatable['options']['parameters'] ?? null !!} data-table></table>
@endforeach
