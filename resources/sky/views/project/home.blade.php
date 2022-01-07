@inject('datatable', '\App\Services\Datatable')

@extends('layouts.main')

@section('home')
<section class="mt-3 overview-grid-container">
    <article class="card card-border">
        <header class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0 font-weight-bold text-uppercase">{{ $api->model->name }}</h4>
            @include('partials/buttons')
        </header>
        @if ($api->model->status == 0)
            <div class="card-body">
                <h4 class="card-title mb-0 font-weight-bold">@lang('labels.projectDetails')</h4>
                <table class="mt-3 table table-sm table-card">
                    <tbody>
                        <tr>
                            <th>@lang('labels.location'):</th>
                            <td>{{ $api->model->location }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.country'):</th>
                            <td>{{ $api->model->country->name }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.price'):</th>
                            <td>{!! $datatable->price($api->model, 'price')->first()->price !!}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.siteArea'):</th>
                            <td>{!! $api->model->site_area ? $api->model->site_area . ' m<sup>2</sup>' : '' !!}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.constructionArea'):</th>
                            <td>{!! $api->model->construction_area ? $api->model->construction_area . ' m<sup>2</sup>' : '' !!}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.gdv'):</th>
                            <td>{!! $datatable->price($api->model, 'gdv')->first()->gdv !!}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.equity'):</th>
                            <td>{!! $datatable->price($api->model, 'equity')->first()->equity !!}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.bank'):</th>
                            <td>{!! $datatable->price($api->model, 'bank')->first()->bank !!}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.investmentPeriod'):</th>
                            <td>{{ $api->model->period }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.targetIrr'):</th>
                            <td>{{ $api->model->irr }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.introducer'):</th>
                            <td>{{ optional($api->model->contact)->company }}</td>
                        </tr>
                        @if ($api->model->description)
                            <tr>
                                <td class="description" colspan="2">{{ $api->model->description }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="card-body">
                <h4 class="card-title mb-0 font-weight-bold">@lang('labels.projectFeatures') <span class="badge badge-{{ $score <= 50 ? 'danger' : 'success' }}">{{ $score }}</span></h4>
                <table class="mt-3 table table-sm table-card">
                    <tbody>
                        @foreach ($api->model->selectFeatures() as $features)
                            <tr>
                                <th>{{ $features['name'] }}:</th>
                                <td>{{ optional($api->model->features->get($loop->index))->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="card-body">
                <h4 class="card-title mb-0 font-weight-bold">@lang('labels.projectDetails')</h4>
                <table class="mt-3 table table-sm table-card">
                    <tbody>
                        <tr>
                            <th>@lang('labels.location'):</th>
                            <td>{{ $api->model->location }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.country'):</th>
                            <td>{{ $api->model->country->name }}</td>
                        </tr>
                        @if ($api->model->description)
                            <tr>
                                <td class="description" colspan="2">{{ $api->model->description }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        @endif
        @can('View System Details')
            <div class="card-body">
                <h4 class="card-title mb-0 font-weight-bold">@lang('labels.systemDetails')</h4>
                <table class="mt-3 table table-sm table-card">
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
