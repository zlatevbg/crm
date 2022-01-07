@extends('layouts.main')

@if (session()->has('project'))

@section('content')
   <div class="mt-3">
        <h1 class="h3 report-heading">@lang('text.salesSummary')</h1>
        <section class="card-deck">
           @can('View Dashboard Sales')
               <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.unitSales')</div>
                    <div class="card-body p-0 m-auto">
                        <div id="chart-unit-sales" class="report-chart"></div>
                        {{-- <h1 class="card-title mb-0 font-weight-bold">{{ $salesBreakdown['sales'] }}</h1> --}}
                    </div>
                    <div class="card-footer">@lang('labels.agents'): <strong>{{ $salesBreakdown['agents'] }}</strong>, @lang('labels.direct'): <strong>{{ $salesBreakdown['direct'] }}</strong></div>
                </article>
            @endcan

            @can('View Dashboard Revenue')
               <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.salesRevenue')</div>
                    <div class="card-body p-0 m-auto">
                        <div id="chart-sales-revenue" class="report-chart"></div>
                        {{-- <h1 class="card-title mb-0 font-weight-bold">&euro;{{ number_format($revenue, 2) }}</h1> --}}
                    </div>
                    <div class="card-footer">@lang('text.salesRevenueHelp')</div>
                </article>
            @endcan

            @can('View Dashboard Revenue Received')
               <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.salesRevenueReceived')</div>
                    <div class="card-body p-0 m-auto">
                        <div id="chart-sales-revenue-received" class="report-chart"></div>
                        {{-- <h1 class="card-title mb-0 font-weight-bold">&euro;{{ number_format($revenueReceived, 2) }}</h1> --}}
                    </div>
                    <div class="card-footer">@lang('text.salesRevenueReceivedHelp')</div>
                </article>
            @endcan

            @can('View Dashboard Outstanding Revenue')
               <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.salesOutstandingRevenue')</div>
                    <div class="card-body p-0 m-auto">
                        <div id="chart-sales-outstanding-revenue" class="report-chart"></div>
                        {{-- <h1 class="card-title mb-0 font-weight-bold">&euro;{{ number_format($outstandingRevenue, 2) }}</h1> --}}
                    </div>
                    <div class="card-footer">@lang('text.salesOutstandingRevenueHelp')</div>
                </article>
            @endcan
        </section>
        <section class="card-deck">
            @php /*
            @can('View Dashboard Discount')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.discount')</div>
                    <div class="card-body p-0 m-auto">
                        <div id="chart-discount" class="report-chart"></div>
                        {{-- <h1 class="card-title mb-0 font-weight-bold">&euro;{{ number_format($discount, 2) }}</h1> --}}
                    </div>
                    <div class="card-footer">@lang('text.discountHelp')</div>
                </article>
            @endcan
            */ @endphp

            @php /*
            @can('View Dashboard Commission')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.commission')</div>
                    <div class="card-body p-0 m-auto">
                        <div id="chart-commission" class="report-chart"></div>
                        {{-- <h1 class="card-title mb-0 font-weight-bold">&euro;{{ number_format($commission, 2) }}</h1> --}}
                    </div>
                    <div class="card-footer">@lang('text.commissionHelp')</div>
                </article>
            @endcan
            */ @endphp

            @can('View Dashboard Conversion Rate')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.conversion')</div>
                    <div class="card-body p-0 m-auto">
                        <div id="chart-conversion" class="report-chart"></div>
                        {{-- <h1 class="card-title mb-0 font-weight-bold">{{ $conversion }}%</h1> --}}
                    </div>
                    <div class="card-footer">@lang('text.conversionHelp')</div>
                </article>
            @endcan

            @can('View Dashboard Sales Top Countries')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.salesTopCountries')</div>
                    <div class="card-body">
                        <div id="chart-top-sales-countries" class="report-chart"></div>
                    </div>
                </article>
            @endcan
        </section>
        <h1 class="h3 report-heading">@lang('text.apartmentsStatistics')</h1>
        <section class="card-deck">
            @can('View Dashboard Apartments Count')
               <article class="card text-center col-chart">
                    <div class="card-header">@lang('labels.apartments')</div>
                    <div class="card-body p-0 m-auto">
                        <div id="chart-apartments-count" class="report-chart"></div>
                        {{-- <h1 class="card-title mb-0 font-weight-bold">{{ $apartmentsCount }}</h1> --}}
                    </div>
                </article>
            @endcan

            @can('View Dashboard Apartments Status')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.apartmentsStatus')</div>
                    <div class="card-body p-0">
                        <div id="chart-apartments-1" class="report-chart"></div>
                    </div>
                </article>
            @endcan

            @can('View Dashboard Apartments Prices')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('labels.apartmentsPrices')</div>
                    <div class="card-body p-0">
                        <div id="chart-apartments-2" class="report-chart"></div>
                    </div>
                </article>
            @endcan
           @php /*
           @foreach ($apartments as $apartment)
               <article class="card text-center col-chart">
                    <div class="card-header">{{ $apartment['name'] }}</div>
                    <div class="card-body p-0 m-auto">
                        <div id="chart-apartment-{{ $apartment['id'] }}" class="report-chart"></div>
                        {{-- <h1 class="card-title mb-0 font-weight-bold">{{ $apartment['total'] }}</h1> --}}
                    </div>
                    <div class="card-footer">&euro;{{ number_format($apartment['amount'], 2) }}</div>
                </article>
            @endforeach
            */ @endphp
        </section>
        <h1 class="h3 report-heading">@lang('text.clientsStatistics')</h1>
        <section class="card-deck">
            @can('View Dashboard Clients Count')
               <article class="card text-center col-chart">
                    <div class="card-header">@lang('labels.clients')</div>
                    <div class="card-body p-0 m-auto">
                        <div id="chart-clients-count" class="report-chart"></div>
                        {{-- <h1 class="card-title mb-0 font-weight-bold">{{ $clientsCount }}</h1> --}}
                    </div>
                </article>
            @endcan

            @can('View Dashboard Clients Country')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.clientsCountry')</div>
                    <div class="card-body">
                        <div id="chart-clients-country" class="report-chart"></div>
                    </div>
                </article>
            @endcan

            @can('View Dashboard Clients Status')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.clientsStatus')</div>
                    <div class="card-body p-0">
                        <div id="chart-clients-status" class="report-chart"></div>
                        {{-- <section class="card-deck">
                           @foreach ($clientsStatus as $client)
                               <article class="card text-center text-white bg-primary">
                                    <div class="card-header">{{ $client['name'] }}</div>
                                    <div class="card-body">
                                        <h1 class="card-title mb-0 font-weight-bold">{{ $client['total'] }}</h1>
                                    </div>
                                </article>
                            @endforeach
                        </section> --}}
                    </div>
                </article>
            @endcan

            @can('View Dashboard Clients Source')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.clientsSource')</div>
                    <div class="card-body p-0">
                        <div id="chart-clients-source" class="report-chart"></div>
                        {{-- <section class="card-deck">
                           @foreach ($clientsSource as $client)
                               <article class="card text-center text-white bg-primary">
                                    <div class="card-header">{{ $client['name'] }}</div>
                                    <div class="card-body">
                                        <h1 class="card-title mb-0 font-weight-bold">{{ $client['total'] }}</h1>
                                    </div>
                                </article>
                            @endforeach
                        </section> --}}
                    </div>
                </article>
            @endcan
        </section>
        <h1 class="h3 report-heading">@lang('text.agentsStatistics')</h1>
        <section class="card-deck">
            @can('View Dashboard Agents Count')
               <article class="card text-center col-chart">
                    <div class="card-header">@lang('labels.agents')</div>
                    <div class="card-body p-0 m-auto">
                        <div id="chart-agents-count" class="report-chart"></div>
                        {{-- <h1 class="card-title mb-0 font-weight-bold">{{ $agentsCount }}</h1> --}}
                    </div>
                </article>
            @endcan

            @can('View Dashboard Agents Country')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.agentsCountry')</div>
                    <div class="card-body">
                        <div id="chart-agents-country" class="report-chart"></div>
                    </div>
                </article>
            @endcan

            @can('View Dashboard Agents Type')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.agentsType')</div>
                    <div class="card-body p-0">
                        <div id="chart-agents-type" class="report-chart"></div>
                        {{--
                        <section class="card-deck">
                        @foreach ($agents as $agent)
                           <article class="card text-center text-white bg-primary">
                                <div class="card-header">{{ $agent['name'] }}</div>
                                <div class="card-body">
                                    <h1 class="card-title mb-0 font-weight-bold">{{ $agent['total'] }}</h1>
                                </div>
                            </article>
                        @endforeach
                        </section> --}}
                    </div>
                </article>
            @endcan
        </section>
        <section class="card-deck">
            @can('View Dashboard Sales Top Agents')
               <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.salesTopAgents')</div>
                    <div class="card-body">
                        <div id="chart-top-sales-agents" class="report-chart"></div>
                    </div>
                </article>
            @endcan

            @can('View Dashboard Viewings Top Agents')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.viewingsTopAgents')</div>
                    <div class="card-body">
                        <div id="chart-top-viewings-agents" class="report-chart"></div>
                    </div>
                </article>
            @endcan

            @can('View Dashboard Leads Top Agents')
               <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.leadsTopAgents')</div>
                    <div class="card-body">
                        <div id="chart-top-leads-agents" class="report-chart"></div>
                    </div>
                </article>
            @endcan
        </section>
        <h1 class="h3 report-heading">@lang('text.viewingsStatistics')</h1>
        <section class="card-deck">
            @can('View Dashboard Viewings')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.viewings')</div>
                    <div class="card-body p-0 m-auto">
                        <div id="chart-viewings" class="report-chart"></div>
                        {{-- <section class="card-deck">
                           <article class="card text-center text-white bg-primary">
                                <div class="card-header">@lang('text.total')</div>
                                <div class="card-body">
                                    <h1 class="card-title mb-0 font-weight-bold">{{ $viewings->sum('total') }}</h1>
                                </div>
                            </article>
                        </section> --}}
                    </div>
                </article>
            @endcan

            @can('View Dashboard Viewings Top Countries')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.viewingsTopCountries')</div>
                    <div class="card-body">
                        <div id="chart-top-viewings-countries" class="report-chart"></div>
                    </div>
                </article>
            @endcan

            @can('View Dashboard Viewings By Date')
               <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.viewingsByDay')</div>
                    <div class="card-body">
                        <div id="chart-viewings-by-day" class="report-chart"></div>
                    </div>
                </article>
            @endcan
        </section>
        <section class="card-deck mb-4">
            @can('View Dashboard Viewings Reasons')
                <article class="card text-center chart-col-2">
                    <div class="card-header">@lang('text.viewingsReasons')</div>
                    <div class="card-body">
                        <table class="table table-sm table-bordered table-striped table-light mb-0">
                            <thead class="text-white bg-secondary">
                                <tr>
                                    <th class="text-center">@lang('text.#')</th>
                                    <th class="text-left">@lang('text.reason')</th>
                                    <th class="text-right">@lang('text.count')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($viewingsReasons as $number => $reason)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-left">{{ $reason['name'] ?: trans('text.other') }}</td>
                                        <td class="text-right">{{ $reason['total'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>
            @endcan
        </section>
        <h1 class="h3 report-heading">@lang('text.targetsSummary')</h1>
        <section class="card-deck mb-4">
            @can('View Dashboard Targets')
                <article class="card text-center chart-col-2">
                    <div class="card-header">@lang('text.targetVsActual')</div>
                    @if ($targets)
                        <div class="card-body d-flex flex-wrap">
                            <table class="table table-bordered table-striped table-light table-fixed">
                                <thead class="text-white bg-secondary">
                                    <tr>
                                        @if ($targets[0]['period'])<th class="text-center">@lang('labels.period')</th>@endif
                                        <th class="text-center">@lang('labels.salesTarget')</th>
                                        <th class="text-center">@lang('labels.sales')</th>
                                        <th class="text-center">@lang('labels.salesPercentage')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($targets as $target)
                                        <tr>
                                            @if ($target['period'])<td class="text-center">{{ $target['period'] }}</td>@endif
                                            <td class="text-center">{{ $target['salesTarget'] }} @if ($target['diff'])({{ $target['salesTarget'] + $target['diff'] }})@endif</td>
                                            <td class="text-center">{{ $target['sales'] }} (@lang('labels.agents'): <strong>{{ $target['agents'] }}</strong>, @lang('labels.direct'): <strong>{{ $target['direct'] }}</strong>)</td>
                                            <td class="text-center h3 mb-0 text-white p-0 d-flex border-0">
                                                <span style="padding: .75rem 0 .75rem .75rem;" class="w-50 {{  $target['salesPercentage'] < 100 ? 'bg-loss' : 'bg-profit' }}">{!! $target['salesPercentage'] !!}%</span>
                                                <span style="padding: .75rem .75rem .75rem 0;" class="w-50 {{  $target['diffPercentage'] < 100 ? 'bg-loss' : 'bg-profit' }}">({!! $target['diffPercentage'] !!}%)</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <table class="table table-bordered table-striped table-light mb-0 table-fixed">
                                <thead class="text-white bg-secondary">
                                    <tr>
                                        @if ($targets[0]['period'])<th class="text-center">@lang('labels.period')</th>@endif
                                        <th class="text-center">@lang('labels.revenueTarget')</th>
                                        <th class="text-center">@lang('labels.revenue')</th>
                                        <th class="text-center">@lang('labels.revenuePercentage')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($targets as $target)
                                        <tr>
                                            @if ($target['period'])<td class="text-center">{{ $target['period'] }}</td>@endif
                                            <td class="text-center">{!! $target['revenueTarget'] !!}</td>
                                            <td class="text-center">{!! $target['revenue'] !!}</td>
                                            <td class="text-center h3 mb-0 text-white {{  $target['revenuePercentage'] < 100 ? 'bg-loss' : 'bg-profit' }}">{!! $target['revenuePercentage'] !!}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </article>
            @endcan
        </section>
   </div>
@endsection

@push('scripts')
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
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

            var pieChartOptions = {
                'legend': {
                    'position': 'right',
                    'textStyle': {
                        'fontSize': 12,
                    },
                },
                'pieSliceText': 'value',
                'pieResidueSliceLabel': '@lang('text.other')',
                'chartArea': {
                    'top': 10,
                    'width': '100%',
                    'height': '90%',
                },
            };

            var pieValueChartOptions = {
                'legend': {
                    'position': 'none',
                },
                'pieSliceText': 'label',
                'pieSliceTextStyle': {
                    'fontSize': 30,
                },
                'tooltip': {
                    'trigger': 'none',
                },
                'chartArea': {
                    'width': '100%',
                    'height': '90%',
                },
                'width': 300,
                'height': 300,
            };

            var pieSingleChartOptions = {
                'legend': {
                    'position': 'none',
                },
                'pieSliceText': 'value',
                'pieSliceTextStyle': {
                    'fontSize': 50,
                },
                'tooltip': {
                    'trigger': 'none',
                },
                'chartArea': {
                    'width': '100%',
                    'height': '90%',
                },
                'width': 300,
                'height': 300,
            };

            var columnChartOptions = {
                'annotations': {
                    'textStyle': {
                        'fontSize': 16,
                        'bold': true,
                    },
                },
                'tooltip': {
                    'ignoreBounds': true,
                    'showColorCode': true,
                },
                'legend': {
                    'position': 'bottom',
                    'textStyle': {
                        'fontSize': 12,
                    },
                },
            };

            var horizontalColumnChartOptions = {
                'orientation': 'vertical',
                'annotations': {
                    'textStyle': {
                        'fontSize': 16,
                        'bold': true,
                    },
                },
                'tooltip': {
                    'ignoreBounds': true,
                    'showColorCode': true,
                },
                'legend': {
                    'position': 'bottom',
                    'textStyle': {
                        'fontSize': 12,
                    },
                },
            };

            @can('View Dashboard Sales')
                function drawUnitSales() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('text.unitSales')');
                    data.addColumn('number', '@lang('labels.total')');
                    data.addRows([['', {{ $salesBreakdown['sales'] }}]]);

                    var chart = new google.visualization.PieChart(document.getElementById('chart-unit-sales'));
                    chart.draw(data, pieSingleChartOptions);
                }
            @endcan

            @can('View Dashboard Revenue')
                function drawSalesRevenue() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('text.salesRevenue')');
                    data.addColumn('number', '@lang('labels.total')');
                    data.addRows([['€{{ number_format($revenue, 0) }}', 1]]);

                    var chart = new google.visualization.PieChart(document.getElementById('chart-sales-revenue'));
                    chart.draw(data, pieValueChartOptions);
                }
            @endcan

            @can('View Dashboard Revenue Received')
                function drawSalesRevenueReceived() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('text.salesRevenueReceived')');
                    data.addColumn('number', '@lang('labels.total')');
                    data.addRows([['€{{ number_format($revenueReceived, 0) }}', 1]]);

                    var chart = new google.visualization.PieChart(document.getElementById('chart-sales-revenue-received'));
                    chart.draw(data, pieValueChartOptions);
                }
            @endcan

            @can('View Dashboard Outstanding Revenue')
                function drawSalesOutstandingRevenue() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('text.salesOutstandingRevenue')');
                    data.addColumn('number', '@lang('labels.total')');
                    data.addRows([['€{{ number_format($outstandingRevenue, 0) }}', 1]]);

                    var chart = new google.visualization.PieChart(document.getElementById('chart-sales-outstanding-revenue'));
                    chart.draw(data, pieValueChartOptions);
                }
            @endcan

            @can('View Dashboard Discount')
                function drawDiscount() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('text.discount')');
                    data.addColumn('number', '@lang('labels.total')');
                    data.addRows([['€{{ number_format($discount, 0) }}', 1]]);

                    var chart = new google.visualization.PieChart(document.getElementById('chart-discount'));
                    chart.draw(data, pieValueChartOptions);
                }
            @endcan

            @can('View Dashboard Commission')
                function drawCommission() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('text.commission')');
                    data.addColumn('number', '@lang('labels.total')');
                    data.addRows([['€{{ number_format($commission, 0) }}', 1]]);

                    var chart = new google.visualization.PieChart(document.getElementById('chart-commission'));
                    chart.draw(data, pieValueChartOptions);
                }
            @endcan

            @can('View Dashboard Conversion Rate')
                function drawConversion() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('text.conversion')');
                    data.addColumn('number', '@lang('labels.total')');
                    data.addRows([['{{ $conversion }}%', 1]]);

                    var chart = new google.visualization.PieChart(document.getElementById('chart-conversion'));
                    chart.draw(data, pieValueChartOptions);
                }
            @endcan

            @can('View Dashboard Apartments Count')
                function drawApartmentsCount() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.apartments')');
                    data.addColumn('number', '@lang('labels.total')');
                    data.addRows([['', {{ $apartmentsCount }}]]);

                    var chart = new google.visualization.PieChart(document.getElementById('chart-apartments-count'));
                    chart.draw(data, pieSingleChartOptions);
                }
            @endcan

            @php /*function drawApartments() {
                @foreach ($apartments as $apartment)
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('text.apartments')');
                    data.addColumn('number', '@lang('labels.total')');
                    data.addRows([['', {{ $apartment['total'] }}]]);

                    var chart = new google.visualization.PieChart(document.getElementById('chart-apartment-{{ $apartment['id'] }}'));
                    chart.draw(data, pieSingleChartOptions);
                @endforeach
            }*/@endphp

            @can('View Dashboard Apartments Status')
                function drawApartmentsStatus() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.apartments')');
                    @foreach ($apartments as $apartment)
                        data.addColumn('number', '{{ $apartment['name'] }}');
                        data.addColumn({type: 'number', role: 'annotation'});
                    @endforeach
                    data.addRows(@json($apartmentsStatus));

                    var chart = new google.visualization.ColumnChart(document.getElementById('chart-apartments-1'));
                    chart.draw(data, columnChartOptions);
                }
            @endcan

            @can('View Dashboard Apartments Prices')
                function drawApartmentsPrices() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.apartments')');
                    @foreach ($apartments as $apartment)
                        data.addColumn('number', '{{ $apartment['name'] }}');
                        data.addColumn({type: 'number', role: 'annotation'});
                    @endforeach
                    data.addRows(@json($apartmentsPrices));

                    var chart = new google.visualization.ColumnChart(document.getElementById('chart-apartments-2'));
                    chart.draw(data, horizontalColumnChartOptions);
                }
            @endcan

            @can('View Dashboard Clients Count')
                function drawClientsCount() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.clients')');
                    data.addColumn('number', '@lang('labels.total')');
                    data.addRows([['', {{ $clientsCount }}]]);

                    var chart = new google.visualization.PieChart(document.getElementById('chart-clients-count'));
                    chart.draw(data, pieSingleChartOptions);
                }
            @endcan

            @can('View Dashboard Clients Country')
                function drawClientsCountry() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.country')');
                    data.addColumn('number', '@lang('labels.clients')');
                    data.addRows(@json($clientsCountry));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-clients-country'));
                    chart.draw(data, pieChartOptions);
                }
            @endcan

            @can('View Dashboard Clients Status')
                function drawClientsStatus() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.clients')');
                    @foreach ($clientsStatus as $client)
                        data.addColumn('number', '{{ $client['name'] }}');
                        data.addColumn({type: 'number', role: 'annotation'});
                    @endforeach
                    data.addRows(@json($clientsStatusChart));

                    var chart = new google.visualization.ColumnChart(document.getElementById('chart-clients-status'));
                    chart.draw(data, columnChartOptions);
                }
            @endcan

            @can('View Dashboard Clients Source')
                function drawClientsSource() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.clients')');
                    @foreach ($clientsSource as $client)
                        data.addColumn('number', '{{ $client['source'] }}');
                        data.addColumn({type: 'number', role: 'annotation'});
                    @endforeach
                    data.addRows(@json($clientsSourceChart));

                    var chart = new google.visualization.ColumnChart(document.getElementById('chart-clients-source'));
                    chart.draw(data, columnChartOptions);
                }
            @endcan

            @can('View Dashboard Agents Count')
                function drawAgentsCount() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.agents')');
                    data.addColumn('number', '@lang('labels.total')');
                    data.addRows([['', {{ $agentsCount }}]]);

                    var chart = new google.visualization.PieChart(document.getElementById('chart-agents-count'));
                    chart.draw(data, pieSingleChartOptions);
                }
            @endcan

            @can('View Dashboard Agents Country')
                function drawAgentsCountry() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.country')');
                    data.addColumn('number', '@lang('labels.agents')');
                    data.addRows(@json($agentsCountry));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-agents-country'));
                    chart.draw(data, pieChartOptions);
                }
            @endcan

            @can('View Dashboard Agents Type')
                function drawAgents() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.agents')');
                    @foreach ($agents as $agent)
                        data.addColumn('number', '{{ $agent['name'] }}');
                        data.addColumn({type: 'number', role: 'annotation'});
                    @endforeach
                    data.addRows(@json($agentsType));

                    var chart = new google.visualization.ColumnChart(document.getElementById('chart-agents-type'));
                    chart.draw(data, columnChartOptions);
                }
            @endcan

            @can('View Dashboard Viewings')
                function drawViewings() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.viewings')');
                    data.addColumn('number', '@lang('labels.total')');
                    data.addRows([['', {{ $viewings->sum('total') }}]]);

                    var chart = new google.visualization.PieChart(document.getElementById('chart-viewings'));
                    chart.draw(data, pieSingleChartOptions);
                }
            @endcan

            @can('View Dashboard Sales Top Agents')
                function drawTopSalesAgents() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.agents')');
                    data.addColumn('number', '@lang('labels.sales')');
                    data.addRows(@json($topSalesAgents));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-top-sales-agents'));
                    chart.draw(data, pieChartOptions);
                }
            @endcan

            @can('View Dashboard Sales Top Countries')
                function drawTopSalesCountries() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.countries')');
                    data.addColumn('number', '@lang('labels.sales')');
                    data.addRows(@json($topSalesCountries));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-top-sales-countries'));
                    chart.draw(data, pieChartOptions);
                }
            @endcan

            @can('View Dashboard Viewings Top Agents')
                function drawTopViewingsAgents() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.agents')');
                    data.addColumn('number', '@lang('text.viewings')');
                    data.addRows(@json($topViewingsAgents));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-top-viewings-agents'));
                    chart.draw(data, pieChartOptions);
                }
            @endcan

            @can('View Dashboard Viewings By Date')
                function drawViewingsByDay() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.days')');
                    data.addColumn('number', '@lang('text.viewings')');
                    data.addRows(@json($viewingsByDay));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-viewings-by-day'));
                    chart.draw(data, pieChartOptions);
                }
            @endcan

            @can('View Dashboard Viewings Top Countries')
                function drawTopViewingsCountries() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.countries')');
                    data.addColumn('number', '@lang('text.viewings')');
                    data.addRows(@json($topViewingsCountries));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-top-viewings-countries'));
                    chart.draw(data, pieChartOptions);
                }
            @endcan

            @can('View Dashboard Leads Top Agents')
                function drawTopLeadsAgents() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.agents')');
                    data.addColumn('number', '@lang('labels.leads')');
                    data.addRows(@json($topLeadsAgents));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-top-leads-agents'));
                    chart.draw(data, pieChartOptions);
                }
            @endcan

            google.charts.load('current', {
                'packages': [
                    'corechart',
                ],
            });

            @can('View Dashboard Sales')google.charts.setOnLoadCallback(drawUnitSales);@endcan
            @can('View Dashboard Revenue')google.charts.setOnLoadCallback(drawSalesRevenue);@endcan
            @can('View Dashboard Revenue Received')google.charts.setOnLoadCallback(drawSalesRevenueReceived);@endcan
            @can('View Dashboard Outstanding Revenue')google.charts.setOnLoadCallback(drawSalesOutstandingRevenue);@endcan
            @can('View Dashboard Discount')google.charts.setOnLoadCallback(drawDiscount);@endcan
            @can('View Dashboard Commission')google.charts.setOnLoadCallback(drawCommission);@endcan
            @can('View Dashboard Conversion Rate')google.charts.setOnLoadCallback(drawConversion);@endcan
            @can('View Dashboard Apartments Count')google.charts.setOnLoadCallback(drawApartmentsCount);@endcan
            @can('View Dashboard Apartments Status')google.charts.setOnLoadCallback(drawApartmentsStatus);@endcan
            @can('View Dashboard Apartments Prices')google.charts.setOnLoadCallback(drawApartmentsPrices);@endcan
            @can('View Dashboard Clients Count')google.charts.setOnLoadCallback(drawClientsCount);@endcan
            @can('View Dashboard Clients Country')google.charts.setOnLoadCallback(drawClientsCountry);@endcan
            @can('View Dashboard Clients Status')google.charts.setOnLoadCallback(drawClientsStatus);@endcan
            @can('View Dashboard Clients Source')google.charts.setOnLoadCallback(drawClientsSource);@endcan
            @can('View Dashboard Agents Count')google.charts.setOnLoadCallback(drawAgentsCount);@endcan
            @can('View Dashboard Agents Country')google.charts.setOnLoadCallback(drawAgentsCountry);@endcan
            @can('View Dashboard Agents Type')google.charts.setOnLoadCallback(drawAgents);@endcan
            @can('View Dashboard Viewings')google.charts.setOnLoadCallback(drawViewings);@endcan
            @can('View Dashboard Sales Top Agents')google.charts.setOnLoadCallback(drawTopSalesAgents);@endcan
            @can('View Dashboard Sales Top Countries')google.charts.setOnLoadCallback(drawTopSalesCountries);@endcan
            @can('View Dashboard Viewings Top Agents')google.charts.setOnLoadCallback(drawTopViewingsAgents);@endcan
            @can('View Dashboard Viewings By Date')google.charts.setOnLoadCallback(drawViewingsByDay);@endcan
            @can('View Dashboard Viewings Top Countries')google.charts.setOnLoadCallback(drawTopViewingsCountries);@endcan
            @can('View Dashboard Leads Top Agents')google.charts.setOnLoadCallback(drawTopLeadsAgents);@endcan
        });
    </script>
@endpush

@endif
