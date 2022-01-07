<?php

/*
SELECT statuses.id, statuses.conversion, statuses.name, COUNT(apartments.id) AS total, SUM(apartments.price) AS apartments_price, SUM(sales.price + sales.furniture) AS sales_price, SUM((apartments.price - sales.price) + (COALESCE(furniture.price, "0") - sales.furniture)) AS discount, SUM(sales.commission) AS commission, GROUP_CONCAT(COALESCE(agents.company, "XXX") SEPARATOR ", ") AS agents, GROUP_CONCAT(apartments.id SEPARATOR ", ") AS apartments, GROUP_CONCAT(COALESCE(countries.name, "XXX") SEPARATOR ", ") AS countries
FROM statuses
LEFT JOIN apartment_status ON apartment_status.status_id = statuses.id
LEFT JOIN apartments ON apartments.id = apartment_status.apartment_id
LEFT JOIN furniture ON furniture.id = apartments.furniture_id
LEFT JOIN sales ON sales.apartment_id = apartments.id AND sales.deleted_at IS NULL
LEFT JOIN clients ON clients.id = sales.client_id
LEFT JOIN countries ON countries.id = clients.country_id
LEFT JOIN agents ON agents.id = clients.agent_id AND agents.deleted_at IS NULL
WHERE apartments.project_id = '1' AND apartments.deleted_at IS NULL AND statuses.parent = '1' AND statuses.deleted_at IS NULL AND apartments.reports = '1' AND apartment_status.id = (
    SELECT apartment_status.id
    FROM apartment_status
    WHERE DATE(`apartment_status`.`created_at`) >= '2018-01-01' AND DATE(`apartment_status`.`created_at`) <= '2018-09-30' AND apartment_status.apartment_id = apartments.id
    ORDER BY apartment_status.created_at DESC
    LIMIT 1)
GROUP BY statuses.id
ORDER BY statuses.order

SELECT COUNT(DISTINCT viewings.id) AS total, countries.name AS country, GROUP_CONCAT(viewings.id SEPARATOR ", ") AS viewings
FROM viewings
LEFT JOIN client_viewing ON client_viewing.viewing_id = viewings.id
LEFT JOIN clients ON clients.id = client_viewing.client_id AND clients.deleted_at IS NULL
LEFT JOIN countries ON countries.id = clients.country_id
WHERE viewings.project_id = '1' AND DATE(`viewings`.`viewed_at`) >= '2018-01-01' AND DATE(`viewings`.`viewed_at`) <= '2018-03-31' AND viewings.deleted_at IS NULL
GROUP BY countries.id
ORDER BY total DESC, country

SELECT COUNT(DISTINCT sales.id) AS total, countries.name AS country
FROM sales
LEFT JOIN apartments ON apartments.id = sales.apartment_id AND apartments.deleted_at IS NULL
LEFT JOIN apartment_status ON apartment_status.apartment_id = apartments.id
LEFT JOIN clients ON clients.id = sales.client_id AND clients.deleted_at IS NULL
LEFT JOIN countries ON countries.id = clients.country_id
WHERE sales.project_id = '1' AND apartments.reports = '1' AND sales.deleted_at IS NULL
GROUP BY countries.id
ORDER BY total DESC

SELECT COUNT(DISTINCT viewings.id) AS total, statuses.name
FROM viewings
LEFT JOIN apartment_viewing ON apartment_viewing.viewing_id = viewings.id AND apartment_viewing.deleted_at IS NULL
LEFT JOIN statuses ON statuses.id = viewings.status_id
WHERE viewings.project_id = '1' AND viewings.deleted_at IS NULL AND apartment_viewing.apartment_id IN (1,2,3,4,5,6,7,10,12,17,21,22,31,32,46,47,49,51,52,53,54,55,43)
GROUP BY statuses.id
ORDER BY total DESC

SELECT COUNT(DISTINCT viewings.id) AS total, agents.company AS agent
FROM viewings
LEFT JOIN client_viewing ON client_viewing.viewing_id = viewings.id
LEFT JOIN clients ON clients.id = client_viewing.client_id AND clients.deleted_at IS NULL
LEFT JOIN agents ON agents.id = clients.agent_id
WHERE viewings.project_id = '1' AND viewings.deleted_at IS NULL
GROUP BY agents.id
ORDER BY total DESC, agent

SELECT statuses.name, COUNT(clients.id) AS total
FROM statuses
LEFT JOIN client_status ON client_status.status_id = statuses.id
LEFT JOIN clients ON clients.id = client_status.client_id
WHERE clients.deleted_at IS NULL AND statuses.parent = '2' AND statuses.deleted_at IS NULL AND client_status.id = (
    SELECT client_status.id
    FROM client_status
    WHERE DATE(`client_status`.`created_at`) >= '2018-01-01' AND DATE(`client_status`.`created_at`) <= '2018-09-30' AND client_status.client_id = clients.id
    ORDER BY client_status.created_at DESC
    LIMIT 1)
GROUP BY statuses.id
ORDER BY statuses.order
*/

namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Target;
use App\Models\Status;
use App\Models\User;
use App\Models\Client;
use App\Models\Investor;
use App\Models\Agent;
use App\Models\Apartment;
use App\Models\Project;
use App\Models\FundSize;
use App\Models\InvestmentRange;
use App\Models\Category;
use App\Models\Viewing;
use App\Models\Payment;
use App\Models\Bed;
use App\Models\Task;
use App\Models\Country;
use App\Models\Source;
use App\Models\Department;
use App\Models\Website;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\Datatable;
use App\Services\Api;
use App\Services\Helper;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Google_Client;
use Google_Service_Analytics;
use Google_Service_AnalyticsReporting;
use Google_Service_AnalyticsReporting_DateRange;
use Google_Service_AnalyticsReporting_Metric;
use Google_Service_AnalyticsReporting_Dimension;
use Google_Service_AnalyticsReporting_DimensionFilter;
use Google_Service_AnalyticsReporting_DimensionFilterClause;
use Google_Service_AnalyticsReporting_ReportRequest;
use Google_Service_AnalyticsReporting_GetReportsRequest;
use Google_Service_AnalyticsReporting_OrderBy;

class ReportController extends Controller
{
    public $limit = 10;

