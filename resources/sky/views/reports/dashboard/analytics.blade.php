@extends('layouts.main')

@section('content')
    <div class="datatable-buttons text-center mt-3">
        <button id="button-google-analytics" data-target=".modal" data-toggle="modal" data-action="/view-google-analytics" class="btn btn-lg btn-info fa-left" data-method="get"><i class="fas fa-chart-bar"></i>@lang('buttons.googleAnalytics')</button>
        <button id="button-google-ads" data-target=".modal" data-toggle="modal" data-action="/view-google-ads" class="btn btn-lg btn-info fa-left" data-method="get"><i class="fas fa-chart-bar"></i>@lang('buttons.googleAds')</button>
        <button id="button-google-search-console" data-target=".modal" data-toggle="modal" data-action="/view-google-search-console" class="btn btn-lg btn-info fa-left" data-method="get"><i class="fas fa-chart-bar"></i>@lang('buttons.googleSearchConsole')</button>
        <button id="button-youtube" data-target=".modal" data-toggle="modal" data-action="/view-youtube" class="btn btn-lg btn-info fa-left" data-method="get"><i class="fas fa-chart-bar"></i>@lang('buttons.youtube')</button>
        <a target="_blank" class="btn btn-info btn-analytics fa-left" href="https://mespileuropeanventures-my.sharepoint.com/:f:/g/personal/mitko_mespil_ie/Ekvoio8RpghAlHn_AhDo1CcBu4N52jkH1733AEFfyCAgMg?e=AhH7zQ"><i class="fas fa-chart-bar"></i>@lang('buttons.socialMedia')</a>
    </div>
    <div class="mt-3">
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
        <section class="card-deck">
           <article class="card text-center col-chart">
                <div class="card-header">@lang('text.newVsReturning')</div>
                <div class="card-body m-auto">
                    <div id="chart-user-type" class="report-chart"></div>
                </div>
                <div class="card-footer">@lang('text.numberOfUsers')</div>
            </article>
            @if ($genders)
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.gender')</div>
                    <div class="card-body m-auto">
                        <div id="chart-gender" class="report-chart"></div>
                    </div>
                    <div class="card-footer">@lang('text.percentOfUsers', ['percent' => $genders['percentOfTotal']])</div>
                </article>
            @endif
            @if ($ages)
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.age')</div>
                    <div class="card-body m-auto">
                        <div id="chart-age" class="report-chart"></div>
                    </div>
                    <div class="card-footer">@lang('text.percentOfUsers', ['percent' => $ages['percentOfTotal']])</div>
                </article>
            @endif
            @if ($countries)
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.country')</div>
                    <div class="card-body m-auto">
                        <div id="chart-country" class="report-chart"></div>
                    </div>
                    <div class="card-footer">@lang('text.countCountries', ['count' => $countries['count']])</div>
                </article>
            @endif
        </section>
        <section class="card-deck">
           @if ($socialNetworks)
               <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.socialNetwork')</div>
                    <div class="card-body m-auto">
                        <div id="chart-social-network" class="report-chart"></div>
                    </div>
                    <div class="card-footer">@lang('text.percentOfSessions', ['percent' => $socialNetworks['percentOfTotal']])</div>
                </article>
            @endif
           <article class="card text-center col-chart">
                <div class="card-header">@lang('text.channels')</div>
                <div class="card-body m-auto">
                    <div id="chart-channels" class="report-chart"></div>
                </div>
                <div class="card-footer">@lang('text.numberOfSessions')</div>
            </article>
            @if ($keywordsOrganic)
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.keywordsOrganic')</div>
                    <div class="card-body m-auto">
                        <div id="chart-keywords-organic" class="report-chart"></div>
                    </div>
                    <div class="card-footer">@lang('text.percentOfSessions', ['percent' => $keywordsOrganic['percentOfTotal']])</div>
                </article>
            @endif
            @if ($keywords)
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.keywordsAll')</div>
                    <div class="card-body m-auto">
                        <div id="chart-keywords" class="report-chart"></div>
                    </div>
                    <div class="card-footer">@lang('text.percentOfSessions', ['percent' => $keywords['percentOfTotal']])</div>
                </article>
            @endif
        </section>
        <section class="card-deck">
           @if ($referrals)
               <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.referrals')</div>
                    <div class="card-body m-auto">
                        <div id="chart-referrals" class="report-chart"></div>
                    </div>
                    <div class="card-footer">@lang('text.percentOfUsers', ['percent' => $referrals['percentOfTotal']])</div>
                </article>
            @endif
            @if ($searchEnginesOrganic)
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.searchEnginesOrganic')</div>
                    <div class="card-body m-auto">
                        <div id="chart-search-engines-organic" class="report-chart"></div>
                    </div>
                    <div class="card-footer">@lang('text.percentOfUsers', ['percent' => $searchEnginesOrganic['percentOfTotal']])</div>
                </article>
            @endif
            @if ($searchEngines)
                <article class="card text-center col-chart">
                    <div class="card-header">@lang('text.searchEnginesAll')</div>
                    <div class="card-body m-auto">
                        <div id="chart-search-engines" class="report-chart"></div>
                    </div>
                    <div class="card-footer">@lang('text.percentOfUsers', ['percent' => $searchEngines['percentOfTotal']])</div>
                </article>
            @endif
            <article class="card text-center col-chart">
                <div class="card-header">@lang('text.pagePath')</div>
                <div class="card-body m-auto">
                    <div id="chart-page-path" class="report-chart"></div>
                </div>
                <div class="card-footer">@lang('text.numberOfPageviews')</div>
            </article>
        </section>
        <section class="card-deck">
           <article class="card text-center col-chart">
                <div class="card-header">@lang('text.landingPagePath')</div>
                <div class="card-body m-auto">
                    <div id="chart-landing-page-path" class="report-chart"></div>
                </div>
                <div class="card-footer">@lang('text.numberOfPageviews')</div>
            </article>
            <article class="card text-center col-chart">
                <div class="card-header">@lang('text.deviceCategory')</div>
                <div class="card-body m-auto">
                    <div id="chart-device-category" class="report-chart"></div>
                </div>
                <div class="card-footer">@lang('text.numberOfUsers')</div>
            </article>
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
                'is3D': true,
                'fontSize': 16,
                'legend': {
                    'position': 'bottom',
                },
                'pieSliceText': 'value',
                'pieResidueSliceLabel': '@lang('text.other')',
                'chartArea': {
                    'width': '100%',
                    'height': '80%',
                },
            };

            var columnChartOptions = {
                'fontSize': 16,
                'legend': {
                    'position': 'none',
                },
                'chartArea': {
                    'width': '80%',
                    'height': '80%',
                },
                'focusTarget': 'category',
            };

            @if ($userTypes)
                function drawUserType() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.user')');
                    data.addColumn('number', '@lang('text.total')');
                    data.addColumn({type: 'string', role: 'tooltip'});
                    data.addRows(@json($userTypes['data']));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-user-type'));
                    var pieChartOptionsLocal = Object.assign({}, pieChartOptions);
                    pieChartOptionsLocal.pieSliceText = 'percentage';
                    chart.draw(data, pieChartOptionsLocal);
                }
            @endif

            @if ($genders)
                function drawGender() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.gender')');
                    data.addColumn('number', '@lang('text.total')');
                    data.addColumn({type: 'string', role: 'tooltip'});
                    data.addRows(@json($genders['data']));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-gender'));
                    var pieChartOptionsLocal = Object.assign({}, pieChartOptions);
                    pieChartOptionsLocal.pieSliceText = 'percentage';
                    chart.draw(data, pieChartOptionsLocal);
                }
            @endif

            @if ($ages)
                function drawAge() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.age')');
                    data.addColumn('number', '@lang('labels.users')');
                    data.addColumn({type: 'number', role: 'annotation'});
                    data.addColumn({type: 'string', role: 'tooltip'});
                    data.addRows(@json($ages['data']));

                    var chart = new google.visualization.ColumnChart(document.getElementById('chart-age'));
                    var columnChartOptionsLocal = Object.assign({}, columnChartOptions);
                    columnChartOptionsLocal.annotations = {
                        'textStyle': {
                            'fontSize': 12,
                        },
                    };
                    columnChartOptionsLocal.vAxis = {
                        'format': 'percent',
                    };
                    columnChartOptionsLocal.tooltip = {
                        'showColorCode': false,
                    };
                    chart.draw(data, columnChartOptionsLocal);
                }
            @endif

            @if ($countries)
                function drawCountry() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('labels.country')');
                    data.addColumn('number', '@lang('labels.sessions')');
                    data.addColumn({type: 'string', role: 'tooltip'});
                    data.addRows(@json($countries['data']));

                    var chart = new google.visualization.ColumnChart(document.getElementById('chart-country'));
                    var columnChartOptionsLocal = Object.assign({}, columnChartOptions);
                    columnChartOptionsLocal.orientation = 'vertical';
                    columnChartOptionsLocal.hAxis = {
                        'format': 'percent',
                    };
                    columnChartOptionsLocal.vAxis = {
                        'textPosition': 'in',
                        'textStyle': {
                            'color': 'white',
                            'auraColor': 'gray',
                            'auraWidth': 2,
                        },
                    };
                    columnChartOptionsLocal.tooltip = {
                        'showColorCode': false,
                    };
                    chart.draw(data, columnChartOptionsLocal);
                }
            @endif

            @if ($socialNetworks)
                function drawSocialNetwork() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('text.socialNetwork')');
                    data.addColumn('number', '@lang('labels.sessions')');
                    data.addColumn({type: 'string', role: 'tooltip'});
                    data.addRows(@json($socialNetworks['data']));

                    var chart = new google.visualization.ColumnChart(document.getElementById('chart-social-network'));
                    var columnChartOptionsLocal = Object.assign({}, columnChartOptions);
                    columnChartOptionsLocal.orientation = 'vertical';
                    columnChartOptionsLocal.hAxis = {
                        'format': 'percent',
                    };
                    columnChartOptionsLocal.vAxis = {
                        'textPosition': 'in',
                        'textStyle': {
                            'color': 'white',
                            'auraColor': 'gray',
                            'auraWidth': 2,
                        },
                    };
                    columnChartOptionsLocal.tooltip = {
                        'showColorCode': false,
                    };
                    chart.draw(data, columnChartOptionsLocal);
                }
            @endif

            @if ($channels)
                function drawChannels() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('text.channels')');
                    data.addColumn('number', '@lang('labels.sessions')');
                    data.addColumn({type: 'string', role: 'tooltip'});
                    data.addRows(@json($channels['data']));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-channels'));
                    var pieChartOptionsLocal = Object.assign({}, pieChartOptions);
                    pieChartOptionsLocal.fontSize = 12;
                    pieChartOptionsLocal.pieSliceText = 'percentage';
                    pieChartOptionsLocal.legend = {
                        'position': 'right',
                        'alignment': 'center',
                    };
                    chart.draw(data, pieChartOptionsLocal);
                }
            @endif

            @if ($keywordsOrganic)
                function drawKeywordsOrganic() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('text.keywordsOrganic')');
                    data.addColumn('number', '@lang('labels.sessions')');
                    data.addColumn({type: 'string', role: 'tooltip'});
                    data.addRows(@json($keywordsOrganic['data']));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-keywords-organic'));
                    var pieChartOptionsLocal = Object.assign({}, pieChartOptions);
                    pieChartOptionsLocal.fontSize = 12;
                    pieChartOptionsLocal.legend = {
                        'position': 'right',
                        'alignment': 'center',
                    };
                    chart.draw(data, pieChartOptionsLocal);
                }
            @endif

            @if ($keywords)
                function drawKeywords() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('text.keywordsAll')');
                    data.addColumn('number', '@lang('labels.sessions')');
                    data.addColumn({type: 'string', role: 'tooltip'});
                    data.addRows(@json($keywords['data']));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-keywords'));
                    var pieChartOptionsLocal = Object.assign({}, pieChartOptions);
                    pieChartOptionsLocal.fontSize = 12;
                    pieChartOptionsLocal.legend = {
                        'position': 'right',
                        'alignment': 'center',
                    };
                    chart.draw(data, pieChartOptionsLocal);
                }
            @endif

            @if ($referrals)
                function drawReferrals() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('text.referral')');
                    data.addColumn('number', '@lang('labels.users')');
                    data.addColumn({type: 'string', role: 'tooltip'});
                    data.addRows(@json($referrals['data']));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-referrals'));
                    var pieChartOptionsLocal = Object.assign({}, pieChartOptions);
                    pieChartOptionsLocal.fontSize = 12;
                    pieChartOptionsLocal.legend = {
                        'position': 'right',
                        'alignment': 'center',
                    };
                    chart.draw(data, pieChartOptionsLocal);
                }
            @endif

            @if ($searchEnginesOrganic)
                function drawSearchEnginesOrganic() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('text.searchEnginesOrganic')');
                    data.addColumn('number', '@lang('labels.users')');
                    data.addColumn({type: 'string', role: 'tooltip'});
                    data.addRows(@json($searchEnginesOrganic['data']));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-search-engines-organic'));
                    var pieChartOptionsLocal = Object.assign({}, pieChartOptions);
                    pieChartOptionsLocal.fontSize = 12;
                    pieChartOptionsLocal.legend = {
                        'position': 'right',
                        'alignment': 'center',
                    };
                    chart.draw(data, pieChartOptionsLocal);
                }
            @endif

            @if ($searchEngines)
                function drawSearchEngines() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('text.searchEnginesAll')');
                    data.addColumn('number', '@lang('labels.users')');
                    data.addColumn({type: 'string', role: 'tooltip'});
                    data.addRows(@json($searchEngines['data']));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-search-engines'));
                    var pieChartOptionsLocal = Object.assign({}, pieChartOptions);
                    pieChartOptionsLocal.fontSize = 12;
                    pieChartOptionsLocal.legend = {
                        'position': 'right',
                        'alignment': 'center',
                    };
                    chart.draw(data, pieChartOptionsLocal);
                }
            @endif

            @if ($pagePath)
                function drawPagePath() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('text.pagePath')');
                    data.addColumn('number', '@lang('labels.pageviews')');
                    data.addColumn({type: 'string', role: 'tooltip'});
                    data.addRows(@json($pagePath['data']));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-page-path'));
                    var pieChartOptionsLocal = Object.assign({}, pieChartOptions);
                    pieChartOptionsLocal.fontSize = 12;
                    pieChartOptionsLocal.legend = {
                        'position': 'right',
                        'alignment': 'center',
                    };
                    chart.draw(data, pieChartOptionsLocal);
                }
            @endif

            @if ($landingPagePath)
                function drawLandingPagePath() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('text.landingPagePath')');
                    data.addColumn('number', '@lang('labels.pageviews')');
                    data.addColumn({type: 'string', role: 'tooltip'});
                    data.addRows(@json($landingPagePath['data']));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-landing-page-path'));
                    var pieChartOptionsLocal = Object.assign({}, pieChartOptions);
                    pieChartOptionsLocal.fontSize = 12;
                    pieChartOptionsLocal.legend = {
                        'position': 'right',
                        'alignment': 'center',
                    };
                    chart.draw(data, pieChartOptionsLocal);
                }
            @endif

            @if ($deviceCategory)
                function drawDeviceCategory() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', '@lang('text.deviceCategory')');
                    data.addColumn('number', '@lang('labels.users')');
                    data.addColumn({type: 'string', role: 'tooltip'});
                    data.addRows(@json($deviceCategory['data']));

                    var chart = new google.visualization.PieChart(document.getElementById('chart-device-category'));
                    var pieChartOptionsLocal = Object.assign({}, pieChartOptions);
                    pieChartOptionsLocal.fontSize = 12;
                    pieChartOptionsLocal.pieSliceText = 'percentage';
                    pieChartOptionsLocal.legend = {
                        'position': 'right',
                        'alignment': 'center',
                    };
                    chart.draw(data, pieChartOptionsLocal);
                }
            @endif

            google.charts.load('current', {
                'packages': [
                    'corechart',
                ],
            });

            @if ($userTypes)
                google.charts.setOnLoadCallback(drawUserType);
            @endif
            @if ($genders)
                google.charts.setOnLoadCallback(drawGender);
            @endif
            @if ($ages)
                google.charts.setOnLoadCallback(drawAge);
            @endif
            @if ($countries)
                google.charts.setOnLoadCallback(drawCountry);
            @endif
            @if ($socialNetworks)
                google.charts.setOnLoadCallback(drawSocialNetwork);
            @endif
            @if ($channels)
                google.charts.setOnLoadCallback(drawChannels);
            @endif
            @if ($keywordsOrganic)
                google.charts.setOnLoadCallback(drawKeywordsOrganic);
            @endif
            @if ($keywords)
                google.charts.setOnLoadCallback(drawKeywords);
            @endif
            @if ($referrals)
                google.charts.setOnLoadCallback(drawReferrals);
            @endif
            @if ($searchEnginesOrganic)
                google.charts.setOnLoadCallback(drawSearchEnginesOrganic);
            @endif
            @if ($searchEngines)
                google.charts.setOnLoadCallback(drawSearchEngines);
            @endif
            @if ($pagePath)
                google.charts.setOnLoadCallback(drawPagePath);
            @endif
            @if ($landingPagePath)
                google.charts.setOnLoadCallback(drawLandingPagePath);
            @endif
            @if ($deviceCategory)
                google.charts.setOnLoadCallback(drawDeviceCategory);
            @endif
        });
    </script>
@endpush
