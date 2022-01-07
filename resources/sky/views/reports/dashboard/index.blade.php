@extends('layouts.main')

@if (session()->has('project'))

@section('content')
    <div class="mt-3">
        <div class="datatable-buttons">
            <a href="{{ secure_url('reports/dashboard/sales') }}" class="btn p-0"><h1 class="h3 report-heading">@lang('labels.sales')<i class="fas fa-angle-right fa-lg"></i></h1></a>
        </div>
        <p><em>@lang('buttons.allTime')</em></p>
        <section class="card-deck">
           @can('View Dashboard Sales')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.unitSales')</div>
                    <div class="card-body m-auto">
                        <div class="report-value">
                            <p>{{ $salesBreakdown['sales'] }}</p>
                            <p class="small">@lang('labels.agents'): <strong>{{ $salesBreakdown['agents'] }}</strong>, @lang('labels.direct'): <strong>{{ $salesBreakdown['direct'] }}</strong></p>
                        </div>
                    </div>
                </article>
            @endcan

            @can('View Dashboard Revenue')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.salesRevenue')</div>
                    <div class="card-body m-auto">
                        <div class="report-value">
                            <p>€{{ number_format($revenue, 0) }}</p>
                        </div>
                    </div>
                </article>
            @endcan

            @can('View Dashboard Revenue Received')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.salesRevenueReceived')</div>
                    <div class="card-body m-auto">
                        <div class="report-value">
                            <p>€{{ number_format($revenueReceived, 0) }}</p>
                        </div>
                    </div>
                </article>
            @endcan

            @can('View Dashboard Outstanding Revenue')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.salesOutstandingRevenue')</div>
                    <div class="card-body m-auto">
                        <div class="report-value">
                            <p>€{{ number_format($outstandingRevenue, 0) }}</p>
                        </div>
                    </div>
                </article>
            @endcan
        </section>
    </div>

   @can('View Dashboard Investors')
    <div class="mt-3">
        <div class="datatable-buttons">
            <a href="{{ secure_url('reports/dashboard/funding') }}" class="btn p-0"><h1 class="h3 report-heading">@lang('labels.funding')<i class="fas fa-angle-right fa-lg"></i></h1></a>
        </div>
        <p><em>@lang('buttons.allTime')</em></p>
        <section class="card-deck">
            <article class="card text-center col-chart">
                <div class="card-header">@lang('labels.investors')</div>
                <div class="card-body m-auto">
                    <div class="report-value">
                        <p>{{ $investors }}</p>
                    </div>
                </div>
            </article>
        </section>
    </div>
    @endcan

    @can('View: Analytics')
    <div class="mt-3">
        <div class="datatable-buttons">
            <a href="{{ secure_url('reports/dashboard/analytics') }}" class="btn p-0"><h1 class="h3 report-heading">@lang('labels.analytics')<i class="fas fa-angle-right fa-lg"></i></h1></a>
            <button data-target=".modal" data-toggle="modal" data-action="/view-google-analytics" class="btn btn-info btn-analytics fa-left" data-method="get"><i class="fas fa-chart-bar"></i>@lang('buttons.googleAnalytics')</button>
            <button data-target=".modal" data-toggle="modal" data-action="/view-google-ads" class="btn btn-info btn-analytics fa-left" data-method="get"><i class="fas fa-chart-bar"></i>@lang('buttons.googleAds')</button>
            <button data-target=".modal" data-toggle="modal" data-action="/view-google-search-console" class="btn btn-info btn-analytics fa-left" data-method="get"><i class="fas fa-chart-bar"></i>@lang('buttons.googleSearchConsole')</button>
            <button data-target=".modal" data-toggle="modal" data-action="/view-youtube" class="btn btn-info btn-analytics fa-left" data-method="get"><i class="fas fa-chart-bar"></i>@lang('buttons.youtube')</button>
            <a target="_blank" class="btn btn-info btn-analytics fa-left" href="https://mespileuropeanventures-my.sharepoint.com/:f:/g/personal/mitko_mespil_ie/Ekvoio8RpghAlHn_AhDo1CcBu4N52jkH1733AEFfyCAgMg?e=AhH7zQ"><i class="fas fa-chart-bar"></i>@lang('buttons.socialMedia')</a>
        </div>
        <p><em>@lang('buttons.last30Days')</em></p>
        <section class="card-deck">
            <article class="card text-center col-chart">
                <div class="card-header">@lang('text.users')</div>
                <div class="card-body m-auto">
                    <div class="report-value">
                        <p>{{ $values['users']['current'] ?? 0 }}</p>
                        @isset ($values['users']['previous'])
                            <p class="small {{ $values['users']['percentChange'] > 0 ? 'up' : ($values['users']['percentChange'] < 0 ? 'down' : '') }}">
                                @if ($values['users']['percentChange'] > 0)
                                    <i class="fas fa-long-arrow-alt-up"></i>
                                @elseif ($values['users']['percentChange'] < 0)
                                    <i class="fas fa-long-arrow-alt-down"></i>
                                @endif
                                {{ $values['users']['percentChange'] }}%
                            </p>
                        @endif
                    </div>
                </div>
            </article>
            <article class="card text-center col-chart">
                <div class="card-header">@lang('text.newUsers')</div>
                <div class="card-body m-auto">
                    <div class="report-value">
                        <p>{{ $values['newUsers']['current'] ?? 0 }}</p>
                        @isset ($values['newUsers']['previous'])
                            <p class="small {{ $values['newUsers']['percentChange'] > 0 ? 'up' : ($values['newUsers']['percentChange'] < 0 ? 'down' : '') }}">
                                @if ($values['newUsers']['percentChange'] > 0)
                                    <i class="fas fa-long-arrow-alt-up"></i>
                                @elseif ($values['newUsers']['percentChange'] < 0)
                                    <i class="fas fa-long-arrow-alt-down"></i>
                                @endif
                                {{ $values['newUsers']['percentChange'] }}%
                            </p>
                        @endif
                    </div>
                </div>
            </article>
            <article class="card text-center col-chart">
                <div class="card-header">@lang('text.sessions')</div>
                <div class="card-body m-auto">
                    <div class="report-value">
                        <p>{{ $values['sessions']['current'] ?? 0 }}</p>
                        @isset ($values['sessions']['previous'])
                            <p class="small {{ $values['sessions']['percentChange'] > 0 ? 'up' : ($values['sessions']['percentChange'] < 0 ? 'down' : '') }}">
                                @if ($values['sessions']['percentChange'] > 0)
                                    <i class="fas fa-long-arrow-alt-up"></i>
                                @elseif ($values['sessions']['percentChange'] < 0)
                                    <i class="fas fa-long-arrow-alt-down"></i>
                                @endif
                                {{ $values['sessions']['percentChange'] }}%
                            </p>
                        @endif
                    </div>
                </div>
            </article>
            <article class="card text-center col-chart">
                <div class="card-header">@lang('text.sessionsPerUser')</div>
                <div class="card-body m-auto">
                    <div class="report-value">
                        <p>{{ round($values['sessionsPerUser']['current'] ?? 0, 2) }}</p>
                        @isset ($values['sessionsPerUser']['previous'])
                            <p class="small {{ $values['sessionsPerUser']['percentChange'] > 0 ? 'up' : ($values['sessionsPerUser']['percentChange'] < 0 ? 'down' : '') }}">
                                @if ($values['sessionsPerUser']['percentChange'] > 0)
                                    <i class="fas fa-long-arrow-alt-up"></i>
                                @elseif ($values['sessionsPerUser']['percentChange'] < 0)
                                    <i class="fas fa-long-arrow-alt-down"></i>
                                @endif
                                {{ $values['sessionsPerUser']['percentChange'] }}%
                            </p>
                        @endif
                    </div>
                </div>
            </article>
        </section>
        <section class="card-deck">
            <article class="card text-center col-chart">
                <div class="card-header">@lang('text.pageviews')</div>
                <div class="card-body m-auto">
                    <div class="report-value">
                        <p>{{ $values['pageviews']['current'] ?? 0 }}</p>
                        @isset ($values['pageviews']['previous'])
                            <p class="small {{ $values['pageviews']['percentChange'] > 0 ? 'up' : ($values['pageviews']['percentChange'] < 0 ? 'down' : '') }}">
                                @if ($values['pageviews']['percentChange'] > 0)
                                    <i class="fas fa-long-arrow-alt-up"></i>
                                @elseif ($values['pageviews']['percentChange'] < 0)
                                    <i class="fas fa-long-arrow-alt-down"></i>
                                @endif
                                {{ $values['pageviews']['percentChange'] }}%
                            </p>
                        @endif
                    </div>
                </div>
            </article>
            <article class="card text-center col-chart">
                <div class="card-header">@lang('text.pageviewsPerSession')</div>
                <div class="card-body m-auto">
                    <div class="report-value">
                        <p>{{ round($values['pageviewsPerSession']['current'] ?? 0, 2) }}</p>
                        @isset ($values['pageviewsPerSession']['previous'])
                            <p class="small {{ $values['pageviewsPerSession']['percentChange'] > 0 ? 'up' : ($values['pageviewsPerSession']['percentChange'] < 0 ? 'down' : '') }}">
                                @if ($values['pageviewsPerSession']['percentChange'] > 0)
                                    <i class="fas fa-long-arrow-alt-up"></i>
                                @elseif ($values['pageviewsPerSession']['percentChange'] < 0)
                                    <i class="fas fa-long-arrow-alt-down"></i>
                                @endif
                                {{ $values['pageviewsPerSession']['percentChange'] }}%
                            </p>
                        @endif
                    </div>
                </div>
            </article>
            <article class="card text-center col-chart">
                <div class="card-header">@lang('text.avgSessionDuration')</div>
                <div class="card-body m-auto">
                    <div class="report-value">
                        <p>{{ gmdate("H:i:s", $values['avgSessionDuration']['current'] ?? 0) }}</p>
                        @isset ($values['avgSessionDuration']['previous'])
                            <p class="small {{ $values['avgSessionDuration']['percentChange'] > 0 ? 'up' : ($values['avgSessionDuration']['percentChange'] < 0 ? 'down' : '') }}">
                                @if ($values['avgSessionDuration']['percentChange'] > 0)
                                    <i class="fas fa-long-arrow-alt-up"></i>
                                @elseif ($values['avgSessionDuration']['percentChange'] < 0)
                                    <i class="fas fa-long-arrow-alt-down"></i>
                                @endif
                                {{ $values['avgSessionDuration']['percentChange'] }}%
                            </p>
                        @endif
                    </div>
                </div>
            </article>
            <article class="card text-center col-chart">
                <div class="card-header">@lang('text.bounceRate')</div>
                <div class="card-body m-auto">
                    <div class="report-value">
                        <p>{{ round($values['bounceRate']['current'] ?? 0, 2) }}</p>
                        @isset ($values['bounceRate']['previous'])
                            <p class="small {{ $values['bounceRate']['percentChange'] > 0 ? 'down' : ($values['bounceRate']['percentChange'] < 0 ? 'up' : '') }}">
                                @if ($values['bounceRate']['percentChange'] > 0)
                                    <i class="fas fa-long-arrow-alt-up"></i>
                                @elseif ($values['bounceRate']['percentChange'] < 0)
                                    <i class="fas fa-long-arrow-alt-down"></i>
                                @endif
                                {{ $values['bounceRate']['percentChange'] }}%
                            </p>
                        @endif
                    </div>
                </div>
            </article>
        </section>
    @endcan
