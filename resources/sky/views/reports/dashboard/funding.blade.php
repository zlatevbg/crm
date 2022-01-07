@extends('layouts.main')

@if (session()->has('project'))

@section('content')
   <div class="mt-3">
        <h1 class="h3 report-heading">@lang('text.fundingSummary')</h1>
        <section class="card-deck">
            @can('View Dashboard Investors')
               <article class="card text-center col-chart">
                    <div class="card-header">@lang('labels.investorClubMember')</div>
                    <div class="card-body p-0 m-auto">
                        <div id="chart-investors" class="report-chart"></div>
                    </div>
                </article>
            @endcan
        </section>
        <h1 class="h3 report-heading">@lang('text.topValues')</h1>
        <section class="card-deck">
            @can('View Dashboard Investors Country')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.investorsCountry')</div>
                    <div class="card-body">
                        <div id="chart-investors-country" class="report-chart"></div>
                    </div>
                </article>
            @endcan

            @can('View Dashboard Investors Source')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.investorsSource')</div>
                    <div class="card-body">
                        <div id="chart-investors-source" class="report-chart"></div>
                    </div>
                </article>
            @endcan

            @can('View Dashboard Investors Category')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.investorsCategory')</div>
                    <div class="card-body">
                        <div id="chart-investors-category" class="report-chart"></div>
                    </div>
                </article>
            @endcan

            @can('View Dashboard Investors Fund Size')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.investorsFundSize')</div>
                    <div class="card-body">
                        <div id="chart-investors-fund-size" class="report-chart"></div>
                    </div>
                </article>
            @endcan

            @can('View Dashboard Investors Investment Range')
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.investorsInvestmentRange')</div>
                    <div class="card-body">
                        <div id="chart-investors-investment-range" class="report-chart"></div>
                    </div>
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

            @can('View Dashboard Investors')
                function drawInvestors() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.investors')');
                    data.addColumn('number', '@lang('labels.total')');
                    data.addRows([['', {{ $investors }}]]);

                    var chart = new google.visualization.PieChart(document.getElementById('chart-investors'));
                    chart.draw(data, pieSingleChartOptions);
                }
            @endcan

            @can('View Dashboard Investors Country')
                function drawInvestorsCountry() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.country')');
                    data.addColumn('number', '@lang('labels.investors')');
                    data.addRows(@json($investorsCountry));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-investors-country'));
                    chart.draw(data, pieChartOptions);
                }
            @endcan

            @can('View Dashboard Investors Source')
                function drawInvestorsSource() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.source')');
                    data.addColumn('number', '@lang('labels.investors')');
                    data.addRows(@json($investorsSource));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-investors-source'));
                    chart.draw(data, pieChartOptions);
                }
            @endcan

            @can('View Dashboard Investors Category')
                function drawInvestorsCategory() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.category')');
                    data.addColumn('number', '@lang('labels.investors')');
                    data.addRows(@json($investorsCategory));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-investors-category'));
                    chart.draw(data, pieChartOptions);
                }
            @endcan

            @can('View Dashboard Investors Fund Size')
                function drawInvestorsFundSize() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.fundSize')');
                    data.addColumn('number', '@lang('labels.investors')');
                    data.addRows(@json($investorsFundSize));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-investors-fund-size'));
                    chart.draw(data, pieChartOptions);
                }
            @endcan

            @can('View Dashboard Investors Investment Range')
                function drawInvestorsInvestmentRange() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.investmentRange')');
                    data.addColumn('number', '@lang('labels.investors')');
                    data.addRows(@json($investorsInvestmentRange));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-investors-investment-range'));
                    chart.draw(data, pieChartOptions);
                }
            @endcan

            google.charts.load('current', {
                'packages': [
                    'corechart',
                ],
            });

            @can('View Dashboard Investors')google.charts.setOnLoadCallback(drawInvestors);@endcan
            @can('View Dashboard Investors Country')google.charts.setOnLoadCallback(drawInvestorsCountry);@endcan
            @can('View Dashboard Investors Source')google.charts.setOnLoadCallback(drawInvestorsSource);@endcan
            @can('View Dashboard Investors Category')google.charts.setOnLoadCallback(drawInvestorsCategory);@endcan
            @can('View Dashboard Investors Category')google.charts.setOnLoadCallback(drawInvestorsFundSize);@endcan
            @can('View Dashboard Investors Category')google.charts.setOnLoadCallback(drawInvestorsInvestmentRange);@endcan
        });
    </script>
@endpush

@endif
