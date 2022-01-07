@inject('datatable', '\App\Services\Datatable')

@extends('layouts.main')

@section('home')
<section class="mt-3 overview-grid-container">
    <article class="card card-border">
        <header class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0 font-weight-bold text-uppercase">@lang('labels.sale')</h4>
            @include('partials/buttons')
        </header>
        <div class="card-body">
            <h4 class="card-title mb-0 font-weight-bold">@lang('labels.saleDetails')</h4>
            <table class="mt-3 table table-sm table-card">
                <tbody>
                    <tr>
                        <th>@lang('labels.closingAt'):</th>
                        <td>{{ $api->model->closing_at }}</td>
                    </tr>
                    <tr>
                        <th>@lang('labels.promissoryAt'):</th>
                        <td>{{ $api->model->promissory_at }}</td>
                    </tr>
                    <tr>
                        <th>@lang('labels.balance'):</th>
                        <td>{!! '&euro;' . number_format($api->model->price + $api->model->furniture - $api->model->payments->sum('amount') , 2, '.', ' ') !!}</td>
                    </tr>
                    <tr>
                        <th>@lang('labels.price'):</th>
                        <td>{!! $datatable->price($api->model, 'price')->first()->price !!}</td>
                    </tr>
                    <tr>
                        <th>@lang('labels.furniturePack'):</th>
                        <td>{!! $datatable->price($api->model, 'furniture')->first()->furniture !!}</td>
                    </tr>
                    <tr>
                        <th>@lang('labels.commission'):</th>
                        <td>{!! $datatable->price($api->model, 'commission')->first()->commission !!}</td>
                    </tr>
                    @if ($api->model->sub_commission > 0)
                        <tr>
                            <th>@lang('labels.subAgent'):</th>
                            <td>{{ optional($api->model->subagent)->company }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.subCommission'):</th>
                            <td>{!! $datatable->price($api->model, 'sub_commission')->first()->sub_commission !!}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>@lang('labels.lawyer'):</th>
                        <td>{{ $api->model->lawyer }}</td>
                    </tr>
                    <tr>
                        <td class="description" colspan="2">{{ $api->model->description }}</td>
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
                            <th>@lang('labels.createdAt'):</th>
                            <td>{{ $api->model->created_at }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.updatedAt'):</th>
                            <td>{{ $api->model->updated_at }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.modifiedBy'):</th>
                            <td>{{ optional($api->model->user)->name }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endcan
    </article>
    <div>
        <section class="card-deck mb-3">
            <article class="card card-border">
                <header class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 font-weight-bold text-uppercase">@lang('labels.apartment')</h4>
                    @include('partials/buttons', ['buttons' => $buttonsApartment])
                </header>
                <div class="card-body">
                    <h4 class="card-title mb-0 font-weight-bold">@lang('labels.projectDetails')</h4>
                    <table class="mt-3 mb-4 table table-sm table-card">
                        <tbody>
                            <tr>
                                <th>@lang('labels.name'):</th>
                                <td>{{ $api->model->project->name }}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.city'):</th>
                                <td>{{ $api->model->project->city }}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.country'):</th>
                                <td>{{ $api->model->project->country->name }}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.description'):</th>
                                <td>{{ $api->model->project->description }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <h4 class="card-title mb-0 font-weight-bold">@lang('labels.apartmentDetails')</h4>
                    <table class="mt-3 mb-4 table table-sm table-card">
                        <tbody>
                            <tr>
                                <th>@lang('labels.unit'):</th>
                                <td>{{ $api->model->apartment->unit }}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.price'):</th>
                                <td>{!! $datatable->price($api->model->apartment, 'price')->first()->price !!}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.block'):</th>
                                <td>{{ optional($api->model->apartment->block)->name }}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.floor'):</th>
                                <td>{{ optional($api->model->apartment->floor)->name }}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.bed'):</th>
                                <td>{{ optional($api->model->apartment->bed)->name }}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.view'):</th>
                                <td>{{ optional($api->model->apartment->view)->name }}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.furniture'):</th>
                                <td>{{ optional($api->model->apartment->furniture)->name }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <h4 class="card-title mb-0 font-weight-bold">@lang('labels.apartmentAreas')</h4>
                    <table class="mt-3 table table-sm table-card">
                        <tbody>
                            <tr>
                                <th>@lang('labels.apartmentArea'):</th>
                                <td>{!! $api->model->apartment->apartment_area ? $api->model->apartment->apartment_area . ' m<sup>2</sup>' : '' !!}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.balconyArea'):</th>
                                <td>{!! $api->model->apartment->balcony_area ? $api->model->apartment->balcony_area . ' m<sup>2</sup>' : '' !!}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.parkingArea'):</th>
                                <td>{!! $api->model->apartment->parking_area ? $api->model->apartment->parking_area . ' m<sup>2</sup>' : '' !!}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.commonArea'):</th>
                                <td>{!! $api->model->apartment->common_area ? $api->model->apartment->common_area . ' m<sup>2</sup>' : '' !!}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.totalArea'):</th>
                                <td>{!! $api->model->apartment->total_area ? $api->model->apartment->total_area . ' m<sup>2</sup>' : '' !!}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>

            @if ($api->model->client)
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
                                    <td>{{ $api->model->client->first_name }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('labels.lastName'):</th>
                                    <td>{{ $api->model->client->last_name }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('labels.gender'):</th>
                                    <td>@lang('labels.' . $api->model->client->gender)</td>
                                </tr>
                                <tr>
                                    <th>@lang('labels.phone'):</th>
                                    <td>{{ $api->model->client->phone }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('labels.email'):</th>
                                    <td>{{ $api->model->client->email }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('labels.passport'):</th>
                                    <td>{{ $api->model->client->passport }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <h4 class="card-title mb-0 font-weight-bold">@lang('labels.address')</h4>
                        <table class="mt-3 mb-4 table table-sm table-card">
                            <tbody>
                                <tr>
                                    <th>@lang('labels.country'):</th>
                                    <td>{{ optional($api->model->client->country)->name }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('labels.postcode'):</th>
                                    <td>{{ $api->model->client->postcode }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('labels.city'):</th>
                                    <td>{{ $api->model->client->city }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('labels.address1'):</th>
                                    <td>{{ $api->model->client->address1 }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('labels.address2'):</th>
                                    <td>{{ $api->model->client->address2 }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <h4 class="card-title mb-0 font-weight-bold">@lang('labels.prefrences')</h4>
                        <table class="mt-3 table table-sm table-card">
                            <tbody>
                                <tr>
                                    <th>@lang('labels.newslettersSubscription'):</th>
                                    <td>@lang('labels.' . ($api->model->client->newsletters ? 'yes' : 'no'))</td>
                                </tr>
                                <tr>
                                    <th>@lang('labels.smsSubscription'):</th>
                                    <td>@lang('labels.' . ($api->model->client->sms ? 'yes' : 'no'))</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </article>
            @endif

            @if ($api->model->agent)
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
                                <td>{{ $api->model->agent->company }}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.type'):</th>
                                <td>@lang('labels.' . $api->model->agent->type)</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.website'):</th>
                                <td>{{ $api->model->agent->website }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <h4 class="card-title mb-0 font-weight-bold">@lang('labels.address')</h4>
                    <table class="mt-3 mb-4 table table-sm table-card">
                        <tbody>
                            <tr>
                                <th>@lang('labels.country'):</th>
                                <td>{{ optional($api->model->agent->country)->name }}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.postcode'):</th>
                                <td>{{ $api->model->agent->postcode }}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.city'):</th>
                                <td>{{ $api->model->agent->city }}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.address1'):</th>
                                <td>{{ $api->model->agent->address1 }}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.address2'):</th>
                                <td>{{ $api->model->agent->address2 }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>
            @endif
        </section>

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