@endsection

@push('scripts')
    <script>
        loadjs.ready('main', function() {
            $.datepicker.regional.{{ App::getLocale() }} = unikatSettings.datepicker.{{ App::getLocale() }};
            $.datepicker.setDefaults($.datepicker.regional.{{ App::getLocale() }});

            var from;
            var to;

            function updateButtonDates() {
              if (from || to) {
                $('#button-dates').removeAttr('disabled');
              } else {
                $('#button-dates').attr('disabled', 'disabled');
              }
            }

            $('#input-from').datepicker({
                changeYear: true,
                changeMonth: true,
                yearRange: '2018:{{ date('Y') }}',
                maxDate: 0,
                onSelect: function(date) {
                    var d = Date.parse($("#input-from").datepicker("getDate"));
                    $('#input-to').datepicker('option', 'minDate', new Date(d));
                    from = date;
                    updateButtonDates();
                },
            });

            $('#input-to').datepicker({
                changeYear: true,
                changeMonth: true,
                yearRange: '2018:{{ date('Y') }}',
                maxDate: 0,
                onSelect: function(date) {
                    to = date;
                    updateButtonDates();
                },
            });

            $('#input-from').on('input', function() {
              from = this.value;
              updateButtonDates();
            });

            $('#input-to').on('input', function() {
              to = this.value;
              updateButtonDates();
            });
        });
    </script>
@endpush

@endif
