@extends('layouts.main')

@section('home')
<section class="mt-3 overview-grid-container">
    <div>
        <article class="card card-border">
            <header class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0 font-weight-bold text-uppercase">@lang('labels.leadProfile')</h4>
                @include('partials/buttons')
            </header>
            <div class="card-body">
                <h4 class="card-title mb-0 font-weight-bold">@lang('labels.personalDetails')</h4>
                <table class="table-fixed mt-3 table table-sm table-card">
                    <tbody>
                        <tr>
                            <th>@lang('labels.email'):</th>
                            <td>{{ $api->model->email }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.name'):</th>
                            <td>{{ $api->model->name }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.phone'):</th>
                            <td>{{ $api->model->phone }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.country'):</th>
                            <td>{{ optional($api->model->country)->name }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.notes'):</th>
                            <td>{!! nl2br($api->model->notes) !!}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-body">
                <h4 class="card-title mb-0 font-weight-bold">@lang('labels.source')</h4>
                <table class="table-fixed mt-3 table table-sm table-card">
                    <tbody>
                        <tr>
                            <th>@lang('labels.sources'):</th>
                            <td>{{ $api->model->sources->pluck('name')->implode(', ') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-body">
                <h4 class="card-title mb-0 font-weight-bold">@lang('labels.tag')</h4>
                <table class="table-fixed mt-3 table table-sm table-card">
                    <tbody>
                        <tr>
                            <th>@lang('labels.tags'):</th>
                            <td>{{ $api->model->tags->pluck('name')->implode(', ') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-body">
                <h4 class="card-title mb-0 font-weight-bold">@lang('labels.prefrences')</h4>
                <table class="table-fixed mt-3 table table-sm table-card">
                    <tbody>
                        <tr>
                            <th>@lang('labels.newslettersSubscription'):</th>
                            <td>@lang('labels.' . ($api->model->newsletters ? 'yes' : 'no'))</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @can('View System Details')
                <div class="card-body">
                    <h4 class="card-title mb-0 font-weight-bold">@lang('labels.systemDetails')</h4>
                    <table class="table-fixed mt-3 table table-sm table-card">
                        <tbody>
                            <tr>
                                <th>@lang('labels.id'):</th>
                                <td>{{ $api->model->id }}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.createdAt'):</th>
                                <td>{{ $api->model->created_at }}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.updatedAt'):</th>
                                <td>{{ $api->model->updated_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endcan
        </article>
    </div>
    <div>
    @foreach ($api->tabsOverview as $tab)
        <article class="card mb-3">
            <header class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0 font-weight-bold text-uppercase">{{ $tab['name'] }}</h4>
                @include('partials/buttons', ['buttons' => current($tab['datatables-overview'])['buttons'], 'key' => key($tab['datatables-overview'])])
            </header>
            @include('partials/datatables', ['datatables' => $tab['datatables-overview']])
        </article>
    @endforeach
    </div>
</section>
@endsection
