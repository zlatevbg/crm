@inject('datatable', '\App\Services\Datatable')

@extends('layouts.main')

@section('home')
<section class="mt-3 overview-grid-container">
    <article class="card card-border">
        <header class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0 font-weight-bold text-uppercase">@lang('labels.apartment')</h4>
            @include('partials/buttons')
        </header>
        <div class="card-body">
            <h4 class="card-title mb-0 font-weight-bold">@lang('labels.apartmentDetails')</h4>
            <table class="mt-3 table table-sm table-card">
                <tbody>
                    <tr>
                        <th>@lang('labels.unit'):</th>
                        <td>{{ $api->model->unit }}</td>
                    </tr>
                    <tr>
                        <th>@lang('labels.status'):</th>
                        <td>{{ $api->model->statuses()->whereNull('apartment_status.deleted_at')->orderBy('apartment_status.created_at', 'desc')->first()->name }}</td>
                    </tr>
                    <tr>
                        <th>@lang('labels.price'):</th>
                        <td>{!! $datatable->price($api->model, 'price')->first()->price !!}</td>
                    </tr>
                    <tr>
                        <th>@lang('labels.project'):</th>
                        <td>{{ $api->model->project->name }}</td>
                    </tr>
                    <tr>
                        <th>@lang('labels.block'):</th>
                        <td>{{ optional($api->model->block)->name }}</td>
                    </tr>
                    <tr>
                        <th>@lang('labels.floor'):</th>
                        <td>{{ optional($api->model->floor)->name }}</td>
                    </tr>
                    <tr>
                        <th>@lang('labels.bed'):</th>
                        <td>{{ optional($api->model->bed)->name }}</td>
                    </tr>
                    <tr>
                        <th>@lang('labels.view'):</th>
                        <td>{{ optional($api->model->view)->name }}</td>
                    </tr>
                    <tr>
                        <th>@lang('labels.furniture'):</th>
                        <td>{{ optional($api->model->furniture)->name }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-body">
            <h4 class="card-title mb-0 font-weight-bold">@lang('labels.apartmentAreas')</h4>
            <table class="mt-3 table table-sm table-card">
                <tbody>
                    <tr>
                        <th>@lang('labels.apartmentArea'):</th>
                        <td>{!! $api->model->apartment_area ? $api->model->apartment_area . ' m<sup>2</sup>' : '' !!}</td>
                    </tr>
                    <tr>
                        <th>@lang('labels.balconyArea'):</th>
                        <td>{!! $api->model->balcony_area ? $api->model->balcony_area . ' m<sup>2</sup>' : '' !!}</td>
                    </tr>
                    <tr>
                        <th>@lang('labels.parkingArea'):</th>
                        <td>{!! $api->model->parking_area ? $api->model->parking_area . ' m<sup>2</sup>' : '' !!}</td>
                    </tr>
                    <tr>
                        <th>@lang('labels.commonArea'):</th>
                        <td>{!! $api->model->common_area ? $api->model->common_area . ' m<sup>2</sup>' : '' !!}</td>
                    </tr>
                    <tr>
                        <th>@lang('labels.totalArea'):</th>
                        <td>{!! $api->model->total_area ? $api->model->total_area . ' m<sup>2</sup>' : '' !!}</td>
                    </tr>
                </tbody>
            </table>
        </div>
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
                            <th>@lang('labels.includeInReports'):</th>
                            <td>@lang('labels.' . ($api->model->reports ? 'yes' : 'no'))</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.includeInPublic'):</th>
                            <td>@lang('labels.' . ($api->model->public ? 'yes' : 'no'))</td>
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
        @if ($sale)
            <section class="card-deck mb-3">
                <article class="card card-border">
                    <header class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 font-weight-bold text-uppercase">@lang('labels.sale')</h4>
                        @include('partials/buttons', ['buttons' => $buttonsSale])
                    </header>
                    <div class="card-body">
                        <h4 class="card-title mb-0 font-weight-bold">@lang('labels.saleDetails')</h4>
                        <table class="mt-3 mb-4 table table-sm table-card">
                            <tbody>
                                <tr>
                                    <th>@lang('labels.closingAt'):</th>
                                    <td>{{ $sale->closing_at }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('labels.promissoryAt'):</th>
                                    <td>{{ $sale->promissory_at }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('labels.price'):</th>
                                    <td>{!! $datatable->price($sale, 'price')->first()->price !!}</td>
                                </tr>
                                <tr>
                                    <th>@lang('labels.furniturePack'):</th>
                                    <td>{!! $datatable->price($sale, 'furniture')->first()->furniture !!}</td>
                                </tr>
                                <tr>
                                    <th>@lang('labels.commission'):</th>
                                    <td>{!! $datatable->price($sale, 'commission')->first()->commission !!}</td>
                                </tr>
                                @if ($sale->sub_commission > 0)
                                    <tr>
                                        <th>@lang('labels.subAgent'):</th>
                                        <td>{{ optional($sale->agent)->company }}</td>
                                    </tr>
                                    <tr>
                                        <th>@lang('labels.subCommission'):</th>
                                        <td>{!! $datatable->price($sale, 'sub_commission')->first()->sub_commission !!}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>@lang('labels.lawyer'):</th>
                                    <td>{{ $sale->lawyer }}</td>
                                </tr>
                                @if ($sale->description)
                                    <tr>
                                        <td class="description" colspan="2">{{ $sale->description }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </article>

                @if ($sale->client)
                    <article class="card card-border">
                        <header class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0 font-weight-bold text-uppercase">@lang('labels.client')</h4>
                            @include('partials/buttons', ['buttons' => $buttonsClient])
                        </header>
                        <div class="card-body">
                            <h4 class="card-title mb-0 font-weight-bold">@lang('labels.personalDetails')</h4>
                            <table class="mt-3 mb-4 table table-sm table-card">
                                <tbody>
                                    <tr>
                                        <th>@lang('labels.firstName'):</th>
                                        <td>{{ $sale->client->first_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>@lang('labels.lastName'):</th>
                                        <td>{{ $sale->client->last_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>@lang('labels.gender'):</th>
                                        <td>@lang('labels.' . $sale->client->gender)</td>
                                    </tr>
                                    <tr>
                                        <th>@lang('labels.phone'):</th>
                                        <td>{{ $sale->client->phone }}</td>
                                    </tr>
                                    <tr>
                                        <th>@lang('labels.email'):</th>
                                        <td>{{ $sale->client->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>@lang('labels.passport'):</th>
                                        <td>{{ $sale->client->passport }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <h4 class="card-title mb-0 font-weight-bold">@lang('labels.address')</h4>
                            <table class="mt-3 mb-4 table table-sm table-card">
                                <tbody>
                                    <tr>
                                        <th>@lang('labels.country'):</th>
                                        <td>{{ optional($sale->client->country)->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>@lang('labels.postcode'):</th>
                                        <td>{{ $sale->client->postcode }}</td>
                                    </tr>
                                    <tr>
                                        <th>@lang('labels.city'):</th>
                                        <td>{{ $sale->client->city }}</td>
                                    </tr>
                                    <tr>
                                        <th>@lang('labels.address1'):</th>
                                        <td>{{ $sale->client->address1 }}</td>
                                    </tr>
                                    <tr>
                                        <th>@lang('labels.address2'):</th>
                                        <td>{{ $sale->client->address2 }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <h4 class="card-title mb-0 font-weight-bold">@lang('labels.prefrences')</h4>
                            <table class="mt-3 table table-sm table-card">
                                <tbody>
                                    <tr>
                                        <th>@lang('labels.newslettersSubscription'):</th>
                                        <td>@lang('labels.' . ($sale->client->newsletters ? 'yes' : 'no'))</td>
                                    </tr>
                                    <tr>
                                        <th>@lang('labels.smsSubscription'):</th>
                                        <td>@lang('labels.' . ($sale->client->sms ? 'yes' : 'no'))</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </article>

                    @if ($sale->client->agent)
                        <article class="card card-border">
                            <header class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="mb-0 font-weight-bold text-uppercase">@lang('labels.agent')</h4>
                                @include('partials/buttons', ['buttons' => $buttonsAgent])
                            </header>
                            <div class="card-body">
                                <h4 class="card-title mb-0 font-weight-bold">@lang('labels.companyDetails')</h4>
                                <table class="mt-3 mb-4 table table-sm table-card">
                                    <tbody>
                                        <tr>
                                            <th>@lang('labels.company'):</th>
                                            <td>{{ $sale->client->agent->company }}</td>
                                        </tr>
                                        <tr>
                                            <th>@lang('labels.type'):</th>
                                            <td>@lang('labels.' . $sale->client->agent->type)</td>
                                        </tr>
                                        <tr>
                                            <th>@lang('labels.website'):</th>
                                            <td>{{ $sale->client->agent->website }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <h4 class="card-title mb-0 font-weight-bold">@lang('labels.address')</h4>
                                <table class="mt-3 mb-4 table table-sm table-card">
                                    <tbody>
                                        <tr>
                                            <th>@lang('labels.country'):</th>
                                            <td>{{ optional($sale->client->agent->country)->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>@lang('labels.postcode'):</th>
                                            <td>{{ $sale->client->agent->postcode }}</td>
                                        </tr>
                                        <tr>
                                            <th>@lang('labels.city'):</th>
                                            <td>{{ $sale->client->agent->city }}</td>
                                        </tr>
                                        <tr>
                                            <th>@lang('labels.address1'):</th>
                                            <td>{{ $sale->client->agent->address1 }}</td>
                                        </tr>
                                        <tr>
                                            <th>@lang('labels.address2'):</th>
                                            <td>{{ $sale->client->agent->address2 }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </article>
                    @endif
                @endif
            </section>
        @endif

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
