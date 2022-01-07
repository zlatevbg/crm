@extends('layouts.main')

@section('home')
<section class="mt-3 overview-grid-container">
    <div>
        <article class="card card-border">
            <header class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0 font-weight-bold text-uppercase">@lang('labels.clientProfile')</h4>
                @include('partials/buttons')
            </header>
            <div class="card-body">
                <h4 class="card-title mb-0 font-weight-bold">@lang('labels.personalDetails')</h4>
                <table class="mt-3 table table-sm table-card">
                    <tbody>
                        <tr>
                            <th>@lang('labels.firstName'):</th>
                            <td>{{ $api->model->first_name }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.lastName'):</th>
                            <td>{{ $api->model->last_name }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.gender'):</th>
                            <td>@lang('labels.' . $api->model->gender)</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.phone'):</th>
                            <td>{{ $api->model->phone }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.email'):</th>
                            <td>{{ $api->model->email }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.passport'):</th>
                            <td>{{ $api->model->passport }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-body">
                <h4 class="card-title mb-0 font-weight-bold">@lang('labels.address')</h4>
                <table class="mt-3 table table-sm table-card">
                    <tbody>
                        <tr>
                            <th>@lang('labels.country'):</th>
                            <td>{{ optional($api->model->country)->name }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.postcode'):</th>
                            <td>{{ $api->model->postcode }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.city'):</th>
                            <td>{{ $api->model->city }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.address1'):</th>
                            <td>{{ $api->model->address1 }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.address2'):</th>
                            <td>{{ $api->model->address2 }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-body">
                <h4 class="card-title mb-0 font-weight-bold">@lang('labels.prefrences')</h4>
                <table class="mt-3 table table-sm table-card">
                    <tbody>
                        <tr>
                            <th>@lang('labels.newslettersSubscription'):</th>
                            <td>@lang('labels.' . ($api->model->newsletters ? 'yes' : 'no'))</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.smsSubscription'):</th>
                            <td>@lang('labels.' . ($api->model->sms ? 'yes' : 'no'))</td>
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
                                <th>@lang('labels.source'):</th>
                                <td>{{ optional($api->model->source)->name }}</td>
                            </tr>
                            <tr>
                                <th>@lang('labels.project'):</th>
                                <td>{{ $api->model->projects->implode('name', ', ') }}</td>
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
        @if ($api->model->agent)
        <article class="card card-border mt-3">
            <header class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0 font-weight-bold text-uppercase">@lang('labels.agentProfile')</h4>
                @include('partials/buttons', ['buttons' => $buttonsAgents])
            </header>
            <div class="card-body">
                <h4 class="card-title mb-0 font-weight-bold">@lang('labels.agentDetails')</h4>
                <table class="mt-3 table table-sm table-card">
                    <tbody>
                        <tr>
                            <th>@lang('labels.company'):</th>
                            <td>{{ $api->model->agent->company }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.type'):</th>
                            <td>{{ $api->model->agent->type ? trans('labels.' . $api->model->agent->type) : '' }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.website'):</th>
                            <td>{{ $api->model->agent->website }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </article>
        @endif
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
