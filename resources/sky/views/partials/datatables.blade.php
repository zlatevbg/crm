@foreach ($datatables as $key => $datatable)
    @if (!ends_with($key, '-overview'))
        <div id="{{ $key }}-sticky-wrapper" class="datatable-sticky-wrapper">
            <div class="datatable-sticky-header">
                <div class="datatable-header">
                    @include('partials/breadcrumbs', ['breadcrumbs' => $api->breadcrumbs()])
                    @isset ($datatable['buttons'])
                        @include('partials/buttons', ['buttons' => $datatable['buttons']])
                    @endisset
                </div>
            </div>
        </div>
    @endif

    @yield('content')

    @if (isset($datatable['columns']) && !empty($datatable['columns']))
        <table id="{{ $key }}" {!! $datatable['options']['parameters'] ?? null !!} data-table>
            @isset ($datatable['options']['footer'])
                <tfoot {!! isset($datatable['options']['footer']) ? 'class="' . $datatable['options']['footer'] . '"' : '' !!}>
                    <tr>
                        @foreach ($datatable['columns'] as $column)
                            <th></th>
                        @endforeach
                    </tr>
                </tfoot>
            @endisset
        </table>
    @endif
@endforeach