    public function __invoke(Request $request)
    {
        $slug = $request->segment(2);

        $api = new Api('reports');

        $view = null;
        if (!$slug || $slug === 'dashboard') {
            if ($slug && !$request->segment(3)) {
                abort(404);
            }

            $view = $request->segment(3);

            $currentYear = date('Y');
            $currentMonth = date('n');
            $currentQuarter = ceil($currentMonth / 3);
            $quarter = null;
            $from = null;
            $to = null;
            $selected = 'all';

            if ($request->has('from') || $request->has('to')) {
                $selected = 'custom';
                if ($request->has('from')) {
                    $from = $request->input('from');
                }

                if ($request->has('to')) {
                    $to = $request->input('to');
                }
            } elseif ($request->has('y')) {
                $from = '01.01.' . $request->input('y');
                $to = '31.12.' . $request->input('y');
                $selected = 'year';
            } elseif ($request->has('q')) {
                $quarter = $request->input('q');
                if ($quarter == 1) {
                    $from = '01.01.' . $currentYear;
                    $to = '31.03.' . $currentYear;
                } elseif ($quarter == 2) {
                    $from = '01.04.' . $currentYear;
                    $to = '30.06.' . $currentYear;
                } elseif ($quarter == 3) {
                    $from = '01.07.' . $currentYear;
                    $to = '30.09.' . $currentYear;
                } elseif ($quarter == 4) {
                    $from = '01.10.' . $currentYear;
                    $to = '31.12.' . $currentYear;
                }

                $selected = 'quarter';
            }

            if ($view) {
                $api->datatables['datatable-report']['buttons'] = [
                    [
                        'view' => 'report-filters',
                    ],
                    'all' => [
                        'url' => secure_url('reports' . ($slug ? '/' . $slug : '') . ($view ? '/' . $view : '')),
                        'class' => 'btn-primary js-link' . ($selected == 'all' ? ' active' : ''),
                        'icon' => '',
                        'method' => 'get',
                        'name' => ($view == 'analytics' ? trans('buttons.lastMonth') : trans('buttons.allTime')),
                    ],
                    $currentYear => [
                        'url' => secure_url('reports' . ($slug ? '/' . $slug : '') . ($view ? '/' . $view : '')) . '?y=' . $currentYear,
                        'class' => 'btn-primary js-link' . ($selected == 'year' ? ' active' : ''),
                        'icon' => '',
                        'method' => 'get',
                        'name' => $currentYear,
                    ],
                    'Q1' => [
                        'url' => secure_url('reports' . ($slug ? '/' . $slug : '') . ($view ? '/' . $view : '')) . '?q=1',
                        'class' => 'btn-primary js-link' . ($quarter == 1 ? ' active' : '') . (1 > $currentQuarter ? ' disabled' : ''),
                        'icon' => '',
                        'method' => 'get',
                        'name' => trans('buttons.Q1'),
                    ],
                    'Q2' => [
                        'url' => secure_url('reports' . ($slug ? '/' . $slug : '') . ($view ? '/' . $view : '')) . '?q=2',
                        'class' => 'btn-primary js-link' . ($quarter == 2 ? ' active' : '') . (2 > $currentQuarter ? ' disabled' : ''),
                        'icon' => '',
                        'method' => 'get',
                        'name' => trans('buttons.Q2'),
                    ],
                    'Q3' => [
                        'url' => secure_url('reports' . ($slug ? '/' . $slug : '') . ($view ? '/' . $view : '')) . '?q=3',
                        'class' => 'btn-primary js-link' . ($quarter == 3 ? ' active' : '') . (3 > $currentQuarter ? ' disabled' : ''),
                        'icon' => '',
                        'method' => 'get',
                        'name' => trans('buttons.Q3'),
                    ],
                    'Q4' => [
                        'url' => secure_url('reports' . ($slug ? '/' . $slug : '') . ($view ? '/' . $view : '')) . '?q=4',
                        'class' => 'btn-primary js-link' . ($quarter == 4 ? ' active' : '') . (4 > $currentQuarter ? ' disabled' : ''),
                        'icon' => '',
                        'method' => 'get',
                        'name' => trans('buttons.Q4'),
                    ],
                    'custom' => [
                        'view' => 'button-dates',
                    ],
                ];
            }

            $from = $from ? Carbon::parse($from) : null;
            $to = $to ? Carbon::parse($to) : null;
            $parameters = [];
            if (!$slug) {
                $slug = 'dashboard';
            }

            if ($view == 'sales') {
                array_push($api->breadcrumbs, $api->breadcrumb($slug, trans('labels.report-' . $slug . '-' . $view)));

                $apartments = $this->dashboardApartments($from, $to);
                $salesIds = explode(',', implode(',', array_column($apartments, 'sales')));

                $apartmentsCount = null;
                if (Auth::user()->can('View Dashboard Apartments Count')) {
                    $apartmentsCount = array_sum(array_column($apartments, 'total'));
                }

                $apartmentsStatus = null;
                if (Auth::user()->can('View Dashboard Apartments Status')) {
                    $apartmentsStatus = [0 => []];
                    array_push($apartmentsStatus[0], null);
                    foreach ($apartments as $apartment) {
                        array_push($apartmentsStatus[0], $apartment['total']);
                        array_push($apartmentsStatus[0], $apartment['total']); // annotation
                    }
                }

                $apartmentsPrices = null;
                if (Auth::user()->can('View Dashboard Apartments Prices')) {
                    $apartmentsPrices = [0 => []];
                    array_push($apartmentsPrices[0], null);
                    foreach ($apartments as $apartment) {
                        array_push($apartmentsPrices[0], (int)number_format($apartment['amount'], 0, '', ''));
                        array_push($apartmentsPrices[0], (int)number_format($apartment['amount'], 0, '', '')); // annotation
                    }
                }

                $clientsStatus = $this->dashboardClientsStatus($from, $to);
                $clientsIds = explode(',', implode(',', array_column($clientsStatus, 'clients')));

                $clientsCount = null;
                if (Auth::user()->can('View Dashboard Clients Count')) {
                    $clientsCount = array_sum(array_column($clientsStatus, 'total'));
                }

                $clientsCountry = null;
                if (Auth::user()->can('View Dashboard Clients Country')) {
                    $clientsCountry = $this->dashboardClientsCountry($clientsIds);
                }

                $clientsStatusChart = null;
                if (Auth::user()->can('View Dashboard Clients Status')) {
                    $clientsStatusChart = [0 => []];
                    array_push($clientsStatusChart[0], null);
                    foreach ($clientsStatus as $client) {
                        array_push($clientsStatusChart[0], $client['total']);
                        array_push($clientsStatusChart[0], $client['total']); // annotation
                    }
                }

                $clientsSource = null;
                $clientsSourceChart = null;
                if (Auth::user()->can('View Dashboard Clients Source')) {
                    $clientsSource = Client::selectRaw('sources.name AS source, COUNT(DISTINCT clients.id) AS total')
                        ->leftJoin('sources', 'sources.id', '=', 'clients.source_id')
                        ->leftJoin('client_project', 'client_project.client_id', '=', 'clients.id')
                        ->whereIn('clients.id', $clientsIds)
                        ->where('sources.parent', 1)
                        ->where(function ($query) {
                            $query->whereIn('client_project.project_id', Helper::project())->when(!session('project'), function ($q) {
                                return $q->orWhereNull('client_project.project_id');
                            });
                        })
                        ->whereNull('clients.deleted_at')
                        ->groupBy('sources.id')
                        ->orderBy('sources.name')->get()->toArray();

                    if ($clientsSource) {
                        $clientsSourceChart = [0 => []];
                        array_push($clientsSourceChart[0], null);
                        foreach ($clientsSource as $client) {
                            array_push($clientsSourceChart[0], $client['total']);
                            array_push($clientsSourceChart[0], $client['total']); // annotation
                        }
                    }
                }

                $agents = $this->dashboardAgents($from, $to);
                $agentsIds = explode(',', implode(',', array_column($agents, 'agents')));

                $agentsCount = null;
                if (Auth::user()->can('View Dashboard Agents Count')) {
                    $agentsCount = array_sum(array_column($agents, 'total'));
                }

                $agentsCountry = null;
                if (Auth::user()->can('View Dashboard Agents Country')) {
                    $agentsCountry = $this->dashboardAgentsCountry($agentsIds);
                }

                $agentsType = null;
                if (Auth::user()->can('View Dashboard Agents Type')) {
                    $agentsType = [0 => []];
                    array_push($agentsType[0], null);
                    foreach ($agents as $agent) {
                        array_push($agentsType[0], $agent['total']);
                        array_push($agentsType[0], $agent['total']); // annotation
                    }
                }

                $unitSales = null;
                $salesBreakdown = null;
                if (Auth::user()->can('View Dashboard Sales')) {
                    $unitSales = $this->dashboardUnitSales($apartments);
                    $salesBreakdown = $this->dashboardSalesBreakdown($apartments);
                }

                $revenue = null;
                if (Auth::user()->can('View Dashboard Revenue')) {
                    $revenue = $this->dashboardRevenue($apartments);
                }

                $revenueReceived = null;
                if (Auth::user()->can('View Dashboard Revenue Received')) {
                    $revenueReceived = $this->dashboardRevenueReceived($salesIds);
                }

                $outstandingRevenue = null;
                if (Auth::user()->can('View Dashboard Outstanding Revenue')) {
                    $outstandingRevenue = $this->dashboardOutstandingRevenue($revenue, $revenueReceived);
                }

                $discount = null;
                /*if (Auth::user()->can('View Dashboard Discount')) {
                    $discount = $this->dashboardDiscount($apartments);
                }*/

                $commission = null;
                /*if (Auth::user()->can('View Dashboard Commission')) {
                    $commission = $this->dashboardCommission($apartments);
                }*/

                $conversion = null;
                if (Auth::user()->can('View Dashboard Conversion Rate')) {
                    $conversion = $this->dashboardConversion($from, $to, $unitSales);
                }

                $targets = null;
                if (Auth::user()->can('View Dashboard Targets')) {
                    $targets = $this->dashboardTargets($from, $to, $salesBreakdown, $revenue);
                }

                $viewings = $this->dashboardViewings($from, $to);
                $viewingsReasons = null;
                if (Auth::user()->can('View Dashboard Viewings Reasons')) {
                    $viewingsReasons = $this->dashboardViewingsReasons($apartments, $viewings);
                }

                $topSalesAgents = null;
                if (Auth::user()->can('View Dashboard Sales Top Agents')) {
                    $topSalesAgents = $this->dashboardTopSalesAgents($apartments);
                }

                $topSalesCountries = null;
                if (Auth::user()->can('View Dashboard Sales Top Countries')) {
                    $topSalesCountries = $this->dashboardTopSalesCountries($apartments);
                }

                $topViewingsAgents = null;
                if (Auth::user()->can('View Dashboard Viewings Top Agents')) {
                    $topViewingsAgents = $this->dashboardTopViewingsAgents($from, $to);
                }

                $viewingsByDay = null;
                if (Auth::user()->can('View Dashboard Viewings By Date')) {
                    $viewingsByDay = $this->dashboardViewingsByDay($from, $to);
                }

                $topViewingsCountries = [];
                if (Auth::user()->can('View Dashboard Viewings Top Countries')) {
                    foreach ($viewings->slice(0, $this->limit) as $viewing) {
                        array_push($topViewingsCountries, [($viewing->country ?: trans('text.none')), $viewing->total]);
                    };
                }

                $topLeadsAgents = null;
                if (Auth::user()->can('View Dashboard Leads Top Agents')) {
                    $topLeadsAgents = $this->dashboardTopLeadsAgents();
                }

                $parameters = ['api', 'apartments', 'apartmentsCount', 'apartmentsStatus', 'apartmentsPrices', 'clientsCount', 'clientsCountry', 'clientsStatus', 'clientsStatusChart', 'clientsSource', 'clientsSourceChart', 'agents', 'agentsCount', 'agentsCountry', 'agentsType', 'unitSales', 'salesBreakdown', 'revenue', 'revenueReceived', 'outstandingRevenue', 'discount', 'commission', 'conversion', 'targets', 'viewings', 'viewingsReasons', 'topSalesAgents', 'topSalesCountries', 'topViewingsAgents', 'topViewingsCountries', 'viewingsByDay', 'topLeadsAgents'];
            } elseif ($view == 'funding') {
                array_push($api->breadcrumbs, $api->breadcrumb($slug, trans('labels.report-' . $slug . '-' . $view)));

                $investors = Investor::count();

                $investorsCountry = null;
                if (Auth::user()->can('View Dashboard Investors Country')) {
                    $investorsCountry = $this->dashboardInvestorsCountry();
                }

                $investorsSource = null;
                if (Auth::user()->can('View Dashboard Investors Source')) {
                    $investorsSource = $this->dashboardInvestorsSource();
                }

                $investorsCategory = null;
                if (Auth::user()->can('View Dashboard Investors Category')) {
                    $investorsCategory = $this->dashboardInvestorsCategory();
                }

                $investorsFundSize = null;
                if (Auth::user()->can('View Dashboard Investors Fund Size')) {
                    $investorsFundSize = $this->dashboardInvestorsFundSize();
                }

                $investorsInvestmentRange = null;
                if (Auth::user()->can('View Dashboard Investors Investment Range')) {
                    $investorsInvestmentRange = $this->dashboardInvestorsInvestmentRange();
                }

                $parameters = ['api', 'investors', 'investorsCountry', 'investorsSource', 'investorsCategory', 'investorsFundSize', 'investorsInvestmentRange'];
            } elseif ($view == 'analytics') {
                array_push($api->breadcrumbs, $api->breadcrumb($slug, trans('labels.report-' . $slug . '-' . $view)));

                $analytics = null;
                $values = null;
                $userTypes = null;
                $genders = null;
                $ages = null;
                $countries = null;
                $socialNetworks = null;
                $channels = null;
                $keywords = null;
                $keywordsOrganic = null;
                $referrals = null;
                $searchEngines = null;
                $searchEnginesOrganic = null;
                $pagePath = null;
                $landingPagePath = null;
                $deviceCategory = null;

                $id = 1; // mespil.ie

                if (in_array($id, (Auth::user()->can('View All Websites') ? Website::pluck('id') : Auth::user()->websites->pluck('id'))->toArray()) && Auth::user()->can('View: Analytics')) {
                    $from = $from ?: Carbon::now()->subDays(30);
                    $to = $to ?: Carbon::now()->subDays(1);
                    $website = Website::find($id);
                    if ($website) {
                        $client = new Google_Client();
                        $client->setApplicationName('Analytics - sky.mespil.ie');
                        $client->setAuthConfig(storage_path('app/google/analytics.json'));
                        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);

                        $analytics = new Google_Service_AnalyticsReporting($client);

                        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
                        $dateRange->setStartDate($from->format('Y-m-d'));
                        $dateRange->setEndDate($to->format('Y-m-d'));

                        $dateRangeHistory = new Google_Service_AnalyticsReporting_DateRange();
                        $dateRangeHistory->setStartDate($from->subDays(30)->format('Y-m-d'));
                        $dateRangeHistory->setEndDate($to->subDays(30)->format('Y-m-d'));

                        $reportGroups = [];

                        $users = new Google_Service_AnalyticsReporting_Metric();
                        $users->setExpression('ga:users');
                        $users->setAlias('users');

                        $newUsers = new Google_Service_AnalyticsReporting_Metric();
                        $newUsers->setExpression('ga:newUsers');
                        $newUsers->setAlias('newUsers');

                        $sessions = new Google_Service_AnalyticsReporting_Metric();
                        $sessions->setExpression('ga:sessions');
                        $sessions->setAlias('sessions');

                        $sessionsPerUser = new Google_Service_AnalyticsReporting_Metric();
                        $sessionsPerUser->setExpression('ga:sessionsPerUser');
                        $sessionsPerUser->setAlias('sessionsPerUser');

                        $pageviews = new Google_Service_AnalyticsReporting_Metric();
                        $pageviews->setExpression('ga:pageviews');
                        $pageviews->setAlias('pageviews');

                        $pageviewsPerSession = new Google_Service_AnalyticsReporting_Metric();
                        $pageviewsPerSession->setExpression('ga:pageviewsPerSession');
                        $pageviewsPerSession->setAlias('pageviewsPerSession');

                        $avgSessionDuration = new Google_Service_AnalyticsReporting_Metric();
                        $avgSessionDuration->setExpression('ga:avgSessionDuration');
                        $avgSessionDuration->setAlias('avgSessionDuration');
                        $avgSessionDuration->setFormattingType('TIME');

                        $bounceRate = new Google_Service_AnalyticsReporting_Metric();
                        $bounceRate->setExpression('ga:bounceRate');
                        $bounceRate->setAlias('bounceRate');

                        $userType = new Google_Service_AnalyticsReporting_Dimension();
                        $userType->setName('ga:userType');

                        $userGender = new Google_Service_AnalyticsReporting_Dimension();
                        $userGender->setName('ga:userGender');

                        $userAge = new Google_Service_AnalyticsReporting_Dimension();
                        $userAge->setName('ga:userAgeBracket');

                        $country = new Google_Service_AnalyticsReporting_Dimension();
                        $country->setName('ga:country');

                        /*$countryOrder = new Google_Service_AnalyticsReporting_OrderBy();
                        $countryOrder->setOrderType("VALUE");
                        $countryOrder->setSortOrder("ASCENDING");
                        $countryOrder->setFieldName("ga:country");*/

                        $dateRanges = [$dateRange, $dateRangeHistory];
                        $metrics = [$users, $newUsers, $sessions, $sessionsPerUser, $pageviews, $pageviewsPerSession, $avgSessionDuration, $bounceRate];
                        $dimensionType = [$userType];
                        $dimensionGender = [$userGender];
                        $dimensionAge = [$userAge];
                        $dimensionCountry = [$country];

                        $requests = [];
                        $requests[] = $this->createRequest($website->analytics, $dateRanges, $metrics);
                        $requests[] = $this->createRequest($website->analytics, $dateRanges, [$users], $dimensionType);
                        $requests[] = $this->createRequest($website->analytics, $dateRanges, [$users], $dimensionGender);
                        $requests[] = $this->createRequest($website->analytics, $dateRanges, [$users], $dimensionAge);
                        $requests[] = $this->createRequest($website->analytics, $dateRanges, [$sessions], $dimensionCountry/*, $countryOrder*/);

                        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
                        $body->setReportRequests($requests);
                        $reportGroups[$website->website] = $analytics->reports->batchGet($body);

                        $data = $this->getData($reportGroups);

                        $values = $data[$website->website][0];

                        $userTypesTotal = isset($data[$website->website][1]['users_total']) ? $data[$website->website][1]['users_total']['current'] : 0;
                        $userTypes = [
                            'total' => $userTypesTotal,
                            'data' => [],
                        ];
                        if (isset($data[$website->website][1]['users'])) {
                            foreach ($data[$website->website][1]['users'] as $key => $value) {
                                array_push($userTypes['data'], [$key, round(($value['current'] / $userTypesTotal) * 100, 2), $key . "\n" . round(($value['current'] / $userTypesTotal) * 100, 2) . '% (' . $value['current'] . ')']);
                            }
                        }

                        $gendersTotal = isset($data[$website->website][2]['users_total']) ? $data[$website->website][2]['users_total']['current'] : 0;
                        $genders = [
                            'total' => $gendersTotal,
                            'percentOfTotal' => round(($gendersTotal / $values['users']['current']) * 100, 2),
                            'data' => [],
                        ];
                        if (isset($data[$website->website][2]['users'])) {
                            foreach ($data[$website->website][2]['users'] as $key => $value) {
                                array_push($genders['data'], [$key, round(($value['current'] / $gendersTotal) * 100, 2), $key . "\n" . round(($value['current'] / $gendersTotal) * 100, 2) . '% (' . $value['current'] . ')']);
                            }
                        }

                        $agesTotal = isset($data[$website->website][3]['users_total']) ? $data[$website->website][3]['users_total']['current'] : 0;
                        $ages = [
                            'total' => $agesTotal,
                            'percentOfTotal' => round(($agesTotal / $values['users']['current']) * 100, 2),
                            'data' => [],
                        ];
                        if (isset($data[$website->website][3]['users'])) {
                            foreach ($data[$website->website][3]['users'] as $key => $value) {
                                array_push($ages['data'], [$key, round($value['current'] / $agesTotal, 2), round(($value['current'] / $agesTotal) * 100, 0), round(($value['current'] / $agesTotal) * 100, 2) . '% (' . $value['current'] . ')']);
                            }
                        }

                        $count = count($data[$website->website][4]['sessions']);
                        $data[$website->website][4]['sessions'] = collect($data[$website->website][4]['sessions'])->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
                        $countriesTotal = isset($data[$website->website][4]['sessions_total']) ? $data[$website->website][4]['sessions_total']['current'] : 0;
                        $countries = [
                            'total' => $countriesTotal,
                            'count' => $count,
                            'data' => [],
                        ];
                        if (isset($data[$website->website][4]['sessions'])) {
                            foreach ($data[$website->website][4]['sessions'] as $key => $value) {
                                array_push($countries['data'], [$key, round($value['current'] / $countriesTotal, 2), round(($value['current'] / $countriesTotal) * 100, 2) . '% (' . $value['current'] . ')']);
                            }
                        }

                        $socialNetwork = new Google_Service_AnalyticsReporting_Dimension();
                        $socialNetwork->setName('ga:socialNetwork');

                        $socialNetworkFilter = new Google_Service_AnalyticsReporting_DimensionFilter([
                            'not' => true,
                        ]);
                        $socialNetworkFilter->setDimensionName('ga:socialNetwork');
                        $socialNetworkFilter->setOperator('EXACT');
                        $socialNetworkFilter->setExpressions(['(not set)']);

                        $socialNetworkDimensionFilterClause = new Google_Service_AnalyticsReporting_DimensionFilterClause();
                        $socialNetworkDimensionFilterClause->setFilters([$socialNetworkFilter]);
                        $dimensionSocialNetwork = [$socialNetwork];

                        $channelGrouping = new Google_Service_AnalyticsReporting_Dimension();
                        $channelGrouping->setName('ga:channelGrouping');
                        $dimensionChannelGrouping = [$channelGrouping];

                        $keyword = new Google_Service_AnalyticsReporting_Dimension();
                        $keyword->setName('ga:keyword');
                        $dimensionKeyword = [$keyword];

                        $keywordFilter = new Google_Service_AnalyticsReporting_DimensionFilter([
                            'not' => true,
                        ]);
                        $keywordFilter->setDimensionName('ga:keyword');
                        $keywordFilter->setOperator('IN_LIST');
                        $keywordFilter->setExpressions(['(not set)', '(not provided)', '(content targeting)']);

                        $keywordDimensionFilterClause = new Google_Service_AnalyticsReporting_DimensionFilterClause();
                        $keywordDimensionFilterClause->setFilters([$keywordFilter]);

                        $mediumOrganicFilter = new Google_Service_AnalyticsReporting_DimensionFilter();
                        $mediumOrganicFilter->setDimensionName('ga:medium');
                        $mediumOrganicFilter->setOperator('EXACT');
                        $mediumOrganicFilter->setExpressions(['organic']);

                        $organicKeywordDimensionFilterClause = new Google_Service_AnalyticsReporting_DimensionFilterClause();
                        $organicKeywordDimensionFilterClause->setOperator('AND');
                        $organicKeywordDimensionFilterClause->setFilters([$keywordFilter, $mediumOrganicFilter]);

                        $source = new Google_Service_AnalyticsReporting_Dimension();
                        $source->setName('ga:source');
                        $dimensionSource = [$source];

                        $mediumReferralFilter = new Google_Service_AnalyticsReporting_DimensionFilter();
                        $mediumReferralFilter->setDimensionName('ga:medium');
                        $mediumReferralFilter->setOperator('EXACT');
                        $mediumReferralFilter->setExpressions(['referral']);

                        $referralDimensionFilterClause = new Google_Service_AnalyticsReporting_DimensionFilterClause();
                        $referralDimensionFilterClause->setFilters([$mediumReferralFilter]);

                        $requests = [];
                        $requests[] = $this->createRequest($website->analytics, $dateRange, [$sessions], $dimensionSocialNetwork, null, [$socialNetworkDimensionFilterClause]);
                        $requests[] = $this->createRequest($website->analytics, $dateRange, [$sessions], $dimensionChannelGrouping);
                        $requests[] = $this->createRequest($website->analytics, $dateRange, [$sessions], $dimensionKeyword, null, [$keywordDimensionFilterClause]);
                        $requests[] = $this->createRequest($website->analytics, $dateRange, [$sessions], $dimensionKeyword, null, [$organicKeywordDimensionFilterClause]);
                        $requests[] = $this->createRequest($website->analytics, $dateRange, [$users], $dimensionSource, null, [$referralDimensionFilterClause]);
                        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
                        $body->setReportRequests($requests);
                        $reportGroups[$website->website] = $analytics->reports->batchGet($body);

                        $data = $this->getData($reportGroups);

                        $socialNetworks = [];
                        if ($data[$website->website][0]) {
                            $data[$website->website][0]['sessions'] = collect($data[$website->website][0]['sessions'])->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
                            $socialNetworksTotal = isset($data[$website->website][0]['sessions_total']) ? $data[$website->website][0]['sessions_total']['current'] : 0;
                            $socialNetworks = [
                                'total' => $socialNetworksTotal,
                                'percentOfTotal' => round(($socialNetworksTotal / $values['sessions']['current']) * 100, 2),
                                'data' => [],
                            ];
                            if (isset($data[$website->website][0]['sessions'])) {
                                foreach ($data[$website->website][0]['sessions'] as $key => $value) {
                                    array_push($socialNetworks['data'], [$key, round($value['current'] / $socialNetworksTotal, 2), round(($value['current'] / $socialNetworksTotal) * 100, 2) . '% (' . $value['current'] . ')']);
                                }
                            }
                        }

                        $channels = [];
                        if ($data[$website->website][1]) {
                            $data[$website->website][1]['sessions'] = collect($data[$website->website][1]['sessions'])->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
                            $channelsTotal = isset($data[$website->website][1]['sessions_total']) ? $data[$website->website][1]['sessions_total']['current'] : 0;
                            $channels = [
                                'total' => $channelsTotal,
                                'data' => [],
                            ];
                            if (isset($data[$website->website][1]['sessions'])) {
                                foreach ($data[$website->website][1]['sessions'] as $key => $value) {
                                    array_push($channels['data'], [$key, round(($value['current'] / $channelsTotal) * 100, 2), $key . "\n" . round(($value['current'] / $channelsTotal) * 100, 2) . '% (' . $value['current'] . ')']);
                                }
                            }
                        }

                        $keywords = [];
                        if ($data[$website->website][2]) {
                            $data[$website->website][2]['sessions'] = collect($data[$website->website][2]['sessions'])->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
                            $keywordsTotal = isset($data[$website->website][2]['sessions_total']) ? $data[$website->website][2]['sessions_total']['current'] : 0;
                            $keywords = [
                                'total' => $keywordsTotal,
                                'percentOfTotal' => round(($keywordsTotal / $values['sessions']['current']) * 100, 2),
                                'data' => [],
                            ];
                            if (isset($data[$website->website][2]['sessions'])) {
                                foreach ($data[$website->website][2]['sessions'] as $key => $value) {
                                    array_push($keywords['data'], [$key, (int)$value['current'], $key . "\n" . $value['current'] . ' (' . round(($value['current'] / $keywordsTotal) * 100, 2) . '%)']);
                                }
                            }
                        }

                        $keywordsOrganic = [];
                        if ($data[$website->website][3]) {
                            $data[$website->website][3]['sessions'] = collect($data[$website->website][3]['sessions'])->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
                            $keywordsOrganicTotal = isset($data[$website->website][3]['sessions_total']) ? $data[$website->website][3]['sessions_total']['current'] : 0;
                            $keywordsOrganic = [
                                'total' => $keywordsOrganicTotal,
                                'percentOfTotal' => round(($keywordsOrganicTotal / $values['sessions']['current']) * 100, 2),
                                'data' => [],
                            ];
                            if (isset($data[$website->website][3]['sessions'])) {
                                foreach ($data[$website->website][3]['sessions'] as $key => $value) {
                                    array_push($keywordsOrganic['data'], [$key, (int)$value['current'], $key . "\n" . $value['current'] . ' (' . round(($value['current'] / $keywordsOrganicTotal) * 100, 2) . '%)']);
                                }
                            }
                        }

                        $referrals = [];
                        if ($data[$website->website][4]) {
                            $data[$website->website][4]['users'] = collect($data[$website->website][4]['users'])->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
                            $referralsTotal = isset($data[$website->website][4]['users_total']) ? $data[$website->website][4]['users_total']['current'] : 0;
                            $referrals = [
                                'total' => $referralsTotal,
                                'percentOfTotal' => round(($referralsTotal / $values['users']['current']) * 100, 2),
                                'data' => [],
                            ];
                            if (isset($data[$website->website][4]['users'])) {
                                foreach ($data[$website->website][4]['users'] as $key => $value) {
                                    array_push($referrals['data'], [$key, (int)$value['current'], $key . "\n" . $value['current'] . ' (' . round(($value['current'] / $referralsTotal) * 100, 2) . '%)']);
                                }
                            }
                        }

                        $mediumSearchEngineFilter = new Google_Service_AnalyticsReporting_DimensionFilter();
                        $mediumSearchEngineFilter->setDimensionName('ga:medium');
                        $mediumSearchEngineFilter->setOperator('IN_LIST');
                        $mediumSearchEngineFilter->setExpressions(['cpa', 'cpc', 'cpm', 'cpp', 'cpv', 'organic', 'ppc']);

                        $searchEngineDimensionFilterClause = new Google_Service_AnalyticsReporting_DimensionFilterClause();
                        $searchEngineDimensionFilterClause->setFilters([$mediumSearchEngineFilter]);

                        $mediumSearchEngineOrganicFilter = new Google_Service_AnalyticsReporting_DimensionFilter();
                        $mediumSearchEngineOrganicFilter->setDimensionName('ga:medium');
                        $mediumSearchEngineOrganicFilter->setOperator('EXACT');
                        $mediumSearchEngineOrganicFilter->setExpressions(['organic']);

                        $searchEngineOrganicDimensionFilterClause = new Google_Service_AnalyticsReporting_DimensionFilterClause();
                        $searchEngineOrganicDimensionFilterClause->setFilters([$mediumSearchEngineOrganicFilter]);

                        $pagePath = new Google_Service_AnalyticsReporting_Dimension();
                        $pagePath->setName('ga:pagePath');
                        $dimensionPagePath = [$pagePath];

                        $entrances = new Google_Service_AnalyticsReporting_Metric();
                        $entrances->setExpression('ga:entrances');
                        $entrances->setAlias('entrances');

                        $landingPagePath = new Google_Service_AnalyticsReporting_Dimension();
                        $landingPagePath->setName('ga:landingPagePath');
                        $dimensionlandingPagePath = [$landingPagePath];

                        $deviceCategory = new Google_Service_AnalyticsReporting_Dimension();
                        $deviceCategory->setName('ga:deviceCategory');
                        $dimensionDeviceCategory = [$deviceCategory];

                        $requests = [];
                        $requests[] = $this->createRequest($website->analytics, $dateRange, [$users], $dimensionSource, null, [$searchEngineDimensionFilterClause]);
                        $requests[] = $this->createRequest($website->analytics, $dateRange, [$users], $dimensionSource, null, [$searchEngineOrganicDimensionFilterClause]);
                        $requests[] = $this->createRequest($website->analytics, $dateRange, [$pageviews], $dimensionPagePath);
                        $requests[] = $this->createRequest($website->analytics, $dateRange, [$entrances], $dimensionlandingPagePath);
                        $requests[] = $this->createRequest($website->analytics, $dateRange, [$users], $dimensionDeviceCategory);

                        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
                        $body->setReportRequests($requests);
                        $reportGroups[$website->website] = $analytics->reports->batchGet($body);

                        $data = $this->getData($reportGroups);

                        $data[$website->website][0]['users'] = collect($data[$website->website][0]['users'])->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
                        $searchEnginesTotal = isset($data[$website->website][0]['users_total']) ? $data[$website->website][0]['users_total']['current'] : 0;
                        $searchEngines = [
                            'total' => $searchEnginesTotal,
                            'percentOfTotal' => round(($searchEnginesTotal / $values['users']['current']) * 100, 2),
                            'data' => [],
                        ];
                        if (isset($data[$website->website][0]['users'])) {
                            foreach ($data[$website->website][0]['users'] as $key => $value) {
                                array_push($searchEngines['data'], [$key, (int)$value['current'], $key . "\n" . $value['current'] . ' (' . round(($value['current'] / $searchEnginesTotal) * 100, 2) . '%)']);
                            }
                        }

                        $data[$website->website][1]['users'] = collect($data[$website->website][1]['users'])->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
                        $searchEnginesOrganicTotal = isset($data[$website->website][1]['users_total']) ? $data[$website->website][1]['users_total']['current'] : 0;
                        $searchEnginesOrganic = [
                            'total' => $searchEnginesOrganicTotal,
                            'percentOfTotal' => round(($searchEnginesOrganicTotal / $values['users']['current']) * 100, 2),
                            'data' => [],
                        ];
                        if (isset($data[$website->website][1]['users'])) {
                            foreach ($data[$website->website][1]['users'] as $key => $value) {
                                array_push($searchEnginesOrganic['data'], [$key, (int)$value['current'], $key . "\n" . $value['current'] . ' (' . round(($value['current'] / $searchEnginesOrganicTotal) * 100, 2) . '%)']);
                            }
                        }

                        $data[$website->website][2]['pageviews'] = collect($data[$website->website][2]['pageviews'])->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
                        $pagePathTotal = isset($data[$website->website][2]['pageviews_total']) ? $data[$website->website][2]['pageviews_total']['current'] : 0;
                        $pagePath = [
                            'total' => $pagePathTotal,
                            'data' => [],
                        ];
                        if (isset($data[$website->website][2]['pageviews'])) {
                            foreach ($data[$website->website][2]['pageviews'] as $key => $value) {
                                array_push($pagePath['data'], [$key, (int)$value['current'], $key . "\n" . $value['current'] . ' (' . round(($value['current'] / $pagePathTotal) * 100, 2) . '%)']);
                            }
                        }

                        $data[$website->website][3]['entrances'] = collect($data[$website->website][3]['entrances'])->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
                        $landingPagePathTotal = isset($data[$website->website][3]['entrances_total']) ? $data[$website->website][3]['entrances_total']['current'] : 0;
                        $landingPagePath = [
                            'total' => $landingPagePathTotal,
                            'data' => [],
                        ];
                        if (isset($data[$website->website][3]['entrances'])) {
                            foreach ($data[$website->website][3]['entrances'] as $key => $value) {
                                array_push($landingPagePath['data'], [$key, (int)$value['current'], $key . "\n" . $value['current'] . ' (' . round(($value['current'] / $landingPagePathTotal) * 100, 2) . '%)']);
                            }
                        }

                        $data[$website->website][4]['users'] = collect($data[$website->website][4]['users'])->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
                        $deviceCategoryTotal = isset($data[$website->website][4]['users_total']) ? $data[$website->website][4]['users_total']['current'] : 0;
                        $deviceCategory = [
                            'total' => $deviceCategoryTotal,
                            'data' => [],
                        ];
                        if (isset($data[$website->website][4]['users'])) {
                            foreach ($data[$website->website][4]['users'] as $key => $value) {
                                array_push($deviceCategory['data'], [$key, round(($value['current'] / $deviceCategoryTotal) * 100, 2), $key . "\n" . round(($value['current'] / $deviceCategoryTotal) * 100, 2) . '% (' . $value['current'] . ')']);
                            }
                        }
                    }
                }

                $parameters = ['api', 'analytics', 'values', 'userTypes', 'genders', 'ages', 'countries', 'socialNetworks', 'channels', 'keywords', 'keywordsOrganic', 'referrals', 'searchEngines', 'searchEnginesOrganic', 'pagePath', 'landingPagePath', 'deviceCategory'];
            } else {
                $view = 'index';

                $api->breadcrumbs = [$api->breadcrumb('reports', trans('labels.report-' . $slug))];

                /* SALES */

                $apartments = $this->dashboardApartments($from, $to);
                $salesIds = explode(',', implode(',', array_column($apartments, 'sales')));

                $unitSales = null;
                $salesBreakdown = null;
                if (Auth::user()->can('View Dashboard Sales')) {
                    $unitSales = $this->dashboardUnitSales($apartments);
                    $salesBreakdown = $this->dashboardSalesBreakdown($apartments);
                }

                $revenue = null;
                if (Auth::user()->can('View Dashboard Revenue')) {
                    $revenue = $this->dashboardRevenue($apartments);
                }

                $revenueReceived = null;
                if (Auth::user()->can('View Dashboard Revenue Received')) {
                    $revenueReceived = $this->dashboardRevenueReceived($salesIds);
                }

                $outstandingRevenue = null;
                if (Auth::user()->can('View Dashboard Outstanding Revenue')) {
                    $outstandingRevenue = $this->dashboardOutstandingRevenue($revenue, $revenueReceived);
                }

                /* FUNDING */

                $investors = 0;
                if (Auth::user()->can('View Dashboard Sales')) {
                    $investors = Investor::count();
                }

                /* ANALYTICS */

                $values = [];
                $id = 1; // mespil.ie
                if (in_array($id, (Auth::user()->can('View All Websites') ? Website::pluck('id') : Auth::user()->websites->pluck('id'))->toArray()) && Auth::user()->can('View: Analytics')) {
                    $from = $from ?: Carbon::now()->subDays(30);
                    $to = $to ?: Carbon::now()->subDays(1);
                    $website = Website::find($id);
                    if ($website) {
                        $client = new Google_Client();
                        $client->setApplicationName('Analytics - sky.mespil.ie');
                        $client->setAuthConfig(storage_path('app/google/analytics.json'));
                        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);

                        $analytics = new Google_Service_AnalyticsReporting($client);

                        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
                        $dateRange->setStartDate($from->format('Y-m-d'));
                        $dateRange->setEndDate($to->format('Y-m-d'));

                        $dateRangeHistory = new Google_Service_AnalyticsReporting_DateRange();
                        $dateRangeHistory->setStartDate($from->subDays(30)->format('Y-m-d'));
                        $dateRangeHistory->setEndDate($to->subDays(30)->format('Y-m-d'));

                        $reportGroups = [];

                        $users = new Google_Service_AnalyticsReporting_Metric();
                        $users->setExpression('ga:users');
                        $users->setAlias('users');

                        $newUsers = new Google_Service_AnalyticsReporting_Metric();
                        $newUsers->setExpression('ga:newUsers');
                        $newUsers->setAlias('newUsers');

                        $sessions = new Google_Service_AnalyticsReporting_Metric();
                        $sessions->setExpression('ga:sessions');
                        $sessions->setAlias('sessions');

                        $sessionsPerUser = new Google_Service_AnalyticsReporting_Metric();
                        $sessionsPerUser->setExpression('ga:sessionsPerUser');
                        $sessionsPerUser->setAlias('sessionsPerUser');

                        $pageviews = new Google_Service_AnalyticsReporting_Metric();
                        $pageviews->setExpression('ga:pageviews');
                        $pageviews->setAlias('pageviews');

                        $pageviewsPerSession = new Google_Service_AnalyticsReporting_Metric();
                        $pageviewsPerSession->setExpression('ga:pageviewsPerSession');
                        $pageviewsPerSession->setAlias('pageviewsPerSession');

                        $avgSessionDuration = new Google_Service_AnalyticsReporting_Metric();
                        $avgSessionDuration->setExpression('ga:avgSessionDuration');
                        $avgSessionDuration->setAlias('avgSessionDuration');
                        $avgSessionDuration->setFormattingType('TIME');

                        $bounceRate = new Google_Service_AnalyticsReporting_Metric();
                        $bounceRate->setExpression('ga:bounceRate');
                        $bounceRate->setAlias('bounceRate');

                        $dateRanges = [$dateRange, $dateRangeHistory];
                        $metrics = [$users, $newUsers, $sessions, $sessionsPerUser, $pageviews, $pageviewsPerSession, $avgSessionDuration, $bounceRate];

                        $requests = [];
                        $requests[] = $this->createRequest($website->analytics, $dateRanges, $metrics);

                        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
                        $body->setReportRequests($requests);
                        $reportGroups[$website->website] = $analytics->reports->batchGet($body);

                        $data = $this->getData($reportGroups);

                        $values = $data[$website->website][0];
                    }
                }

                $parameters = ['api', 'unitSales', 'salesBreakdown', 'revenue', 'revenueReceived', 'outstandingRevenue', 'investors', 'values'];
            }
        } else {
            array_push($api->breadcrumbs, $api->breadcrumb($slug, trans('labels.report-' . $slug)));

            $api->datatables = [
                'datatable-' . $slug => [
                    'options' => method_exists($this, 'dOptions') ? $this->dOptions($slug) : [],
                    'order' => method_exists($this, 'dOrder') ? $this->dOrder($slug) : [],
                    'buttons' => method_exists($this, 'dButtons') ? $this->dButtons($slug) : [],
                    'columns' => $this->dColumns($slug),
                    'data' => [],
                ],
            ];

            $parameters = ['api', 'slug'];

            if ($slug == 'apartments') {
                $statuses = Status::where('parent', 1)->orderBy('order')->pluck('name', 'id');

                $parameters = array_merge($parameters, ['statuses']);
            } elseif ($slug == 'sales') {
                $statuses = Status::where('parent', 1)->where('id', '!=', 10)->orderBy('order')->pluck('name', 'id');
                $agents = Agent::select('agents.id', 'agents.company AS agent')->leftJoin('agent_project', 'agent_project.agent_id', '=', 'agents.id')->whereIn('agent_project.project_id', Helper::project())->orderBy('agent')->pluck('agent', 'id')->prepend(trans('text.directClient'), '');

                $parameters = array_merge($parameters, ['statuses', 'agents']);
            } elseif ($slug == 'clients') {
                $statuses = Status::select('id', 'name')->where('parent', 2)->orderBy('order')->pluck('name', 'id');
                $agents = Agent::select('agents.id', 'agents.company AS agent')->leftJoin('agent_project', 'agent_project.agent_id', '=', 'agents.id')->whereIn('agent_project.project_id', Helper::project())->orderBy('agent')->pluck('agent', 'id');
                $countries = Country::select('id', 'name')->orderBy('name')->pluck('name', 'id');
                $sources = Source::select('id', 'name')->where('parent', 1)->orderBy('name')->pluck('name', 'id');

                $parameters = array_merge($parameters, ['statuses', 'agents', 'countries', 'sources']);
            } elseif ($slug == 'investors') {
                $projects = Project::select('id', 'name')->orderBy('name')->pluck('name', 'id');
                $countries = Country::select('id', 'name')->orderBy('name')->pluck('name', 'id');
                $fundSizes = FundSize::select('id', 'name')->orderBy('order')->pluck('name', 'id');
                $investmentRanges = InvestmentRange::select('id', 'name')->orderBy('order')->pluck('name', 'id');
                $sources = Source::select('id', 'name')->where('parent', 3)->orderBy('name')->pluck('name', 'id');
                $categories = Category::select('id', 'name')->where('parent', 1)->orderBy('name')->pluck('name', 'id');

                $parameters = array_merge($parameters, ['projects', 'countries', 'sources', 'fundSizes', 'investmentRanges', 'categories']);
            } elseif ($slug == 'tasks') {
                $users = User::selectRaw('id, CONCAT(users.first_name, " ", users.last_name) AS user')->orderBy('user')->pluck('user', 'id');
                $statuses = Status::where('parent', 6)->orderBy('order')->pluck('name', 'id');
                $priorities = Status::where('parent', 5)->orderBy('order')->pluck('name', 'id');
                $departments = Department::orderBy('order')->pluck('name', 'id');

                $parameters = array_merge($parameters, ['statuses', 'users', 'priorities', 'departments']);
            } elseif ($slug == 'targets') {
                $years = Target::select('id', 'name AS year')->whereNull('parent')->whereIn('project_id', Helper::project())->orderBy('name', 'desc')->pluck('year', 'id');
                $targets = Target::select('id', 'name as target')->where('parent', key($years->all()))->whereIn('project_id', Helper::project())->orderBy('name')->pluck('target', 'id');

                $parameters = array_merge($parameters, ['years', 'targets']);
            } elseif (in_array($slug, ['closing-dates', 'discount', 'leads', 'cancellations'])) {
                $agents = Agent::select('agents.id', 'agents.company AS agent')->leftJoin('agent_project', 'agent_project.agent_id', '=', 'agents.id')->whereIn('agent_project.project_id', Helper::project())->orderBy('agent')->pluck('agent', 'id')->prepend(trans('text.directClient'), '');

                $parameters = array_merge($parameters, ['agents']);
            } elseif (in_array($slug, ['subagent-commissions'])) {
                $agents = Agent::select('agents.id', 'agents.company AS agent')->leftJoin('agent_project', 'agent_project.agent_id', '=', 'agents.id')->whereIn('agent_project.project_id', Helper::project())->where('agents.type', 'direct')->orderBy('agent')->pluck('agent', 'id');

                $parameters = array_merge($parameters, ['agents']);
            } elseif (in_array($slug, ['viewings'])) {
                $agents = Agent::select('agents.id', 'agents.company AS agent')->leftJoin('agent_project', 'agent_project.agent_id', '=', 'agents.id')->whereIn('agent_project.project_id', Helper::project())->orderBy('agent')->pluck('agent', 'id')->prepend(trans('text.directClient'), '');
                $clients = Client::selectRaw('clients.id, CONCAT(clients.first_name, " ", COALESCE(clients.last_name, "")) AS client')
                    ->leftJoin('client_project', 'client_project.client_id', '=', 'clients.id')
                    ->where(function ($query) {
                        $query->whereIn('client_project.project_id', Helper::project())->when(!session('project'), function ($q) {
                            return $q->orWhereNull('client_project.project_id');
                        });
                    })
                    ->orderBy('client')
                    ->pluck('client', 'id');
                $apartments = Apartment::select('id', 'unit')->whereIn('project_id', Helper::project())->orderBy('unit')->pluck('unit', 'id');
                $statuses = Status::where('parent', 4)->orderBy('order')->pluck('name', 'id');

                $parameters = array_merge($parameters, ['agents', 'clients', 'apartments', 'statuses']);
            }
        }

        return View::exists('reports.' . $slug . ($view ? '.' . $view : '')) ? view('reports.' . $slug . ($view ? '.' . $view : ''), compact($parameters)) : abort(404);
    }

    public function createRequest($viewId, $dateRanges, $metrics, $dimensions = null, $order = null, $filters = null)
    {
        $request = new Google_Service_AnalyticsReporting_ReportRequest([
            'hideValueRanges' => true,
        ]);
        $request->setViewId($viewId);
        $request->setDateRanges($dateRanges);
        $request->setMetrics($metrics);

        if ($order) {
            $request->setOrderBys($order);
        }

        if ($dimensions) {
            $request->setDimensions($dimensions);
        }

        if ($filters) {
            $request->setDimensionFilterClauses($filters);
        }

        return $request;
    }

    public function getData($reportGroups)
    {
        $data = [];
        foreach ($reportGroups as $website => $reports) {
            for ($reportIndex = 0; $reportIndex < count($reports->getReports()); $reportIndex++) {
                $report = $reports[$reportIndex];
                $header = $report->getColumnHeader();
                // $dimensionHeaders = $header->getDimensions();
                $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
                $reportData = $report->getData();
                $rows = $reportData->getRows();

                if (count($rows)) {
                    for ($rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
                        $row = $rows[$rowIndex];
                        $dimensions = $row->getDimensions();
                        $metrics = $row->getMetrics();

                        if ($dimensions) {
                            for ($i = 0; /*$i < count($dimensionHeaders) && */$i < count($dimensions); $i++) {
                                // $dimensionHeaders[$i];
                                $result = $this->getMetrics($metricHeaders, $metrics);
                                $metric = key($result);
                                $data[$website][$reportIndex][$metric][$dimensions[$i]] = $result[$metric];

                                if ($rowIndex == 0 && $i == 0) {
                                    $totals = $reportData->getTotals();
                                    for ($totalIndex = 0; $totalIndex < count($totals); $totalIndex++) {
                                        if ($totalIndex == 0) {
                                            $key = 'current';
                                        } elseif ($totalIndex == 1) {
                                            $key = 'previous';
                                        } else {
                                            $key = $totalIndex;
                                        }
                                        $data[$website][$reportIndex][$metric . '_total'][$key] = $totals[$totalIndex]->getValues()[0];
                                    }
                                }
                            }
                        } else {
                            $data[$website][$reportIndex] = $this->getMetrics($metricHeaders, $metrics);
                        }
                    }
                } else {
                    $data[$website][$reportIndex] = [];
                }
            }
        }

        return $data;
    }

    public function getMetrics($metricHeaders, $metrics)
    {
        $data = [];
        if ($metrics) {
            for ($i = 0; $i < count($metrics); $i++) {
                $values = $metrics[$i]->getValues();
                for ($j = 0; $j < count($values); $j++) {
                    $entry = $metricHeaders[$j];

                    if ($i == 0) {
                        $data[$entry->getName()]['current'] = $values[$j];
                    } elseif ($i == 1) {
                        $data[$entry->getName()]['previous'] = $values[$j];
                        $percentChange = ($data[$entry->getName()]['current'] && $values[$j]) ? round((($data[$entry->getName()]['current'] - $values[$j]) / $values[$j]) * 100, 2) : 0;
                        $data[$entry->getName()]['percentChange'] = $percentChange;
                    } else {
                        $data[$entry->getName()][$i] = $values[$j];
                    }
                }
            }
        }

        return $data;
    }

    public function dOptions($slug)
    {
        if (in_array($slug, ['apartments', 'closing-dates', 'discount', 'agent-commissions', 'subagent-commissions', 'sales', 'cancellations'])) {
            return [
                'dom' => '<"card-block table-responsive"tr><"card-footer"ip>',
                'wrapperClass' => 'table-hidden',
                'footer' => 'bg-dark text-white',
            ];
        } elseif (in_array($slug, ['clients', 'investors', 'viewings'])) {
            return [
                'dom' => '<"card-block table-responsive"tr><"card-footer"ip>',
                'wrapperClass' => 'table-hidden',
            ];
        } elseif (in_array($slug, ['tasks'])) {
            return [
                'dom' => '<"card-block table-responsive"tr><"card-footer"ip>',
                'wrapperClass' => 'table-hidden',
                'priorities' => 'order',
            ];
        } elseif (in_array($slug, ['conversion-rate', 'targets'])) {
            return [
                'dom' => '<"card-block table-responsive"tr>',
                'wrapperClass' => 'table-hidden',
            ];
        } else {
            return [
                'wrapperClass' => 'table-hidden',
            ];
        }
    }

    public function dOrder($slug)
    {
        if (in_array($slug, ['apartments', 'clients', 'investors', 'closing-dates', 'discount', 'agent-commissions', 'subagent-commissions', 'sales', 'cancellations'])) {
            return [
                [0, 'asc'],
            ];
        } elseif (in_array($slug, ['viewings'])) {
            return [
                [0, 'desc'],
            ];
        } elseif (in_array($slug, ['tasks'])) {
            return [
                [6, 'desc'],
                [3, 'asc'],
            ];
        } else {
            return [];
        }
    }

    public function dButtons($slug)
    {
        if (!$slug) {
            return [];
        }

        return [
            'export' => [
                'url' => Helper::route('export-report', $slug),
                'class' => 'btn-info button-export',
                'parameters' => 'hidden data-ajax data-form="report-form"',
                'method' => 'post',
                'icon' => 'file-excel',
                'name' => trans('buttons.export'),
            ],
            'generate' => [
                'url' => Helper::route('generate-report', $slug),
                'class' => 'btn-success /*disabled*/ button-generate',
                'parameters' => '/*disabled*/ autocomplete="off" data-ajax data-form="report-form"',
                'method' => 'post',
                'icon' => 'cog',
                'name' => trans('buttons.generate'),
            ],
        ];
    }

    public function dColumns($slug, $export = false)
    {
        if ($slug == 'apartments') {
            return [
                [
                    'id' => 'unit',
                    'name' => trans('labels.unit'),
                ],
                [
                    'id' => 'status',
                    'name' => trans('labels.status'),
                ],
                [
                    'id' => 'bed',
                    'name' => trans('labels.bed'),
                ],
                [
                    'id' => 'area',
                    'name' => trans('labels.area'),
                    'class' => 'text-right',
                ],
                [
                    'id' => 'price',
                    'name' => trans('labels.price'),
                    'class' => 'text-right',
                    'footer' => [
                        'function' => 'sum',
                        'currency' => true,
                    ],
                ],
            ];
        } elseif ($slug == 'clients') {
            return [
                [
                    'id' => 'client',
                    'name' => trans('labels.client'),
                ],
                [
                    'id' => 'status',
                    'name' => trans('labels.status'),
                ],
                [
                    'id' => 'units',
                    'name' => trans('labels.units'),
                ],
                [
                    'id' => 'country_name',
                    'name' => trans('labels.country'),
                ],
                [
                    'id' => 'email',
                    'name' => trans('labels.email'),
                    'order' => false,
                ],
                [
                    'id' => 'phone',
                    'name' => trans('labels.phone'),
                    'order' => false,
                ],
                [
                    'id' => 'source_name',
                    'name' => trans('labels.source'),
                ],
                [
                    'id' => 'agent',
                    'name' => trans('labels.agent'),
                ],
            ];
        } elseif ($slug == 'investors') {
            $columns = [
                [
                    'id' => 'investor',
                    'name' => trans('labels.investor'),
                ],
                [
                    'id' => 'country_name',
                    'name' => trans('labels.country'),
                ],
                [
                    'id' => 'source_name',
                    'name' => trans('labels.source'),
                ],
                [
                    'id' => 'category_name',
                    'name' => trans('labels.category'),
                ],
                [
                    'id' => 'fund_size_name',
                    'name' => trans('labels.fundSize'),
                ],
                [
                    'id' => 'investment_range_name',
                    'name' => trans('labels.investmentRange'),
                ],
                [
                    'id' => 'start',
                    'name' => trans('labels.startAt'),
                    'render' =>  ['sort'],
                ],
                [
                    'id' => 'end',
                    'name' => trans('labels.endAt'),
                    'render' =>  ['sort'],
                ],
                [
                    'id' => 'projects',
                    'name' => trans('labels.project'),
                ],
            ];

            if ($export) {
                array_push($columns, [
                    'id' => 'phone',
                    'name' => trans('labels.phone'),
                ],
                [
                    'id' => 'email',
                    'name' => trans('labels.email'),
                ]);
            }

            return $columns;
        } elseif ($slug == 'tasks') {
            return [
                [
                    'id' => 'name',
                    'name' => trans('labels.name'),
                    'class' => 'vertical-center',
                ],
                [
                    'id' => 'users',
                    'name' => trans('labels.assignedTo'),
                    'class' => 'vertical-center',
                ],
                [
                    'id' => 'department',
                    'name' => trans('labels.department'),
                    'class' => 'vertical-center',
                ],
                [
                    'id' => 'deadline',
                    'name' => trans('labels.deadline'),
                    'render' =>  ['sort'],
                    'class' => 'text-center vertical-center',
                ],
                [
                    'id' => 'priority',
                    'name' => trans('labels.priority'),
                    'class' => 'text-center vertical-center task-priority',
                ],
                [
                    'id' => 'status',
                    'name' => trans('labels.status'),
                    'class' => 'text-center vertical-center',
                ],
                [
                    'id' => 'completed',
                    'name' => trans('labels.completed'),
                    'render' =>  ['sort'],
                    'class' => 'text-center vertical-center',
                ],
            ];
        } elseif ($slug == 'discount') {
            return [
                [
                    'id' => 'unit',
                    'name' => trans('labels.unit'),
                ],
                [
                    'id' => 'user',
                    'name' => trans('labels.user'),
                ],
                [
                    'id' => 'client',
                    'name' => trans('labels.client'),
                ],
                [
                    'id' => 'agent',
                    'name' => trans('labels.agent'),
                ],
                [
                    'id' => 'price',
                    'name' => trans('labels.price'),
                    'class' => 'text-right',
                ],
                [
                    'id' => 'sale_price',
                    'name' => trans('labels.salePrice'),
                    'class' => 'text-right',
                ],
                [
                    'id' => 'furniture',
                    'name' => trans('labels.furniture'),
                    'class' => 'text-right',
                ],
                [
                    'id' => 'total',
                    'name' => trans('labels.totalSalePrice'),
                    'class' => 'text-right',
                ],
                [
                    'id' => 'discount',
                    'name' => trans('labels.report-' . $slug),
                    'class' => 'text-right',
                    'footer' => [
                        'function' => 'sum',
                        'currency' => true,
                    ],
                ],
            ];
        } elseif ($slug == 'agent-commissions') {
            return [
                [
                    'id' => 'agent',
                    'name' => trans('labels.agent'),
                ],
                [
                    'id' => 'units',
                    'name' => trans('labels.units'),
                ],
                [
                    'id' => 'sale_price',
                    'name' => trans('labels.salePrice'),
                    'class' => 'text-right',
                ],
                [
                    'id' => 'furniture',
                    'name' => trans('labels.furniture'),
                    'class' => 'text-right',
                ],
                [
                    'id' => 'total',
                    'name' => trans('labels.totalSalePrice'),
                    'class' => 'text-right',
                    'footer' => [
                        'function' => 'sum',
                        'currency' => true,
                    ],
                ],
                [
                    'id' => 'commission',
                    'name' => trans('labels.commission'),
                    'class' => 'text-right',
                    'footer' => [
                        'function' => 'sum',
                        'currency' => true,
                    ],
                ],
                [
                    'id' => 'sub_commission',
                    'name' => trans('labels.subCommission'),
                    'class' => 'text-right',
                    'footer' => [
                        'function' => 'sum',
                        'currency' => true,
                    ],
                ],
                [
                    'id' => 'commissions',
                    'name' => trans('labels.totalCommissions'),
                    'class' => 'text-right',
                    'footer' => [
                        'function' => 'sum',
                        'currency' => true,
                    ],
                ],
            ];
        } elseif ($slug == 'subagent-commissions') {
            return [
                [
                    'id' => 'agent',
                    'name' => trans('labels.subAgent'),
                ],
                [
                    'id' => 'units',
                    'name' => trans('labels.units'),
                ],
                [
                    'id' => 'sale_price',
                    'name' => trans('labels.salePrice'),
                    'class' => 'text-right',
                ],
                [
                    'id' => 'furniture',
                    'name' => trans('labels.furniture'),
                    'class' => 'text-right',
                ],
                [
                    'id' => 'total',
                    'name' => trans('labels.totalSalePrice'),
                    'class' => 'text-right',
                    'footer' => [
                        'function' => 'sum',
                        'currency' => true,
                    ],
                ],
                [
                    'id' => 'commission',
                    'name' => trans('labels.commission'),
                    'class' => 'text-right',
                    'footer' => [
                        'function' => 'sum',
                        'currency' => true,
                    ],
                ],
            ];
        } elseif ($slug == 'sales') {
            return [
                [
                    'id' => 'project',
                    'name' => trans('labels.project'),
                ],
                [
                    'id' => 'unit',
                    'name' => trans('labels.unit'),
                ],
                [
                    'id' => 'bed',
                    'name' => trans('labels.bed'),
                ],
                [
                    'id' => 'status',
                    'name' => trans('labels.status'),
                ],
                [
                    'id' => 'client',
                    'name' => trans('labels.client'),
                ],
                [
                    'id' => 'country',
                    'name' => trans('labels.country'),
                ],
                [
                    'id' => 'agent',
                    'name' => trans('labels.agent'),
                ],
                [
                    'id' => 'price',
                    'name' => trans('labels.price'),
                    'class' => 'text-right',
                ],
                [
                    'id' => 'furniture',
                    'name' => trans('labels.furniture'),
                    'class' => 'text-right',
                ],
                [
                    'id' => 'commission',
                    'name' => trans('labels.commission'),
                    'class' => 'text-right',
                    'footer' => [
                        'function' => 'sum',
                        'currency' => true,
                    ],
                ],
                [
                    'id' => 'sub_commission',
                    'name' => trans('labels.subCommission'),
                    'class' => 'text-right',
                    'footer' => [
                        'function' => 'sum',
                        'currency' => true,
                    ],
                ],
                [
                    'id' => 'total',
                    'name' => trans('labels.totalPrice'),
                    'class' => 'text-right',
                    'footer' => [
                        'function' => 'sum',
                        'currency' => true,
                    ],
                ],
            ];
        } elseif ($slug == 'cancellations') {
            return [
                [
                    'id' => 'unit',
                    'name' => trans('labels.unit'),
                ],
                [
                    'id' => 'client',
                    'name' => trans('labels.client'),
                ],
                [
                    'id' => 'country',
                    'name' => trans('labels.country'),
                ],
                [
                    'id' => 'agent',
                    'name' => trans('labels.agent'),
                ],
                [
                    'id' => 'price',
                    'name' => trans('labels.price'),
                    'class' => 'text-right',
                ],
                [
                    'id' => 'furniture',
                    'name' => trans('labels.furniture'),
                    'class' => 'text-right',
                ],
                [
                    'id' => 'total',
                    'name' => trans('labels.totalPrice'),
                    'class' => 'text-right',
                    'footer' => [
                        'function' => 'sum',
                        'currency' => true,
                    ],
                ],
            ];
        } elseif ($slug == 'closing-dates') {
            $columns = [
                [
                    'id' => 'closing',
                    'name' => trans('labels.closingAt'),
                    'render' =>  ['sort'],
                    'class' => 'vertical-center popovers',
                ],
                [
                    'id' => 'promissory',
                    'name' => trans('labels.promissoryAt'),
                    'render' =>  ['sort'],
                    'class' => 'vertical-center',
                ],
                [
                    'id' => 'apartment',
                    'name' => trans('labels.apartment'),
                    'class' => 'vertical-center',
                ],
                [
                    'id' => 'client',
                    'name' => trans('labels.client'),
                    'class' => 'vertical-center',
                ],
                [
                    'id' => 'agent',
                    'name' => trans('labels.agent'),
                    'class' => 'vertical-center',
                ],
                [
                    'id' => 'price',
                    'name' => trans('labels.price'),
                    'class' => 'text-right',
                    'footer' => [
                        'function' => 'sum',
                        'currency' => true,
                    ],
                ],
                [
                    'id' => 'furniture',
                    'name' => trans('labels.furniture'),
                    'class' => 'text-right',
                    'footer' => [
                        'function' => 'sum',
                        'currency' => true,
                    ],
                ],
                [
                    'id' => 'total',
                    'name' => trans('labels.totalPrice'),
                    'class' => 'text-right',
                    'footer' => [
                        'function' => 'sum',
                        'currency' => true,
                    ],
                ],
                [
                    'id' => 'balance',
                    'name' => trans('labels.balance'),
                    'class' => 'text-right',
                    'footer' => [
                        'function' => 'sum',
                        'currency' => true,
                    ],
                ],
            ];

            if ($export) {
                array_push($columns, [
                    'id' => 'description',
                    'name' => trans('labels.description'),
                ]);
            }

            return $columns;
        } /*elseif ($slug == 'leads') {
            return [
                [
                    'id' => 'client',
                    'name' => trans('labels.name'),
                ],
                [
                    'id' => 'status',
                    'name' => trans('labels.status'),
                ],
                [
                    'id' => 'country_name',
                    'name' => trans('labels.country'),
                ],
                [
                    'id' => 'email',
                    'name' => trans('labels.email'),
                    'order' => false,
                ],
                [
                    'id' => 'phone',
                    'name' => trans('labels.phone'),
                    'order' => false,
                ],
                [
                    'id' => 'agent',
                    'name' => trans('labels.agent'),
                ],
            ];
        } */elseif ($slug == 'viewings') {
            $columns = [
                [
                    'id' => 'viewed',
                    'name' => trans('labels.viewedAt'),
                    'render' =>  ['sort'],
                    'class' => 'vertical-center',
                ],
                [
                    'id' => 'clients',
                    'name' => trans('labels.client'),
                    'class' => 'vertical-center',
                ],
                [
                    'id' => 'agent',
                    'name' => trans('labels.agent'),
                    'class' => 'vertical-center',
                ],
                [
                    'id' => 'units',
                    'name' => trans('labels.apartments'),
                    'class' => 'vertical-center cell-max-15',
                    'order' => false,
                ],
                [
                    'id' => 'status',
                    'name' => trans('labels.status'),
                    'class' => 'vertical-center popovers',
                ],
            ];

            if ($export) {
                array_push($columns, [
                    'id' => 'description',
                    'name' => trans('labels.description'),
                ]);
            }

            return $columns;
        } elseif ($slug == 'conversion-rate') {
            return [
                [
                    'id' => 'sales',
                    'name' => trans('labels.sales'),
                    'class' => 'text-center',
                    'order' => false,
                ],
                [
                    'id' => 'leads',
                    'name' => trans('labels.leads'),
                    'class' => 'text-center',
                    'order' => false,
                ],
                [
                    'id' => 'rate',
                    'name' => trans('labels.report-' . $slug),
                    'class' => 'text-center',
                    'order' => false,
                ],
            ];
        } elseif ($slug == 'targets') {
            return [
                [
                    'id' => 'period',
                    'name' => trans('labels.period'),
                    'class' => 'text-center',
                    'order' => false,
                ],
                [
                    'id' => 'salesTarget',
                    'name' => trans('labels.salesTarget'),
                    'class' => 'text-center',
                    'order' => false,
                ],
                [
                    'id' => 'sales',
                    'name' => trans('labels.sales'),
                    'class' => 'text-center',
                    'order' => false,
                ],
                [
                    'id' => 'salesPercentage',
                    'name' => trans('labels.salesPercentage'),
                    'class' => 'text-center',
                    'order' => false,
                ],
                [
                    'id' => 'revenueTarget',
                    'name' => trans('labels.revenueTarget'),
                    'class' => 'text-center',
                    'order' => false,
                ],
                [
                    'id' => 'revenue',
                    'name' => trans('labels.revenue'),
                    'class' => 'text-center',
                    'order' => false,
                ],
                [
                    'id' => 'revenuePercentage',
                    'name' => trans('labels.revenuePercentage'),
                    'class' => 'text-center',
                    'order' => false,
                ],
            ];
        } else {
            return [];
        }
    }

    public function dData(Request $request, $slug, $export = false)
    {
        $data = [];
        $method = 'report' . studly_case($slug);
        if (method_exists($this, $method)) {
            $data = $this->{$method}($request, $export, $slug);
        }

        return $data;
    }

    public function generate(Request $request, $slug)
    {
        $data = $this->dData($request, $slug);

        if ($data) {
            $datatables = [
                'datatable-' . $slug => [
                    'data' => $data,
                ],
            ];

            $show = ['.button-export'];

            return back()->with('datatables', $datatables)->with('show', $show);
        } else {
            return back()->withErrors(trans('text.reportDataError'));
        }
    }

    public function dashboardApartments($from, $to)
    {
        $apartments = [];

        $all = Status::where('statuses.parent', 1)->get()->keyBy('id');
        $statuses = Status::selectRaw('statuses.id, statuses.order, statuses.name, COUNT(DISTINCT apartments.id) AS total, SUM(apartments.price) AS apartments_price, SUM(sales.price + sales.furniture) AS sales_price, SUM((apartments.price - sales.price) + (COALESCE(furniture.price, "0") - sales.furniture)) AS discount, SUM(sales.commission + sales.sub_commission) AS commission, GROUP_CONCAT(apartments.id SEPARATOR ",") AS apartments, GROUP_CONCAT(sales.id SEPARATOR ",") AS sales, GROUP_CONCAT(COALESCE(agents.company, "*") SEPARATOR ",") AS agents, GROUP_CONCAT(COALESCE(countries.name, "*") SEPARATOR ",") AS countries')
            ->leftJoin('apartment_status', 'apartment_status.status_id', '=', 'statuses.id')
            ->leftJoin('apartments', 'apartments.id', '=', 'apartment_status.apartment_id')
            ->leftJoin('furniture', 'furniture.id', '=', 'apartments.furniture_id')
            ->leftJoin('sales', function ($join) {
                $join->on('sales.apartment_id', '=', 'apartments.id')->whereNull('sales.deleted_at');
            })
            ->leftJoin('clients', function ($join) {
                $join->on('clients.id', '=', 'sales.client_id')->whereNull('clients.deleted_at');
            })
            ->leftJoin('agents', function ($join) {
                $join->on('agents.id', '=', 'clients.agent_id')/*->whereNull('agents.deleted_at')*/;
            })
            ->leftJoin('countries', 'countries.id', '=', 'clients.country_id')
            ->whereIn('apartments.project_id', Helper::project())
            ->where('apartments.reports', 1)
            ->whereNull('apartments.deleted_at')
            ->where('statuses.parent', 1)
            ->groupBy('statuses.id')
            ->orderBy('statuses.order');

        if ($from || $to) {
            if ($from && $to) {
                $statuses = $statuses->whereRaw('apartment_status.id = (SELECT apartment_status.id FROM apartment_status WHERE DATE(apartment_status.created_at) >= ? AND DATE(apartment_status.created_at) <= ? AND apartment_status.apartment_id = apartments.id ORDER BY apartment_status.created_at DESC LIMIT 1)', [$from, $to]);
            } elseif ($from) {
                $statuses = $statuses->whereRaw('apartment_status.id = (SELECT apartment_status.id FROM apartment_status WHERE DATE(apartment_status.created_at) >= ? AND apartment_status.apartment_id = apartments.id ORDER BY apartment_status.created_at DESC LIMIT 1)', [$from]);
            } elseif ($to) {
                $statuses = $statuses->whereRaw('apartment_status.id = (SELECT apartment_status.id FROM apartment_status WHERE DATE(apartment_status.created_at) <= ? AND apartment_status.apartment_id = apartments.id ORDER BY apartment_status.created_at DESC LIMIT 1)', [$to]);
            }
        } else {
            $statuses = $statuses->whereNull('apartment_status.deleted_at'); // latest/current status
        }

        $statuses = $statuses->get();

        foreach ($statuses as $status) {
            unset($all[$status->id]);
            $apartments[$status->order] = [
                'id' => $status->id,
                'name' => $status->name,
                'total' => $status->total,
                'amount' => in_array($status->id, [10]) ? $status->apartments_price : $status->sales_price,
                'discount' => $status->discount,
                'commission' => $status->commission,
                'apartments' => $status->apartments,
                'sales' => $status->sales,
                'agents' => $status->agents,
                'countries' => $status->countries,
            ];
        }

        foreach ($all as $status) {
            $apartments[$status->order] = [
                'id' => $status->id,
                'name' => $status->name,
                'total' => 0,
                'amount' => 0,
                'discount' => $status->discount,
                'commission' => $status->commission,
                'apartments' => $status->apartments,
                'sales' => $status->sales,
                'agents' => $status->agents,
                'countries' => $status->countries,
            ];
        }

        ksort($apartments);

        return $apartments;
    }

    public function dashboardUnitSales($apartments)
    {
        return collect($apartments)->map(function ($item, $key) {
            if ($item['id'] != '10') {
                return $item['total'];
            }
        })->sum();
    }

    public function dashboardSalesBreakdown($apartments)
    {
        $sales = collect($apartments)->map(function ($item, $key) {
            if ($item['id'] != '10') {
                return $item['total'];
            }
        })->sum();

        $agents = collect($apartments)->map(function ($item, $key) {
            if ($item['id'] != '10') {
                return $item['agents'];
            }
        })->filter()->implode(',');

        $agents = array_count_values(array_filter(explode(',', $agents)));
        $direct = $agents['*'] ?? 0;
        unset($agents['*']);
        $agents = array_sum($agents);

        return [
            'sales' => $sales,
            'direct' => $direct,
            'agents' => $agents,
        ];
    }

    public function dashboardRevenue($apartments)
    {
        return collect($apartments)->map(function ($item, $key) {
            if ($item['id'] != '10') {
                return $item['amount'];
            }
        })->sum();
    }

    public function dashboardRevenueReceived($salesIds)
    {
        return Payment::leftJoin('sales', 'sales.id', '=', 'payments.sale_id')->whereNull('sales.deleted_at')->whereIn('sales.id', $salesIds)->whereIn('sales.project_id', Helper::project())->get()->sum('amount');
    }

    public function dashboardOutstandingRevenue($revenue, $revenueReceived)
    {
        return $revenue > $revenueReceived ? $revenue - $revenueReceived : 0;
    }

    public function dashboardDiscount($apartments)
    {
        return collect($apartments)->map(function ($item, $key) {
            if ($item['id'] != '10') {
                return $item['discount'];
            }
        })->sum();
    }

    public function dashboardCommission($apartments)
    {
        return collect($apartments)->map(function ($item, $key) {
            if ($item['id'] != '10') {
                return $item['commission'];
            }
        })->sum();
    }

    public function dashboardConversion($from, $to, $sales)
    {
        $leads = Status::leftJoin('client_status', 'client_status.status_id', '=', 'statuses.id')
            ->leftJoin('clients', function ($join) {
                $join->on('clients.id', '=', 'client_status.client_id')->whereNull('clients.deleted_at');
            })
            ->where('statuses.conversion', 1);

        if ($from || $to) {
            if ($from && $to) {
                $leads = $leads->whereRaw('client_status.id = (SELECT client_status.id FROM client_status WHERE DATE(client_status.created_at) >= ? AND DATE(client_status.created_at) <= ? AND client_status.client_id = clients.id ORDER BY client_status.created_at DESC LIMIT 1)', [$from, $to]);
            } elseif ($from) {
                $leads = $leads->whereRaw('client_status.id = (SELECT client_status.id FROM client_status WHERE DATE(client_status.created_at) >= ? AND client_status.client_id = clients.id ORDER BY client_status.created_at DESC LIMIT 1)', [$from]);
            } elseif ($to) {
                $leads = $leads->whereRaw('client_status.id = (SELECT client_status.id FROM client_status WHERE DATE(client_status.created_at) <= ? AND client_status.client_id = clients.id ORDER BY client_status.created_at DESC LIMIT 1)', [$to]);
            }
        } else {
            $leads = $leads->whereNull('client_status.deleted_at'); // latest/current status
        }

        $leads = $leads->count('clients.id');

        $rate = 0;
        if ($leads > 0) {
            $rate = round(($sales / $leads) * 100, 2);
        }

        return $rate;
    }

    public function dashboardViewings($from, $to)
    {
        $viewings = Viewing::selectRaw('COUNT(DISTINCT viewings.id) AS total, countries.name AS country, GROUP_CONCAT(viewings.id SEPARATOR ",") AS viewings')
            ->leftJoin('clients', function ($join) {
                $join->on('clients.id', '=', 'viewings.client_id')->whereNull('clients.deleted_at');
            })->leftJoin('countries', 'countries.id', '=', 'clients.country_id')
            ->whereIn('viewings.project_id', Helper::project());

        if ($from || $to) {
            $viewings = $viewings->withTrashed();
            if ($from && $to) {
                $viewings = $viewings->whereDate('viewings.viewed_at', '>=', $from)->whereDate('viewings.viewed_at', '<=', $to);
            } elseif ($from) {
                $viewings = $viewings->whereDate('viewings.viewed_at', '>=', $from);
            } elseif ($to) {
                $viewings = $viewings->whereDate('viewings.viewed_at', '<=', $to);
            }
        }

        $viewings = $viewings->groupBy('countries.id')
            ->orderBy('total', 'DESC')
            ->orderBy('country')
            ->get();

        return $viewings;
    }

    public function dashboardViewingsReasons($apartments, $viewings)
    {
        $bought = collect($apartments)->map(function ($item, $key) {
            if ($item['id'] != '10') {
                return $item['apartments'];
            }
        })->filter()->implode(',');
        $bought = $bought ? explode(',', $bought) : [];

        $viewed = collect($viewings)->map(function ($item, $key) {
            if ($item['id'] != '10') {
                return $item['viewings'];
            }
        })->filter()->implode(',');
        $viewed = $viewed ? explode(',', $viewed) : [];

        $viewingsReasons = Viewing::selectRaw('COUNT(DISTINCT viewings.id) AS total, statuses.name')
            ->leftJoin('apartment_viewing', function ($join) {
                $join->on('apartment_viewing.viewing_id', '=', 'viewings.id')->whereNull('apartment_viewing.deleted_at');
            })
            ->leftJoin('status_viewing', 'status_viewing.viewing_id', '=', 'viewings.id')
            ->leftJoin('statuses', 'statuses.id', '=', 'status_viewing.status_id')
            ->whereIn('viewings.project_id', Helper::project())
            ->whereNotIn('apartment_viewing.apartment_id', $bought)
            ->whereIn('viewings.id', $viewed)
            ->groupBy('statuses.id')
            ->orderBy('total', 'DESC')
            ->limit($this->limit)
            ->get();

        return $viewingsReasons;
    }

    public function dashboardTopSalesCountries($apartments)
    {
        $countries = collect($apartments)->map(function ($item, $key) {
            if ($item['id'] != '10') {
                return $item['countries'];
            }
        })->filter()->implode(',');

        $topSalesCountries = [];
        $countries = array_count_values(array_filter(explode(',', $countries)));
        arsort($countries);
        $countries = array_slice($countries, 0, $this->limit);
        foreach ($countries as $country => $sales) {
            array_push($topSalesCountries, [($country == '*' ? trans('text.none') : $country), $sales]);
        };

        return $topSalesCountries;
    }

    public function dashboardTargets($from, $to, $sales, $revenue)
    {
        $targets = [];

        $target = Target::whereIn('project_id', Helper::project());

        if ($from || $to) {
            $target = $target->select('name', 'start_at', 'end_at', 'sales AS salesTarget', 'revenue AS revenueTarget');

            if ($from && $to) {
                $target = $target->whereRaw('DATE(end_at) >= ? AND DATE(start_at) < ?', [$from, $to]);
            } elseif ($from) {
                $target = $target->whereRaw('DATE(start_at) >= ?', [$from]);
            } elseif ($to) {
                $target = $target->whereRaw('DATE(end_at) <= ?', [$to]);
            }
        } else {
            $target = $target->selectRaw('SUM(sales) AS salesTarget, SUM(revenue) AS revenueTarget');
        }

        $target = $target->get();

        $diff = 0;
        foreach ($target as $value) {
            if ($value->name && count($target) > 1) {
                $from = Carbon::parse($value->start_at)->format('Y-m-d');
                $to = Carbon::parse($value->end_at)->format('Y-m-d');
                $apartments = $this->dashboardApartments($from, $to);
                $sales = $this->dashboardSalesBreakdown($apartments);
                $revenue = $this->dashboardRevenue($apartments);
            }

            $salesPercentage = 0;
            if ($sales['sales'] > 0 && $value->salesTarget > 0) {
                $salesPercentage = round(($sales['sales'] / $value->salesTarget) * 100, 2);
            }

            $diffPercentage = 0;
            if ($sales['sales'] > 0 && $value->salesTarget > 0) {
                $diffPercentage = round(($sales['sales'] / ($value->salesTarget + $diff)) * 100, 2);
            }

            $revenuePercentage = 0;
            if ($revenue > 0 && $value->revenueTarget > 0) {
                $revenuePercentage = round(($revenue / $value->revenueTarget) * 100, 2);
            }

            array_push($targets, [
                'period' => $value->name,
                'salesTarget' => $value->salesTarget,
                'sales' => $sales['sales'],
                'direct' => $sales['direct'],
                'agents' => $sales['agents'],
                'diff' => $diff,
                'salesPercentage' => $salesPercentage,
                'diffPercentage' => $diffPercentage,
                'revenueTarget' => '&euro;' . number_format($value->revenueTarget, 2),
                'revenue' => '&euro;' . number_format($revenue, 2),
                'revenuePercentage' => $revenuePercentage,
            ]);

            $diff += $value->salesTarget - $sales['sales'];

            if ($diff < 0) {
                $diff = 0;
            }
        }

        return $targets;
    }

    public function dashboardTopSalesAgents($apartments)
    {
        $agents = collect($apartments)->map(function ($item, $key) {
            if ($item['id'] != '10') {
                return $item['agents'];
            }
        })->filter()->implode(',');

        $topSalesAgents = [];
        $agents = array_count_values(array_filter(explode(',', $agents)));
        arsort($agents);
        $agents = array_slice($agents, 0, $this->limit);
        foreach ($agents as $agent => $sales) {
            array_push($topSalesAgents, [($agent == '*' ? trans('labels.direct') : $agent), $sales]);
        };

        return $topSalesAgents;
    }

    public function dashboardTopViewingsAgents($from, $to)
    {
        $viewings = Viewing::selectRaw('COUNT(DISTINCT viewings.id) AS total, agents.company AS agent')
            ->leftJoin('clients', function ($join) {
                $join->on('clients.id', '=', 'viewings.client_id')->whereNull('clients.deleted_at');
            })->leftJoin('agents', 'agents.id', '=', 'viewings.agent_id') // clients.agent_id
            ->whereIn('viewings.project_id', Helper::project());

        if ($from || $to) {
            $viewings = $viewings->withTrashed();
            if ($from && $to) {
                $viewings = $viewings->whereDate('viewings.viewed_at', '>=', $from)->whereDate('viewings.viewed_at', '<=', $to);
            } elseif ($from) {
                $viewings = $viewings->whereDate('viewings.viewed_at', '>=', $from);
            } elseif ($to) {
                $viewings = $viewings->whereDate('viewings.viewed_at', '<=', $to);
            }
        }

        $viewings = $viewings->groupBy('agents.id')
            ->orderBy('total', 'DESC')
            ->orderBy('agent')
            ->limit($this->limit)
            ->get();

        $topViewingsAgents = [];
        foreach ($viewings as $viewing) {
            array_push($topViewingsAgents, [($viewing->agent ?: trans('labels.direct')), $viewing->total]);
        };

        return $topViewingsAgents;
    }

    public function dashboardViewingsByDay($from, $to)
    {
        $viewings = Viewing::selectRaw('WEEKDAY(viewed_at) AS day, COUNT(*) AS total')->whereIn('project_id', Helper::project());

        if ($from || $to) {
            $viewings = $viewings->withTrashed();
            if ($from && $to) {
                $viewings = $viewings->whereDate('viewed_at', '>=', $from)->whereDate('viewed_at', '<=', $to);
            } elseif ($from) {
                $viewings = $viewings->whereDate('viewed_at', '>=', $from);
            } elseif ($to) {
                $viewings = $viewings->whereDate('viewed_at', '<=', $to);
            }
        }

        $viewings = $viewings->groupBy(DB::raw('WEEKDAY(viewed_at)'))
            ->orderByRaw('WEEKDAY(viewed_at)')
            ->get();

        $viewingsByDay = [];
        foreach ($viewings as $viewing) {
            array_push($viewingsByDay, [trans('labels.day' . $viewing->day), $viewing->total]);
        };

        return $viewingsByDay;
    }

    public function dashboardTopLeadsAgents()
    {
        $leads = Client::selectRaw('COUNT(clients.id) as total, agents.company')->leftJoin('agents', 'agents.id', '=', 'clients.agent_id')->whereNotNull('clients.agent_id')->groupBy('clients.agent_id')->orderBy('total', 'desc')->pluck('total', 'company');

        $topLeadsAgents = [];
        foreach ($leads->slice(0, $this->limit) as $lead => $total) {
            array_push($topLeadsAgents, [$lead, $total]);
        };

        return $topLeadsAgents;
    }

    public function dashboardClientsCountry($clientsIds)
    {
        $countries = Client::selectRaw('COUNT(clients.id) as total, countries.name')->leftJoin('countries', 'countries.id', '=', 'clients.country_id')->whereIn('clients.id', $clientsIds)->groupBy('clients.country_id')->orderBy('total', 'desc')->pluck('total', 'name');

        $clientsCountry = [];
        foreach ($countries->slice(0, $this->limit) as $country => $clients) {
            array_push($clientsCountry, [($country ?: trans('text.none')), $clients]);
        };

        return $clientsCountry;
    }

    public function dashboardAgentsCountry($agentsIds)
    {
        $countries = Agent::selectRaw('COUNT(agents.id) as total, countries.name')->leftJoin('countries', 'countries.id', '=', 'agents.country_id')->whereIn('agents.id', $agentsIds)->groupBy('agents.country_id')->orderBy('total', 'desc')->pluck('total', 'name');

        $agentsCountry = [];
        foreach ($countries->slice(0, $this->limit) as $country => $agents) {
            array_push($agentsCountry, [($country ?: trans('text.none')), $agents]);
        };

        return $agentsCountry;
    }

    public function dashboardClientsStatus($from, $to)
    {
        $clients = [];

        $all = Status::where('statuses.parent', 2)->get()->keyBy('id');
        $statuses = Status::selectRaw('statuses.id, statuses.order, statuses.name, COUNT(DISTINCT clients.id) AS total, GROUP_CONCAT(clients.id SEPARATOR ", ") AS clients')
            ->leftJoin('client_status', 'client_status.status_id', '=', 'statuses.id')
            ->leftJoin('clients', 'clients.id', '=', 'client_status.client_id')
            ->leftJoin('client_project', 'client_project.client_id', '=', 'clients.id')
            ->whereNull('clients.deleted_at')
            ->where('statuses.parent', 2)
            ->where(function ($query) {
                $query->whereIn('client_project.project_id', Helper::project())->when(!session('project'), function ($q) {
                    return $q->orWhereNull('client_project.project_id');
                });
            })
            ->groupBy('statuses.id')
            ->orderBy('statuses.order');

        if ($from || $to) {
            if ($from && $to) {
                $statuses = $statuses->whereRaw('client_status.id = (SELECT client_status.id FROM client_status WHERE DATE(client_status.created_at) >= ? AND DATE(client_status.created_at) <= ? AND client_status.client_id = clients.id ORDER BY client_status.created_at DESC LIMIT 1)', [$from, $to]);
            } elseif ($from) {
                $statuses = $statuses->whereRaw('client_status.id = (SELECT client_status.id FROM client_status WHERE DATE(client_status.created_at) >= ? AND client_status.client_id = clients.id ORDER BY client_status.created_at DESC LIMIT 1)', [$from]);
            } elseif ($to) {
                $statuses = $statuses->whereRaw('client_status.id = (SELECT client_status.id FROM client_status WHERE DATE(client_status.created_at) <= ? AND client_status.client_id = clients.id ORDER BY client_status.created_at DESC LIMIT 1)', [$to]);
            }
        } else {
            $statuses = $statuses->whereNull('client_status.deleted_at'); // latest/current status
        }

        $statuses = $statuses->get();

        foreach ($statuses as $status) {
            unset($all[$status->id]);
            $clients[$status->order] = [
                'name' => $status->name,
                'total' => $status->total,
                'clients' => $status->clients,
            ];
        }

        foreach ($all as $status) {
            $clients[$status->order] = [
                'name' => $status->name,
                'total' => 0,
                'clients' => '',
            ];
        }

        ksort($clients);

        return $clients;
    }

    public function dashboardAgents($from, $to)
    {
        $agents = [];

        $all = [
            'main' => [
                'order' => 1,
                'name' => trans('labels.main'),
            ],
            'referral' => [
                'order' => 2,
                'name' => trans('labels.referral'),
            ],
            'direct' => [
                'order' => 3,
                'name' => trans('labels.direct'),
            ],
        ];

        $types = Agent::selectRaw('agents.type, COUNT(agents.id) AS total, GROUP_CONCAT(agents.id SEPARATOR ", ") AS agents')->leftJoin('agent_project', 'agent_project.agent_id', '=', 'agents.id')->whereIn('agent_project.project_id', Helper::project())->groupBy('type');

        if ($from || $to) {
            $types = $types->withTrashed();
            if ($from && $to) {
                $types = $types->whereDate('agents.created_at', '>=', $from)->whereDate('agents.created_at', '<=', $to);
            } elseif ($from) {
                $types = $types->whereDate('agents.created_at', '>=', $from);
            } elseif ($to) {
                $types = $types->whereDate('agents.created_at', '<=', $to);
            }
        }

        $types = $types->withTrashed()->get(); // no withTrashed

        foreach ($types as $type) {
            $agents[$all[$type->type]['order']] = [
                'name' => trans('labels.' . $type->type),
                'total' => $type->total,
                'agents' => $type->agents,
            ];
            unset($all[$type->type]);
        }

        foreach ($all as $type) {
            $agents[$type['order']] = [
                'name' => $type['name'],
                'total' => 0,
                'agents' => '',
            ];
        }

        ksort($agents);

        return $agents;
    }

    public function dashboardInvestorsCountry()
    {
        $countries = Investor::selectRaw('COUNT(investors.id) as total, countries.name')->leftJoin('countries', 'countries.id', '=', 'investors.country_id')->groupBy('investors.country_id')->orderBy('total', 'desc')->pluck('total', 'name');

        $investorsCountry = [];
        foreach ($countries->slice(0, $this->limit) as $country => $investors) {
            array_push($investorsCountry, [($country ?: trans('text.none')), $investors]);
        };

        return $investorsCountry;
    }

    public function dashboardInvestorsSource()
    {
        $sources = Investor::selectRaw('COUNT(investors.id) as total, sources.name')->leftJoin('sources', function ($join) {
            $join->on('sources.id', '=', 'investors.source_id')->where('sources.parent', 3);
        })->groupBy('investors.source_id')->orderBy('total', 'desc')->pluck('total', 'name');

        $investorsSource = [];
        foreach ($sources->slice(0, $this->limit) as $source => $investors) {
            array_push($investorsSource, [($source ?: trans('text.none')), $investors]);
        };

        return $investorsSource;
    }

    public function dashboardInvestorsCategory()
    {
        $categories = Investor::selectRaw('COUNT(investors.id) as total, categories.name')->leftJoin('categories', function ($join) {
            $join->on('categories.id', '=', 'investors.category_id')->where('categories.parent', 1);
        })->groupBy('investors.category_id')->orderBy('total', 'desc')->pluck('total', 'name');

        $investorsCategory = [];
        foreach ($categories->slice(0, $this->limit) as $category => $investors) {
            array_push($investorsCategory, [($category ?: trans('text.none')), $investors]);
        };

        return $investorsCategory;
    }

    public function dashboardInvestorsFundSize()
    {
        $fundSizes = Investor::selectRaw('COUNT(investors.id) as total, fund_size.name')->leftJoin('fund_size', 'fund_size.id', '=', 'investors.fund_size_id')->groupBy('investors.fund_size_id')->orderBy('total', 'desc')->pluck('total', 'name');

        $investorsFundSize = [];
        foreach ($fundSizes->slice(0, $this->limit) as $fundSize => $investors) {
            array_push($investorsFundSize, [($fundSize ?: trans('text.none')), $investors]);
        };

        return $investorsFundSize;
    }

    public function dashboardInvestorsInvestmentRange()
    {
        $investmentRanges = Investor::selectRaw('COUNT(investors.id) as total, investment_range.name')->leftJoin('investment_range', 'investment_range.id', '=', 'investors.investment_range_id')->groupBy('investors.investment_range_id')->orderBy('total', 'desc')->pluck('total', 'name');

        $investorsInvestmentRange = [];
        foreach ($investmentRanges->slice(0, $this->limit) as $investmentRange => $investors) {
            array_push($investorsInvestmentRange, [($investmentRange ?: trans('text.none')), $investors]);
        };

        return $investorsInvestmentRange;
    }

    public function reportApartments(Request $request, $export, $slug)
    {
        $data = Apartment::select('apartments.unit', DB::raw('(SELECT statuses.name FROM apartment_status LEFT JOIN statuses ON statuses.id = apartment_status.status_id ' . ($request->input('status_id') ? 'AND statuses.id = ' . $request->input('status_id') : '') . ' WHERE apartment_status.apartment_id = apartments.id AND apartment_status.deleted_at IS NULL ORDER BY apartment_status.created_at DESC LIMIT 1) AS status'), 'beds.name AS bed', 'apartments.apartment_area AS area', 'apartments.price')->leftJoin('beds', 'apartments.bed_id', '=', 'beds.id')->where('apartments.reports', 1)->whereIn('apartments.project_id', Helper::project())->havingRaw('status IS NOT NULL')->get();

        if (!$export) {
            $data = Datatable::suffix($data, 'area', ' m<sup>2</sup>', 'float');
            $data = Datatable::price($data, ['price']);
        }

        return $data;
    }

    public function reportClients(Request $request, $export, $slug)
    {
        $clients = Client::distinct()->with(['country', 'source'])->selectRaw('clients.id, CONCAT(clients.first_name, " ", COALESCE(clients.last_name, "")) AS client, GROUP_CONCAT(DISTINCT apartments.unit SEPARATOR ", ") AS units, clients.email, clients.phone_code, clients.phone_number, clients.country_id, clients.source_id, agents.company AS agent, (SELECT statuses.name FROM client_status LEFT JOIN statuses ON statuses.id = client_status.status_id ' . ($request->input('status_id') ? 'AND statuses.id = ' . $request->input('status_id') : '') . ' WHERE client_status.client_id = clients.id AND client_status.deleted_at IS NULL ORDER BY client_status.created_at DESC LIMIT 1) AS status')->havingRaw('status IS NOT NULL')
            ->leftJoin('agents', 'agents.id', '=', 'clients.agent_id')
            ->leftJoin('client_status', 'client_status.client_id', '=', 'clients.id')
            ->leftJoin('sales', function ($join) {
                $join->on('sales.client_id', '=', 'clients.id')->whereNull('sales.deleted_at');
            })
            ->leftJoin('apartments', 'apartments.id', '=', 'sales.apartment_id')
            ->leftJoin('client_project', 'client_project.client_id', '=', 'clients.id')
            ->where(function ($query) {
                $query->whereIn('client_project.project_id', Helper::project())->when(!session('project'), function ($q) {
                    return $q->orWhereNull('client_project.project_id');
                })->when(session('project'), function ($q) {
                    return $q->whereIn('sales.project_id', Helper::project());
                });
            })
            ->whereNull('apartments.deleted_at')
            ->whereNull('client_status.deleted_at');

        if ($agents = $request->input('agents')) {
            if (in_array(0, $request->input('agents'))) {
                unset($agents[array_search(0, $agents)]);
                $clients = $clients->where(function ($query) use ($agents) {
                    $query->whereIn('clients.agent_id', $agents)->orWhereNull('clients.agent_id');
                });
            } else {
                $clients = $clients->whereIn('clients.agent_id', $agents);
            }
        }

        if ($request->input('sources')) {
            $clients = $clients->whereIn('clients.source_id', $request->input('sources'));
        }

        if ($request->input('countries')) {
            $clients = $clients->whereIn('clients.country_id', $request->input('countries'));
        }

        if ($request->input('dfrom')) {
            if ($request->input('status_id')) {
                $clients = $clients->whereDate('client_status.created_at', '>=', Carbon::parse($request->input('dfrom')));
            } else {
                $clients = $clients->whereDate('clients.created_at', '>=', Carbon::parse($request->input('dfrom')));
            }
        }

        if ($request->input('dto')) {
            if ($request->input('status_id')) {
                $clients = $clients->whereDate('client_status.created_at', '<=', Carbon::parse($request->input('dto')));
            } else {
                $clients = $clients->whereDate('clients.created_at', '<=', Carbon::parse($request->input('dto')));
            }
        }

        $clients = $clients->orderBy('client')->groupBy('clients.id')->get();

        $data = Datatable::relationship($clients, 'country_name', 'country');
        $data = Datatable::relationship($data, 'source_name', 'source');
        $data = Datatable::default($data, 'phone');

        return Datatable::data($data, array_column($this->dColumns($slug), 'id'));
    }

    public function reportInvestors(Request $request, $export, $slug)
    {
        $investors = Investor::distinct()->with(['country', 'source', 'fundSize', 'investmentRange', 'category'])->selectRaw('investors.id, CONCAT(investors.first_name, " ", COALESCE(investors.last_name, "")) AS investor, investors.email, investors.phone_code, investors.phone_number, investors.country_id, investors.source_id, investors.fund_size_id, investors.investment_range_id, investors.category_id, investors.start_at, investors.end_at, GROUP_CONCAT(DISTINCT projects.name SEPARATOR ", ") AS projects')
            ->leftJoin('investor_project', 'investor_project.investor_id', '=', 'investors.id')
            ->leftJoin('projects', 'projects.id', '=', 'investor_project.project_id');

        if ($request->input('projects')) {
            $investors = $investors->whereIn('investor_project.project_id', $request->input('projects'));
        }

        if ($request->input('sources')) {
            $investors = $investors->whereIn('investors.source_id', $request->input('sources'));
        }

        if ($request->input('countries')) {
            $investors = $investors->whereIn('investors.country_id', $request->input('countries'));
        }

        if ($request->input('fundSizes')) {
            $investors = $investors->whereIn('investors.fund_size_id', $request->input('fundSizes'));
        }

        if ($request->input('investmentRanges')) {
            $investors = $investors->whereIn('investors.investment_range_id', $request->input('investmentRanges'));
        }

        if ($request->input('categories')) {
            $investors = $investors->whereIn('investors.category_id', $request->input('categories'));
        }

        if ($request->input('start_at')) {
            $investors = $investors->whereDate('investors.start_at', '<=', Carbon::parse($request->input('start_at')));
        }

        if ($request->input('end_at')) {
            $investors = $investors->whereDate('investors.end_at', '>=', Carbon::parse($request->input('end_at')));
        }

        $investors = $investors->orderBy('investor')->groupBy('investors.id')->get();

        $data = Datatable::relationship($investors, 'country_name', 'country');
        $data = Datatable::relationship($data, 'source_name', 'source');
        $data = Datatable::relationship($data, 'fund_size_name', 'fundSize');
        $data = Datatable::relationship($data, 'investment_range_name', 'investmentRange');
        $data = Datatable::relationship($data, 'category_name', 'category');
        $data = Datatable::default($data, 'phone');
        $data = Datatable::format($data, 'date', 'd.m.Y', 'start_at', 'start');
        $data = Datatable::format($data, 'date', 'd.m.Y', 'end_at', 'end');

        if (!$export) {
            $data = Datatable::render($data, 'start', ['sort' => ['start_at' => 'timestamp']]);
            $data = Datatable::render($data, 'end', ['sort' => ['end_at' => 'timestamp']]);
        }

        return Datatable::data($data, array_merge(array_column($this->dColumns($slug), 'id'), ['phone', 'email']));
    }

    public function reportTasks(Request $request, $export, $slug)
    {
        $tasks = Task::selectRaw('tasks.id, tasks.name, tasks.end_at, tasks.completed_at, tasks.priority_id, tasks.department_id, departments.name AS department, priorities.order, priorities.name AS priority, GROUP_CONCAT(DISTINCT CONCAT(users.first_name, " ", COALESCE(users.last_name, "")) SEPARATOR ", ") as users, (SELECT statuses.name FROM task_status LEFT JOIN statuses ON statuses.id = task_status.status_id ' . ($request->input('status_id') ? 'AND statuses.id = ' . $request->input('status_id') : '') . ' WHERE task_status.task_id = tasks.id AND task_status.deleted_at IS NULL ORDER BY task_status.created_at DESC LIMIT 1) AS status')
            ->havingRaw('status IS NOT NULL')
            ->leftJoin('departments', 'departments.id', '=', 'tasks.department_id')
            ->leftJoin('statuses AS priorities', 'priorities.id', '=', 'tasks.priority_id')
            ->leftJoin('task_status', 'task_status.task_id', '=', 'tasks.id')
            ->leftJoin('task_user', 'task_user.task_id', '=', 'tasks.id')
            ->leftJoin('users', 'users.id', '=', 'task_user.user_id')->when($request->input('users'), function ($query) use ($request) {
                return $query->whereIn('users.id', $request->input('users'));
            })
            ->whereIn('tasks.project_id', Helper::project())
            ->groupBy('tasks.id')
            ->orderBy('tasks.completed_at')
            ->orderBy('tasks.end_at')->get();

        $data = Datatable::format($tasks, 'date', 'd.m.Y', 'end_at', 'deadline');
        $data = Datatable::format($data, 'date', 'd.m.Y', 'completed_at', 'completed');

        if (!$export) {
            $data = Datatable::render($data, 'deadline', ['sort' => ['end_at' => 'timestamp']]);
            $data = Datatable::render($data, 'completed', ['sort' => ['completed_at' => 'timestamp']]);
            return Datatable::data($data, array_merge(array_column($this->dColumns($slug), 'id'), ['order']));
        } else {
            return Datatable::data($data, array_column($this->dColumns($slug), 'id'));
        }
    }

    public function reportDiscount(Request $request, $export, $slug)
    {
        $sales = Sale::selectRaw('apartments.unit, CONCAT(users.first_name, " ", COALESCE(users.last_name, "")) as user, agents.company AS agent, CONCAT(clients.first_name, " ", COALESCE(clients.last_name, "")) AS client, apartments.price, sales.price AS sale_price, sales.furniture, (sales.price + sales.furniture) AS total, ((apartments.price - sales.price) + (COALESCE(furniture.price, "0") - sales.furniture)) AS discount')
            ->leftJoin('users', 'sales.user_id', '=', 'users.id')
            ->leftJoin('clients', 'sales.client_id', '=', 'clients.id')
            ->leftJoin('agents', 'clients.agent_id', '=', 'agents.id')
            ->leftJoin('apartments', 'sales.apartment_id', '=', 'apartments.id')
            ->leftJoin('furniture', 'furniture.id', '=', 'apartments.furniture_id')
            ->whereIn('sales.project_id', Helper::project())
            // ->whereColumn('sales.price', '!=', 'apartments.price')
            ->where('apartments.reports', 1)
            ->whereNull('clients.deleted_at')
            // ->whereNull('agents.deleted_at')
            ->whereNull('apartments.deleted_at')
            ->having('discount', '>', 0);

        if ($agents = $request->input('agents')) {
            if (in_array(0, $request->input('agents'))) {
                unset($agents[array_search(0, $agents)]);
                $sales = $sales->where(function ($query) use ($agents) {
                    $query->whereIn('clients.agent_id', $agents)->orWhereNull('clients.agent_id');
                });
            } else {
                $sales = $sales->whereIn('clients.agent_id', $agents);
            }
        }

        $sales = $sales->get();

        if (!$export) {
            $sales = Datatable::price($sales, ['price', 'sale_price', 'furniture', 'total', 'discount']);
        }

        return $sales;
    }

    public function reportAgentCommissions(Request $request, $export, $slug)
    {
        $sales = Sale::selectRaw('agents.company AS agent, GROUP_CONCAT(apartments.unit SEPARATOR ", ") AS units, SUM(sales.price) AS sale_price, SUM(sales.furniture) AS furniture, SUM(sales.price + sales.furniture) AS total, SUM(sales.commission) AS commission, SUM(sales.sub_commission) AS sub_commission, SUM(sales.commission + sales.sub_commission) AS commissions')
            ->leftJoin('agents', 'sales.agent_id', '=', 'agents.id')
            ->leftJoin('apartments', 'sales.apartment_id', '=', 'apartments.id')
            ->whereIn('sales.project_id', Helper::project())
            ->where('apartments.reports', 1)
            // ->whereNull('agents.deleted_at')
            ->whereNull('apartments.deleted_at');

        if ($agents = $request->input('agents')) {
            if (in_array(0, $request->input('agents'))) {
                unset($agents[array_search(0, $agents)]);
                $sales = $sales->where(function ($query) use ($agents) {
                    $query->whereIn('sales.agent_id', $agents)->orWhereNull('sales.agent_id');
                });
            } else {
                $sales = $sales->whereIn('sales.agent_id', $agents);
            }
        }

        if ($request->input('dfrom')) {
            $sales = $sales->whereDate('sales.created_at', '>=', Carbon::parse($request->input('dfrom')));
        }

        if ($request->input('dto')) {
            $sales = $sales->whereDate('sales.created_at', '<=', Carbon::parse($request->input('dto')));
        }

        $sales = $sales->groupBy('agents.id')->get();

        $data = Datatable::default($sales, 'agent', trans('text.directClient'));

        if (!$export) {
            $data = Datatable::price($data, ['sale_price', 'furniture', 'total', 'commission', 'sub_commission', 'commissions']);
        }

        return $data;
    }

    public function reportSubagentCommissions(Request $request, $export, $slug)
    {
        $sales = Sale::selectRaw('agents.company AS agent, GROUP_CONCAT(apartments.unit SEPARATOR ", ") AS units, SUM(sales.price) AS sale_price, SUM(sales.furniture) AS furniture, SUM(sales.price + sales.furniture) AS total, SUM(sales.sub_commission) AS commission')
            ->leftJoin('agents', 'agents.id', '=', 'sales.subagent_id')
            ->leftJoin('apartments', 'sales.apartment_id', '=', 'apartments.id')
            ->whereIn('sales.project_id', Helper::project())
            ->where('apartments.reports', 1)
            ->where('agents.type', 'direct')
            ->whereNull('agents.deleted_at')
            ->whereNull('apartments.deleted_at');

        if ($agents = $request->input('agents')) {
            $sales = $sales->whereIn('sales.subagent_id', $agents);
        }

        if ($request->input('dfrom')) {
            $sales = $sales->whereDate('sales.created_at', '>=', Carbon::parse($request->input('dfrom')));
        }

        if ($request->input('dto')) {
            $sales = $sales->whereDate('sales.created_at', '<=', Carbon::parse($request->input('dto')));
        }

        $data = $sales->groupBy('agents.id')->get();

        if (!$export) {
            $data = Datatable::price($data, ['sale_price', 'furniture', 'total', 'commission']);
        }

        return $data;
    }

    public function reportSales(Request $request, $export, $slug)
    {
        $sales = Status::selectRaw('CONCAT(projects.name, ", ", projects.location) AS project, apartments.unit, beds.name AS bed, statuses.name AS status, agents.company AS agent, CONCAT(clients.first_name, " ", COALESCE(clients.last_name, "")) AS client, countries.name AS country, sales.price, sales.furniture, SUM(sales.commission) AS commission, SUM(sales.sub_commission) AS sub_commission, (sales.price + sales.furniture) AS total')
            ->leftJoin('apartment_status', 'apartment_status.status_id', '=', 'statuses.id')
            ->leftJoin('apartments', 'apartments.id', '=', 'apartment_status.apartment_id')
            ->leftJoin('beds', 'beds.id', '=', 'apartments.bed_id')
            ->leftJoin('furniture', 'furniture.id', '=', 'apartments.furniture_id')
            ->leftJoin('projects', 'projects.id', '=', 'apartments.project_id')
            ->leftJoin('sales', function ($join) {
                $join->on('sales.apartment_id', '=', 'apartments.id')->whereNull('sales.deleted_at');
            })
            ->leftJoin('clients', function ($join) {
                $join->on('clients.id', '=', 'sales.client_id')->whereNull('clients.deleted_at');
            })
            ->leftJoin('agents', function ($join) {
                $join->on('agents.id', '=', 'clients.agent_id')/*->whereNull('agents.deleted_at')*/;
            })
            ->leftJoin('countries', 'countries.id', '=', 'clients.country_id')
            ->whereIn('sales.project_id', Helper::project())->whereIn('apartments.project_id', Helper::project())
            ->where('apartments.reports', 1)
            ->whereNull('apartments.deleted_at')
            ->where('statuses.parent', 1)
            ->groupBy('statuses.id')
            ->orderBy('projects.name')
            ->orderBy('apartments.unit')
            ->orderBy('statuses.order');

        if ($request->input('statuses')) {
            $sales = $sales->whereIn('statuses.id', $request->input('statuses'));
        }

        if ($request->input('dfrom') || $request->input('dto')) {
            if ($request->input('dfrom') && $request->input('dto')) {
                $sales = $sales->whereRaw('apartment_status.id = (SELECT apartment_status.id FROM apartment_status WHERE DATE(apartment_status.created_at) >= ? AND DATE(apartment_status.created_at) <= ? AND apartment_status.apartment_id = apartments.id and apartment_status.status_id != "10" ORDER BY apartment_status.created_at DESC LIMIT 1)', [Carbon::parse($request->input('dfrom')), Carbon::parse($request->input('dto'))]);
            } elseif ($request->input('dfrom')) {
                $sales = $sales->whereRaw('apartment_status.id = (SELECT apartment_status.id FROM apartment_status WHERE DATE(apartment_status.created_at) >= ? AND apartment_status.apartment_id = apartments.id and apartment_status.status_id != "10" ORDER BY apartment_status.created_at DESC LIMIT 1)', [Carbon::parse($request->input('dfrom'))]);
            } elseif ($request->input('dto')) {
                $sales = $sales->whereRaw('apartment_status.id = (SELECT apartment_status.id FROM apartment_status WHERE DATE(apartment_status.created_at) <= ? AND apartment_status.apartment_id = apartments.id and apartment_status.status_id != "10" ORDER BY apartment_status.created_at DESC LIMIT 1)', [Carbon::parse($request->input('dto'))]);
            }
        } else {
            $sales = $sales->whereNull('apartment_status.deleted_at'); // latest/current status
        }

        if ($agents = $request->input('agents')) {
            if (in_array(0, $request->input('agents'))) {
                unset($agents[array_search(0, $agents)]);
                $sales = $sales->where(function ($query) use ($agents) {
                    $query->whereIn('clients.agent_id', $agents)->orWhereNull('clients.agent_id');
                });
            } else {
                $sales = $sales->whereIn('clients.agent_id', $agents);
            }
        }

        $sales = $sales->groupBy('sales.id')->get();

        if (!$export) {
            $sales = Datatable::price($sales, ['price', 'furniture', 'commission', 'sub_commission', 'total']);
        }

        return Datatable::data($sales, array_column($this->dColumns($slug), 'id'));
    }

    public function reportCancellations(Request $request, $export, $slug)
    {
        $sales = Sale::onlyTrashed()->selectRaw('apartments.unit, agents.company AS agent, CONCAT(clients.first_name, " ", COALESCE(clients.last_name, "")) AS client, countries.name as country, sales.price, sales.furniture, (sales.price + sales.furniture) AS total')
            ->leftJoin('clients', 'sales.client_id', '=', 'clients.id')
            ->leftJoin('agents', 'clients.agent_id', '=', 'agents.id')
            ->leftJoin('apartments', 'sales.apartment_id', '=', 'apartments.id')
            ->leftJoin('countries', 'countries.id', '=', 'clients.country_id')
            /*->leftJoin('payments', function ($join) {
                $join->on('sales.id', '=', 'payments.sale_id')->whereIn('payments.status_id', [19, 33]);
            })*/
            ->whereIn('sales.project_id', Helper::project())
            ->where('apartments.reports', 1)
            ->whereNull('clients.deleted_at')
            ->whereNull('agents.deleted_at')
            ->whereNull('apartments.deleted_at')
            // ->whereNull('payments.deleted_at'),
            ->whereNull('countries.deleted_at');

        if ($agents = $request->input('agents')) {
            if (in_array(0, $request->input('agents'))) {
                unset($agents[array_search(0, $agents)]);
                $sales = $sales->where(function ($query) use ($agents) {
                    $query->whereIn('clients.agent_id', $agents)->orWhereNull('clients.agent_id');
                });
            } else {
                $sales = $sales->whereIn('clients.agent_id', $agents);
            }
        }

        if ($request->input('dfrom')) {
            $sales = $sales->whereDate('sales.created_at', '>=', Carbon::parse($request->input('dfrom')));
        }

        if ($request->input('dto')) {
            $sales = $sales->whereDate('sales.created_at', '<=', Carbon::parse($request->input('dto')));
        }

        $sales = $sales->groupBy('sales.id')->get();

        if (!$export) {
            $sales = Datatable::price($sales, ['price', 'furniture', 'total']);
        }

        return Datatable::data($sales, array_column($this->dColumns($slug), 'id'));
    }

    public function reportClosingDates(Request $request, $export, $slug)
    {
        $sold = Apartment::selectRaw('id, (SELECT statuses.id FROM apartment_status LEFT JOIN statuses ON statuses.id = apartment_status.status_id WHERE apartment_status.apartment_id = apartments.id AND apartment_status.deleted_at IS NULL AND statuses.action = "final-balance" ORDER BY apartment_status.created_at DESC LIMIT 1) AS status')->havingRaw('status IS NOT NULL')->pluck('id');

        $sales = Sale::selectRaw('sales.id, sales.closing_at, sales.promissory_at, sales.price, sales.furniture, (sales.price + sales.furniture) as total, sales.description, CONCAT(apartments.unit, " / ", blocks.name) AS apartment, CONCAT(clients.first_name, " ", COALESCE(clients.last_name, "")) AS client, agents.company AS agent, ((sales.price + sales.furniture) - COALESCE(SUM(payments.amount), 0)) AS balance')
            ->leftJoin('projects', 'projects.id', '=', 'sales.project_id')
            ->leftJoin('apartments', 'apartments.id', '=', 'sales.apartment_id')
            ->leftJoin('blocks', 'apartments.block_id', '=', 'blocks.id')
            ->leftJoin('clients', 'sales.client_id', '=', 'clients.id')
            ->leftJoin('agents', 'clients.agent_id', '=', 'agents.id')
            ->leftJoin('payments', function ($join) {
                $join->on('payments.sale_id', '=', 'sales.id')->whereNull('payments.deleted_at');
            })
            ->whereIn('sales.project_id', Helper::project())
            /*->where(function ($query) {
                $query->whereNotNull('sales.closing_at')->orWhereNotNull('sales.promissory_at');
            })
            ->where(function ($query) {
                $query->whereDate('sales.closing_at', '>=', Carbon::now())->orWhereDate('sales.promissory_at', '>=', Carbon::now());
            })*/
            ->whereNotIn('apartments.id', $sold)
            ->whereNull('projects.deleted_at')
            ->whereNull('apartments.deleted_at')
            ->whereNull('blocks.deleted_at')
            ->whereNull('clients.deleted_at')
            ->whereNull('agents.deleted_at')
            ->groupBy('sales.id');

        if ($agents = $request->input('agents')) {
            if (in_array(0, $request->input('agents'))) {
                unset($agents[array_search(0, $agents)]);
                $sales = $sales->where(function ($query) use ($agents) {
                    $query->whereIn('clients.agent_id', $agents)->orWhereNull('clients.agent_id');
                });
            } else {
                $sales = $sales->whereIn('clients.agent_id', $agents);
            }
        }

        $sales = $sales->get();

        $data = Datatable::format($sales, 'date', 'd.m.Y', 'closing_at', 'closing');
        $data = Datatable::format($data, 'date', 'd.m.Y', 'promissory_at', 'promissory');
        $data = Datatable::default($data, 'agent', trans('text.directClient'));

        if (!$export) {
            $data = Datatable::render($data, 'closing', ['sort' => ['closing_at' => 'timestamp']]);
            $data = Datatable::render($data, 'promissory', ['sort' => ['promissory_at' => 'timestamp']]);
            $data = Datatable::price($data, ['price', 'furniture', 'total', 'balance']);
            $data = Datatable::popover($data, 'apartment');
        }

        return Datatable::data($data, array_merge(array_column($this->dColumns($slug), 'id'), ['description']));
    }

    /*public function reportLeads(Request $request, $export, $slug)
    {
        $statuses = Status::where('conversion', 1)->pluck('id');

        $clients = Client::distinct()->with('country')->selectRaw('clients.id, CONCAT(clients.first_name, " ", COALESCE(clients.last_name, "")) as client, clients.email, clients.phone_code, clients.phone_number, clients.country_id, agents.company AS agent, ' . DB::raw('(SELECT statuses.name FROM client_status LEFT JOIN statuses ON statuses.id = client_status.status_id WHERE client_status.client_id = clients.id ORDER BY client_status.created_at DESC LIMIT 1) AS status'))
            ->leftJoin('client_status', 'client_status.client_id', '=', 'clients.id')
            ->leftJoin('agents', 'agents.id', '=', 'clients.agent_id')
            ->whereNull('agents.deleted_at')
            ->whereIn('client_status.status_id', $statuses);

        if ($agents = $request->input('agents')) {
            if (in_array(0, $request->input('agents'))) {
                unset($agents[array_search(0, $agents)]);
                $clients = $clients->where(function ($query) use ($agents) {
                    $query->whereIn('clients.agent_id', $agents)->orWhereNull('clients.agent_id');
                });
            } else {
                $clients = $clients->whereIn('clients.agent_id', $agents);
            }
        }

        if ($request->input('dfrom')) {
            $clients = $clients->whereDate('client_status.created_at', '>=', Carbon::parse($request->input('dfrom')));
        }

        if ($request->input('dto')) {
            $clients = $clients->whereDate('client_status.created_at', '<=', Carbon::parse($request->input('dto')));
        }

        $clients = $clients->get();

        $data = Datatable::relationship($clients, 'country_name', 'country');
        $data = Datatable::default($data, 'phone');

        return Datatable::data($data, array_column($this->dColumns($slug), 'id'));
    }*/

    public function reportViewings(Request $request, $export, $slug)
    {
        $viewings = Viewing::selectRaw('viewings.viewed_at, viewings.description, GROUP_CONCAT(DISTINCT apartments.unit SEPARATOR ", ") as units, GROUP_CONCAT(DISTINCT statuses.name SEPARATOR ", ") as status, GROUP_CONCAT(DISTINCT CONCAT(clients.first_name, " ", COALESCE(clients.last_name, "")) SEPARATOR ", ") as clients, agents.company AS agent')
            ->leftJoin('clients', 'clients.id', '=', 'viewings.client_id')
            ->leftJoin('apartment_viewing', 'apartment_viewing.viewing_id', '=', 'viewings.id')
            ->leftJoin('apartments', 'apartments.id', '=', 'apartment_viewing.apartment_id')
            ->leftJoin('agents', 'agents.id', '=', 'viewings.agent_id') // clients.agent_id
            ->leftJoin('projects', 'projects.id', '=', 'viewings.project_id')
            ->leftJoin('status_viewing', 'status_viewing.viewing_id', '=', 'viewings.id')
            ->leftJoin('statuses', 'statuses.id', '=', 'status_viewing.status_id')
            ->whereIn('viewings.project_id', Helper::project())
            ->whereNull('clients.deleted_at')
            ->whereNull('apartment_viewing.deleted_at')
            ->whereNull('apartments.deleted_at')
            // ->whereNull('agents.deleted_at')
            ->whereNull('projects.deleted_at')
            ->whereNull('statuses.deleted_at')
            ->groupBy('viewings.id')
            ->orderBy('viewings.viewed_at', 'desc');

        if ($agents = $request->input('agents')) {
            if (in_array(0, $request->input('agents'))) {
                unset($agents[array_search(0, $agents)]);
                $viewings = $viewings->where(function ($query) use ($agents) {
                    $query->whereIn('viewings.agent_id', $agents)->orWhereNull('viewings.agent_id'); // clients.agent_id
                });
            } else {
                $viewings = $viewings->whereIn('agents.id', $agents);
            }
        }

        if ($request->input('clients')) {
            $viewings = $viewings->whereIn('clients.id', $request->input('clients'));
        }

        if ($request->input('apartments')) {
            $viewings = $viewings->whereIn('apartments.id', $request->input('apartments'));
        }

        if ($request->input('statuses')) {
            $viewings = $viewings->whereIn('statuses.id', $request->input('statuses'));
        }

        if ($request->input('dfrom')) {
            $viewings = $viewings->whereDate('viewings.viewed_at', '>=', Carbon::parse($request->input('dfrom')));
        }

        if ($request->input('dto')) {
            $viewings = $viewings->whereDate('viewings.viewed_at', '<=', Carbon::parse($request->input('dto')));
        }

        $viewings = $viewings->get();

        $data = Datatable::format($viewings, 'date', 'd.m.Y', 'viewed_at', 'viewed');

        if (!$export) {
            $data = Datatable::render($data, 'viewed', ['sort' => ['viewed_at' => 'timestamp']]);
            $data = Datatable::popover($data, 'status');
        }

        return Datatable::data($data, array_merge(array_column($this->dColumns($slug), 'id'), ['description']));
    }

    public function reportConversionRate(Request $request, $export, $slug)
    {
        $statuses = Status::where('conversion', 1)->pluck('id');

        $leads = Client::leftJoin('client_status', 'client_status.client_id', '=', 'clients.id')
            ->leftJoin('client_project', 'client_project.client_id', '=', 'clients.id')
            ->where(function ($query) {
                $query->whereIn('client_project.project_id', Helper::project())->when(!session('project'), function ($q) {
                    return $q->orWhereNull('client_project.project_id');
                });
            })
            ->whereIn('client_status.status_id', $statuses)
            ->whereNull('client_status.deleted_at')
            ->distinct('clients.id');

        if ($request->input('dfrom')) {
            $leads = $leads->whereDate('client_status.created_at', '>=', Carbon::parse($request->input('dfrom')));
        }

        if ($request->input('dto')) {
            $leads = $leads->whereDate('client_status.created_at', '<=', Carbon::parse($request->input('dto')));
        }

        $leads = $leads->count('clients.id');

        $sales = Sale::whereIn('sales.project_id', Helper::project())
            /*->leftJoin('payments', function ($join) {
                $join->on('sales.id', '=', 'payments.sale_id')->whereIn('payments.status_id', [19, 33]);
            })*/
            ->leftJoin('apartments', 'apartments.id', '=', 'sales.apartment_id')
            // ->whereNull('payments.deleted_at')
            ->whereNull('apartments.deleted_at')
            ->where('apartments.reports', 1);

        if ($request->input('dfrom')) {
            $sales = $sales->whereDate('sales.created_at', '>=', Carbon::parse($request->input('dfrom')));
        }

        if ($request->input('dto')) {
            $sales = $sales->whereDate('sales.created_at', '<=', Carbon::parse($request->input('dto')));
        }

        $sales = $sales->count();

        $rate = 0;
        if ($leads > 0) {
            $rate = round(($sales / $leads) * 100, 2);
        }

        $data = collect([
            [
                'sales' => $sales,
                'leads' => $leads,
                'rate' => $rate . '%',
            ],
        ]);

        return $data;
    }

    public function reportTargets(Request $request, $export, $slug)
    {
        $data = [];
        $parent_id = Target::whereIn('project_id', Helper::project())->where('name', $request->input('year'))->value('id');

        if ($request->input('targets')) {
            $targets = Target::whereIn('project_id', Helper::project())->where('parent', $parent_id)->whereIn('id', $request->input('targets'))->get();

            foreach ($targets as $target) {
                $sales = Sale::whereIn('sales.project_id', Helper::project())
                /*->leftJoin('payments', function ($join) {
                    $join->on('sales.id', '=', 'payments.sale_id')->whereIn('payments.status_id', [19, 33]);
                })*/
                ->leftJoin('apartments', 'apartments.id', '=', 'sales.apartment_id')
                // ->whereNull('payments.deleted_at')
                ->whereNull('apartments.deleted_at')
                ->where('apartments.reports', 1)
                ->whereDate('sales.created_at', '>=', Carbon::parse($target->start_at))
                ->whereDate('sales.created_at', '<=', Carbon::parse($target->end_at))
                ->distinct('sales.id')
                ->count('sales.id');

                $salesPercentage = 0;
                if ($sales > 0 && $target->sales > 0) {
                    $salesPercentage = round(($sales / $target->sales) * 100, 2);
                }

                $revenue = Sale::selectRaw('SUM(sales.price + sales.furniture) as total')
                ->whereIn('sales.project_id', Helper::project())
                /*->leftJoin('payments', function ($join) {
                    $join->on('sales.id', '=', 'payments.sale_id')->whereIn('payments.status_id', [19, 33]);
                })*/
                ->leftJoin('apartments', 'apartments.id', '=', 'sales.apartment_id')
                // ->whereNull('payments.deleted_at')
                ->whereNull('apartments.deleted_at')
                ->where('apartments.reports', 1)
                ->whereDate('sales.created_at', '>=', Carbon::parse($target->start_at))
                ->whereDate('sales.created_at', '<=', Carbon::parse($target->end_at))
                ->value('total');

                $revenuePercentage = 0;
                if ($revenue > 0 && $target->revenue) {
                    $revenuePercentage = round(($revenue / $target->revenue) * 100, 2);
                }

                array_push($data, [
                    'period' => $target->name,
                    'salesTarget' => $target->sales,
                    'sales' => $sales,
                    'salesPercentage' => $salesPercentage . '%',
                    'revenueTarget' => number_format($target->revenue, 2, '.', ' '),
                    'revenue' => '&euro;' . number_format($revenue, 2, '.', ' '),
                    'revenuePercentage' => $revenuePercentage . '%',
                ]);
            }
        } else {
            $salesTarget = Target::whereIn('project_id', Helper::project())->where('parent', $parent_id)->sum('sales');
            $revenueTarget = Target::whereIn('project_id', Helper::project())->where('parent', $parent_id)->sum('revenue');

            $sales = Sale::whereIn('sales.project_id', Helper::project())
                /*->leftJoin('payments', function ($join) {
                    $join->on('sales.id', '=', 'payments.sale_id')->whereIn('payments.status_id', [19, 33]);
                })*/
                ->leftJoin('apartments', 'apartments.id', '=', 'sales.apartment_id')
                // ->whereNull('payments.deleted_at')
                ->whereNull('apartments.deleted_at')
                ->where('apartments.reports', 1)
                ->whereYear('sales.created_at', $request->input('year'))
                ->distinct('sales.id')
                ->count('sales.id');

            $salesPercentage = 0;
            if ($sales > 0 && $salesTarget > 0) {
                $salesPercentage = round(($sales / $salesTarget) * 100, 2);
            }

            $revenue = Sale::selectRaw('SUM(sales.price + sales.furniture) as total')
                ->whereIn('sales.project_id', Helper::project())
                /*->leftJoin('payments', function ($join) {
                    $join->on('sales.id', '=', 'payments.sale_id')->whereIn('payments.status_id', [19, 33]);
                })*/
                ->leftJoin('apartments', 'apartments.id', '=', 'sales.apartment_id')
                // ->whereNull('payments.deleted_at')
                ->whereNull('apartments.deleted_at')
                ->where('apartments.reports', 1)
                ->whereYear('sales.created_at', $request->input('year'))
                ->value('total');

            $revenuePercentage = 0;
            if ($revenue > 0 && $revenueTarget > 0) {
                $revenuePercentage = round(($revenue / $revenueTarget) * 100, 2);
            }

            array_push($data, [
                'period' => $request->input('year'),
                'salesTarget' => $salesTarget,
                'sales' => $sales,
                'salesPercentage' => $salesPercentage . '%',
                'revenueTarget' => '&euro;' . number_format($revenueTarget, 2, '.', ' '),
                'revenue' => '&euro;' . number_format($revenue, 2, '.', ' '),
                'revenuePercentage' => $revenuePercentage . '%',
            ]);
        }

        $data = collect($data);

        return $data;
    }

    public function export(Request $request, $slug)
    {
        $data = $this->dData($request, $slug, true)->toArray();

        if ($data) {
            $columns = array_column($this->dColumns($slug, true), 'name');
            array_unshift($data, $columns);

            $firstColumn = 'B';
            $firstRow = 2;
            $firstCell = $firstColumn . $firstRow;

            $lastColumn = chr(ord($firstColumn) + count($columns) - 1);
            $lastRow = count($data) + (in_array($slug, ['apartments', 'discount', 'agent-commissions', 'subagent-commissions', 'closing-dates', 'sales', 'cancellations']) ? $firstRow : 1); // footer
            $lastCell = $lastColumn . $lastRow;

            for ($i = $firstColumn; $i <= $lastColumn; $i++) {
                $widths[$i] = -1;
            }

            foreach ($data as $row) {
                $col = $firstColumn;
                foreach ($row as $value) {
                    $width = mb_strlen($value);
                    if ($width > $widths[$col]) {
                        $widths[$col] = $width;
                    }

                    $col++;
                }
            }

            \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder(new \PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder());
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $spreadsheet->getProperties()->setCreator(env('APP_NAME'));
            $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
            $spreadsheet->getDefaultStyle()->getFont()->setSize(11);

            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setShowGridlines(false);
            $sheet->setTitle(trans('labels.report-' . $slug) . ' ' . trans('labels.report'));

            $sheet->fromArray($data, null, $firstCell);

            if (in_array($slug, ['apartments', 'discount', 'agent-commissions', 'subagent-commissions', 'viewings'])) {
                $sheet->getStyle($firstCell . ':' . $firstColumn . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
            }

            if ($slug == 'closing-dates') {
                $sheet->getStyle($firstColumn . ($firstRow + 1) . ':' . $firstColumn . ($lastRow - 1))->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                    'numberFormat' => [
                        'formatCode' => 'dd.mm.yyyy',
                    ],
                ]);
            }

            if ($slug == 'clients') {
                $sheet->getStyle($lastColumn . $firstRow . ':' . $lastCell)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                    'numberFormat' => [
                        'formatCode' => 'dd.mm.yyyy',
                    ],
                ]);

                $sheet->getStyle(chr(ord($firstColumn) + 2) . $firstRow . ':' . $lastCell)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                    ],
                ]);
            }

            if ($slug == 'tasks') {
                $sheet->getStyle(chr(ord($lastColumn) - 3) . $firstRow . ':' . $lastCell)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                $priorities = Status::select('id', 'name', 'order')->where('parent', 5)->get()->keyBy('name')->toArray();

                $priorityColumn = chr(ord($firstColumn) + 4);
                for ($i = 1; $i < count($data); $i++) {
                    $name = $data[$i]['priority'];

                    if (array_key_exists($name, $priorities)) {
                        switch ($priorities[$name]['order']) {
                            case 1:
                                $rgb = 'D4EDDA';
                                break;
                            case 2:
                                $rgb = 'FFECB4';
                                break;
                            case 3:
                                $rgb = 'F1AEB5';
                                break;
                            default:
                                $rgb = null;
                                break;
                        }

                        if ($rgb) {
                            $sheet->getStyle($priorityColumn . ($firstRow + $i))->applyFromArray([
                                'fill' => [
                                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'color' => [
                                        'rgb' => $rgb,
                                    ],
                                ],
                            ]);
                        }
                    }
                }
            }

            if (in_array($slug, ['sales'])) {
                $sheet->getStyle(chr(ord($lastColumn) - 4) . $firstRow . ':' . $lastCell)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],
                    'numberFormat' => [
                        'formatCode' => '"€"#,##0.00_-', // \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
                    ],
                ]);
            }

            if (in_array($slug, ['cancellations'])) {
                $sheet->getStyle(chr(ord($lastColumn) - 2) . $firstRow . ':' . $lastCell)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],
                    'numberFormat' => [
                        'formatCode' => '"€"#,##0.00_-', // \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
                    ],
                ]);
            }

            if ($slug == 'viewings') {
                $sheet->getStyle(chr(ord($lastColumn) - 2) . $firstRow . ':' . chr(ord($lastColumn) - 2) . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                    ],
                ]);
            }

            if ($slug == 'apartments') {
                $sheet->getStyle($lastColumn . $firstRow . ':' . $lastCell)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],
                    'numberFormat' => [
                        'formatCode' => '"€"#,##0.00_-', // \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
                    ],
                ]);

                $sheet->getStyle(chr(ord($lastColumn) - 1) . $firstRow . ':' . chr(ord($lastColumn) - 1) . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],
                    'numberFormat' => [
                        'formatCode' => '0.00', // \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00,
                    ],
                ]);
            }

            if ($slug == 'discount') {
                $sheet->getStyle(chr(ord($lastColumn) - 4) . $firstRow . ':' . $lastCell)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],
                    'numberFormat' => [
                        'formatCode' => '"€"#,##0.00_-', // \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
                    ],
                ]);
            }

            if ($slug == 'agent-commissions') {
                $sheet->getStyle(chr(ord($lastColumn) - 5) . $firstRow . ':' . $lastCell)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],
                    'numberFormat' => [
                        'formatCode' => '"€"#,##0.00_-', // \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
                    ],
                ]);
            }

            if ($slug == 'subagent-commissions') {
                $sheet->getStyle(chr(ord($lastColumn) - 3) . $firstRow . ':' . $lastCell)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],
                    'numberFormat' => [
                        'formatCode' => '"€"#,##0.00_-', // \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
                    ],
                ]);
            }

            if ($slug == 'closing-dates') {
                $sheet->getStyle(chr(ord($lastColumn) - 4) . $firstRow . ':' . chr(ord($lastColumn) - 1) . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],
                    'numberFormat' => [
                        'formatCode' => '"€"#,##0.00_-', // \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
                    ],
                ]);
            }

            if (in_array($slug, ['apartments', 'discount', 'agent-commissions', 'subagent-commissions', 'closing-dates', 'sales', 'cancellations'])) {
                $sheet->getStyle($firstColumn . $lastRow . ':' . $lastCell)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => [
                            'rgb' => 'FFFFFF',
                        ],
                    ],
                    'borders' => [
                        'top' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => [
                                'rgb' => '999999',
                            ]
                        ]
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => [
                            'rgb' => '343A40',
                        ],
                    ],
                ]);

                $sheet->setCellValue($lastCell, '=SUM(' . $lastColumn . ($firstRow + 1) . ':' . $lastColumn . ($lastRow - 1) . ')');
            }

            if ($slug == 'sales') {
                $col = chr(ord($lastColumn) - 1);
                $sheet->setCellValue($col . $lastRow, '=SUM(' . $col . ($firstRow + 1) . ':' . $col . ($lastRow - 1) . ')');

                $col = chr(ord($lastColumn) - 2);
                $sheet->setCellValue($col . $lastRow, '=SUM(' . $col . ($firstRow + 1) . ':' . $col . ($lastRow - 1) . ')');
            }

            if ($slug == 'agent-commissions') {
                $col = chr(ord($lastColumn) - 1);
                $sheet->setCellValue($col . $lastRow, '=SUM(' . $col . ($firstRow + 1) . ':' . $col . ($lastRow - 1) . ')');

                $col = chr(ord($lastColumn) - 2);
                $sheet->setCellValue($col . $lastRow, '=SUM(' . $col . ($firstRow + 1) . ':' . $col . ($lastRow - 1) . ')');

                $col = chr(ord($lastColumn) - 3);
                $sheet->setCellValue($col . $lastRow, '=SUM(' . $col . ($firstRow + 1) . ':' . $col . ($lastRow - 1) . ')');
            }

            if ($slug == 'subagent-commissions') {
                $col = chr(ord($lastColumn) - 1);
                $sheet->setCellValue($col . $lastRow, '=SUM(' . $col . ($firstRow + 1) . ':' . $col . ($lastRow - 1) . ')');
            }

            if ($slug == 'closing-dates') {
                $col = chr(ord($lastColumn) - 1);
                $sheet->setCellValue($col . $lastRow, '=SUM(' . $col . ($firstRow + 1) . ':' . $col . ($lastRow - 1) . ')');

                $col = chr(ord($lastColumn) - 2);
                $sheet->setCellValue($col . $lastRow, '=SUM(' . $col . ($firstRow + 1) . ':' . $col . ($lastRow - 1) . ')');

                $col = chr(ord($lastColumn) - 3);
                $sheet->setCellValue($col . $lastRow, '=SUM(' . $col . ($firstRow + 1) . ':' . $col . ($lastRow - 1) . ')');

                $col = chr(ord($lastColumn) - 4);
                $sheet->setCellValue($col . $lastRow, '=SUM(' . $col . ($firstRow + 1) . ':' . $col . ($lastRow - 1) . ')');
            }

            if (in_array($slug, ['conversion-rate', 'targets'])) {
                $sheet->getStyle($firstCell . ':' . $lastCell)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
            }

            $sheet->getStyle($firstCell . ':' . $lastCell)->applyFromArray([
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                    'indent' => 1,
                ],
                'borders' => [
                    'inside' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => [
                            'rgb' => '999999',
                        ],
                    ],
                ],
            ]);

            $sheet->getStyle($firstCell . ':' . $lastCell)->applyFromArray([
                'borders' => [
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                        'color' => [
                            'rgb' => '999999',
                        ],
                    ],
                ],
            ]);

            $sheet->getStyle($firstCell . ':' . $lastColumn . $firstRow)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => [
                        'rgb' => 'FFFFFF',
                    ],
                ],
                'borders' => [
                    'bottom' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                        'color' => [
                            'rgb' => '999999',
                        ],
                    ],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => [
                        'rgb' => '3853a3',
                    ],
                ],
            ]);

            $sheet->getColumnDimension('A')->setWidth(3);
            for ($i = $firstColumn; $i <= $lastColumn; $i++) {
                // $sheet->getColumnDimension($i)->setAutoSize(true);
                $sheet->getColumnDimension($i)->setWidth($widths[$i] + 4);
            }

            for ($i = $firstRow + 1; $i <= $lastRow - 1; $i++) {
                $sheet->getRowDimension($i)->setRowHeight(20);
            }

            $sheet->getRowDimension($firstRow)->setRowHeight(25);
            $sheet->getRowDimension($lastRow)->setRowHeight(25);

            $sheet->setSelectedCell('A1');
            // $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
            // $sheet->freezePane($firstColumn . ($firstRow + 1));

            $uuid = (string) Str::uuid();

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save(public_path('storage/reports/' . $slug . '-report-' . $uuid . '.xlsx'));

            return back()->with('callback', 'downloadReport')->with('uuid', $uuid);
        } else {
            return back()->withErrors(trans('text.reportDataError'));
        }
    }

    public function download($slug, $uuid = null)
    {
        return Response::download(public_path('storage/reports/' . $slug . '-report-' . $uuid . '.xlsx'), null, ['Cache-Control' => 'no-cache, no-store, must-revalidate', 'Pragma' => 'no-cache', 'Expires' => '0'])->deleteFileAfterSend(true);
    }

    public function viewGoogleAnalytics(Request $request)
    {
        $id = 1; // mespil.ie
        if (!in_array($id, (Auth::user()->can('View All Websites') ? Website::pluck('id') : Auth::user()->websites->pluck('id'))->toArray()) || !Auth::user()->can('View: Analytics')) {
            abort(403);
        }

        $view = 'reports.dashboard.google-analytics';

        $content = View::exists($view) ? view($view)->renderSections() : trans('text.viewError');
        return response()->json([
            'modal' => $content + ['fulscreen' => true],
        ]);
    }

    public function viewGoogleAds(Request $request)
    {
        $id = 1; // mespil.ie
        if (!in_array($id, (Auth::user()->can('View All Websites') ? Website::pluck('id') : Auth::user()->websites->pluck('id'))->toArray()) || !Auth::user()->can('View: Analytics')) {
            abort(403);
        }

        $view = 'reports.dashboard.google-ads';

        $content = View::exists($view) ? view($view)->renderSections() : trans('text.viewError');
        return response()->json([
            'modal' => $content + ['fulscreen' => true],
        ]);
    }

    public function viewGoogleSearchConsole(Request $request)
    {
        $id = 1; // mespil.ie
        if (!in_array($id, (Auth::user()->can('View All Websites') ? Website::pluck('id') : Auth::user()->websites->pluck('id'))->toArray()) || !Auth::user()->can('View: Analytics')) {
            abort(403);
        }

        $view = 'reports.dashboard.google-search-console';

        $content = View::exists($view) ? view($view)->renderSections() : trans('text.viewError');
        return response()->json([
            'modal' => $content + ['fulscreen' => true],
        ]);
    }

    public function viewYoutube(Request $request)
    {
        $id = 1; // mespil.ie
        if (!in_array($id, (Auth::user()->can('View All Websites') ? Website::pluck('id') : Auth::user()->websites->pluck('id'))->toArray()) || !Auth::user()->can('View: Analytics')) {
            abort(403);
        }

        $view = 'reports.dashboard.youtube';

        $content = View::exists($view) ? view($view)->renderSections() : trans('text.viewError');
        return response()->json([
            'modal' => $content + ['fulscreen' => true],
        ]);
    }
}
