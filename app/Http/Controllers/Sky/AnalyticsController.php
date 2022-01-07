<?php

namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\Website;
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

class AnalyticsController extends Controller
{
    public $limit = 10;

    public function __invoke(Request $request, $id)
    {
        $api = new Api('websites/' . $id, false);

        if (in_array($id, (Auth::user()->can('View All Websites') ? Website::pluck('id') : Auth::user()->websites->pluck('id'))->toArray()) && Auth::user()->can('View: Analytics')) {

        } else {
            abort(403);
        }

        $currentYear = date('Y');
        $from = null;
        $to = null;

        if ($request->has('from') || $request->has('to')) {
            if ($request->has('from')) {
                $from = $request->input('from');
            }

            if ($request->has('to')) {
                $to = $request->input('to');
            }
        } elseif ($request->has('y')) {
            $from = '01.01.' . $request->input('y');
            $to = '31.12.' . $request->input('y');
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
        } else {
            $from = Carbon::now()->subDays(30);
            $to = Carbon::now()->subDays(1);
        }

        $from = Carbon::parse($from);
        $to = Carbon::parse($to);

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
        $requests[] = $this->createRequest($api->model->analytics, $dateRanges, $metrics);
        $requests[] = $this->createRequest($api->model->analytics, $dateRanges, [$users], $dimensionType);
        $requests[] = $this->createRequest($api->model->analytics, $dateRanges, [$users], $dimensionGender);
        $requests[] = $this->createRequest($api->model->analytics, $dateRanges, [$users], $dimensionAge);
        $requests[] = $this->createRequest($api->model->analytics, $dateRanges, [$sessions], $dimensionCountry/*, $countryOrder*/);

        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests($requests);
        $reportGroups[$api->model->website] = $analytics->reports->batchGet($body);

        $data = $this->getData($reportGroups);

        $values = $data[$api->model->website][0];

        $userTypesTotal = isset($data[$api->model->website][1]['users_total']) ? $data[$api->model->website][1]['users_total']['current'] : 0;
        $userTypes = [
            'total' => $userTypesTotal,
            'data' => [],
        ];
        if (isset($data[$api->model->website][1]['users'])) {
            foreach ($data[$api->model->website][1]['users'] as $key => $value) {
                array_push($userTypes['data'], [$key, round(($value['current'] / $userTypesTotal) * 100, 2), $key . "\n" . round(($value['current'] / $userTypesTotal) * 100, 2) . '% (' . $value['current'] . ')']);
            }
        }

        $gendersTotal = isset($data[$api->model->website][2]['users_total']) ? $data[$api->model->website][2]['users_total']['current'] : 0;
        $genders = [
            'total' => $gendersTotal,
            'percentOfTotal' => (isset($values['users']) ? round(($gendersTotal / $values['users']['current']) * 100, 2) : 0),
            'data' => [],
        ];
        if (isset($data[$api->model->website][2]['users'])) {
            foreach ($data[$api->model->website][2]['users'] as $key => $value) {
                array_push($genders['data'], [$key, round(($value['current'] / $gendersTotal) * 100, 2), $key . "\n" . round(($value['current'] / $gendersTotal) * 100, 2) . '% (' . $value['current'] . ')']);
            }
        }

        $agesTotal = isset($data[$api->model->website][3]['users_total']) ? $data[$api->model->website][3]['users_total']['current'] : 0;
        $ages = [
            'total' => $agesTotal,
            'percentOfTotal' => (isset($values['users']) ? round(($agesTotal / $values['users']['current']) * 100, 2) : 0),
            'data' => [],
        ];
        if (isset($data[$api->model->website][3]['users'])) {
            foreach ($data[$api->model->website][3]['users'] as $key => $value) {
                array_push($ages['data'], [$key, round($value['current'] / $agesTotal, 2), round(($value['current'] / $agesTotal) * 100, 0), round(($value['current'] / $agesTotal) * 100, 2) . '% (' . $value['current'] . ')']);
            }
        }

        $sessionsArray = $data[$api->model->website][4]['sessions'] ?? [];
        $count = count($sessionsArray);
        $sessionsArray = collect($sessionsArray)->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
        $countriesTotal = isset($data[$api->model->website][4]['sessions_total']) ? $data[$api->model->website][4]['sessions_total']['current'] : 0;
        $countries = [
            'total' => $countriesTotal,
            'count' => $count,
            'data' => [],
        ];
        if (isset($sessionsArray)) {
            foreach ($sessionsArray as $key => $value) {
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
        $requests[] = $this->createRequest($api->model->analytics, $dateRange, [$sessions], $dimensionSocialNetwork, null, [$socialNetworkDimensionFilterClause]);
        $requests[] = $this->createRequest($api->model->analytics, $dateRange, [$sessions], $dimensionChannelGrouping);
        $requests[] = $this->createRequest($api->model->analytics, $dateRange, [$sessions], $dimensionKeyword, null, [$keywordDimensionFilterClause]);
        $requests[] = $this->createRequest($api->model->analytics, $dateRange, [$sessions], $dimensionKeyword, null, [$organicKeywordDimensionFilterClause]);
        $requests[] = $this->createRequest($api->model->analytics, $dateRange, [$users], $dimensionSource, null, [$referralDimensionFilterClause]);
        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests($requests);
        $reportGroups[$api->model->website] = $analytics->reports->batchGet($body);

        $data = $this->getData($reportGroups);

        $socialNetworks = [];
        if ($data[$api->model->website][0]) {
            $data[$api->model->website][0]['sessions'] = collect($data[$api->model->website][0]['sessions'])->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
            $socialNetworksTotal = isset($data[$api->model->website][0]['sessions_total']) ? $data[$api->model->website][0]['sessions_total']['current'] : 0;
            $socialNetworks = [
                'total' => $socialNetworksTotal,
                'percentOfTotal' => (isset($values['sessions']) ? round(($socialNetworksTotal / $values['sessions']['current']) * 100, 2) : 0),
                'data' => [],
            ];
            if (isset($data[$api->model->website][0]['sessions'])) {
                foreach ($data[$api->model->website][0]['sessions'] as $key => $value) {
                    array_push($socialNetworks['data'], [$key, round($value['current'] / $socialNetworksTotal, 2), round(($value['current'] / $socialNetworksTotal) * 100, 2) . '% (' . $value['current'] . ')']);
                }
            }
        }

        $channels = [];
        if ($data[$api->model->website][1]) {
            $data[$api->model->website][1]['sessions'] = collect($data[$api->model->website][1]['sessions'])->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
            $channelsTotal = isset($data[$api->model->website][1]['sessions_total']) ? $data[$api->model->website][1]['sessions_total']['current'] : 0;
            $channels = [
                'total' => $channelsTotal,
                'data' => [],
            ];
            if (isset($data[$api->model->website][1]['sessions'])) {
                foreach ($data[$api->model->website][1]['sessions'] as $key => $value) {
                    array_push($channels['data'], [$key, round(($value['current'] / $channelsTotal) * 100, 2), $key . "\n" . round(($value['current'] / $channelsTotal) * 100, 2) . '% (' . $value['current'] . ')']);
                }
            }
        }

        $keywords = [];
        if ($data[$api->model->website][2]) {
            $data[$api->model->website][2]['sessions'] = collect($data[$api->model->website][2]['sessions'])->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
            $keywordsTotal = isset($data[$api->model->website][2]['sessions_total']) ? $data[$api->model->website][2]['sessions_total']['current'] : 0;
            $keywords = [
                'total' => $keywordsTotal,
                'percentOfTotal' => round(($keywordsTotal / $values['sessions']['current']) * 100, 2),
                'data' => [],
            ];
            if (isset($data[$api->model->website][2]['sessions'])) {
                foreach ($data[$api->model->website][2]['sessions'] as $key => $value) {
                    array_push($keywords['data'], [$key, (int)$value['current'], $key . "\n" . $value['current'] . ' (' . round(($value['current'] / $keywordsTotal) * 100, 2) . '%)']);
                }
            }
        }

        $keywordsOrganic = [];
        if ($data[$api->model->website][3]) {
            $data[$api->model->website][3]['sessions'] = collect($data[$api->model->website][3]['sessions'])->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
            $keywordsOrganicTotal = isset($data[$api->model->website][3]['sessions_total']) ? $data[$api->model->website][3]['sessions_total']['current'] : 0;
            $keywordsOrganic = [
                'total' => $keywordsOrganicTotal,
                'percentOfTotal' => round(($keywordsOrganicTotal / $values['sessions']['current']) * 100, 2),
                'data' => [],
            ];
            if (isset($data[$api->model->website][3]['sessions'])) {
                foreach ($data[$api->model->website][3]['sessions'] as $key => $value) {
                    array_push($keywordsOrganic['data'], [$key, (int)$value['current'], $key . "\n" . $value['current'] . ' (' . round(($value['current'] / $keywordsOrganicTotal) * 100, 2) . '%)']);
                }
            }
        }

        $referrals = [];
        if ($data[$api->model->website][4]) {
            $data[$api->model->website][4]['users'] = collect($data[$api->model->website][4]['users'])->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
            $referralsTotal = isset($data[$api->model->website][4]['users_total']) ? $data[$api->model->website][4]['users_total']['current'] : 0;
            $referrals = [
                'total' => $referralsTotal,
                'percentOfTotal' => (isset($values['users']) ? round(($referralsTotal / $values['users']['current']) * 100, 2) : 0),
                'data' => [],
            ];
            if (isset($data[$api->model->website][4]['users'])) {
                foreach ($data[$api->model->website][4]['users'] as $key => $value) {
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
        $requests[] = $this->createRequest($api->model->analytics, $dateRange, [$users], $dimensionSource, null, [$searchEngineDimensionFilterClause]);
        $requests[] = $this->createRequest($api->model->analytics, $dateRange, [$users], $dimensionSource, null, [$searchEngineOrganicDimensionFilterClause]);
        $requests[] = $this->createRequest($api->model->analytics, $dateRange, [$pageviews], $dimensionPagePath);
        $requests[] = $this->createRequest($api->model->analytics, $dateRange, [$entrances], $dimensionlandingPagePath);
        $requests[] = $this->createRequest($api->model->analytics, $dateRange, [$users], $dimensionDeviceCategory);

        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests($requests);
        $reportGroups[$api->model->website] = $analytics->reports->batchGet($body);

        $data = $this->getData($reportGroups);

        $usersArray = $data[$api->model->website][0]['users'] ?? [];
        $usersArray = collect($usersArray)->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
        $searchEnginesTotal = isset($data[$api->model->website][0]['users_total']) ? $data[$api->model->website][0]['users_total']['current'] : 0;
        $searchEngines = [
            'total' => $searchEnginesTotal,
            'percentOfTotal' => (isset($values['users']) ? round(($searchEnginesTotal / $values['users']['current']) * 100, 2) : 0),
            'data' => [],
        ];
        if (isset($usersArray)) {
            foreach ($usersArray as $key => $value) {
                array_push($searchEngines['data'], [$key, (int)$value['current'], $key . "\n" . $value['current'] . ' (' . round(($value['current'] / $searchEnginesTotal) * 100, 2) . '%)']);
            }
        }

        $usersArray = $data[$api->model->website][1]['users'] ?? [];
        $usersArray = collect($usersArray)->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
        $searchEnginesOrganicTotal = isset($data[$api->model->website][1]['users_total']) ? $data[$api->model->website][1]['users_total']['current'] : 0;
        $searchEnginesOrganic = [
            'total' => $searchEnginesOrganicTotal,
            'percentOfTotal' => (isset($values['users']) ? round(($searchEnginesOrganicTotal / $values['users']['current']) * 100, 2) : 0),
            'data' => [],
        ];
        if (isset($usersArray)) {
            foreach ($usersArray as $key => $value) {
                array_push($searchEnginesOrganic['data'], [$key, (int)$value['current'], $key . "\n" . $value['current'] . ' (' . round(($value['current'] / $searchEnginesOrganicTotal) * 100, 2) . '%)']);
            }
        }

        $pageviewsArray = $data[$api->model->website][2]['pageviews'] ?? [];
        $pageviewsArray = collect($pageviewsArray)->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
        $pagePathTotal = isset($data[$api->model->website][2]['pageviews_total']) ? $data[$api->model->website][2]['pageviews_total']['current'] : 0;
        $pagePath = [
            'total' => $pagePathTotal,
            'data' => [],
        ];
        if (isset($pageviewsArray)) {
            foreach ($pageviewsArray as $key => $value) {
                array_push($pagePath['data'], [$key, (int)$value['current'], $key . "\n" . $value['current'] . ' (' . round(($value['current'] / $pagePathTotal) * 100, 2) . '%)']);
            }
        }

        $entrancesArray = $data[$api->model->website][3]['entrances'] ?? [];
        $entrancesArray = collect($entrancesArray)->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
        $landingPagePathTotal = isset($data[$api->model->website][3]['entrances_total']) ? $data[$api->model->website][3]['entrances_total']['current'] : 0;
        $landingPagePath = [
            'total' => $landingPagePathTotal,
            'data' => [],
        ];
        if (isset($entrancesArray)) {
            foreach ($entrancesArray as $key => $value) {
                array_push($landingPagePath['data'], [$key, (int)$value['current'], $key . "\n" . $value['current'] . ' (' . round(($value['current'] / $landingPagePathTotal) * 100, 2) . '%)']);
            }
        }

        $usersArray = $data[$api->model->website][4]['users'] ?? [];
        $usersArray = collect($usersArray)->sortByDesc('current')->slice(0, 7)->where('current', '>', 0)->all();
        $deviceCategoryTotal = isset($data[$api->model->website][4]['users_total']) ? $data[$api->model->website][4]['users_total']['current'] : 0;
        $deviceCategory = [
            'total' => $deviceCategoryTotal,
            'data' => [],
        ];
        if (isset($usersArray)) {
            foreach ($usersArray as $key => $value) {
                array_push($deviceCategory['data'], [$key, round(($value['current'] / $deviceCategoryTotal) * 100, 2), $key . "\n" . round(($value['current'] / $deviceCategoryTotal) * 100, 2) . '% (' . $value['current'] . ')']);
            }
        }

        $parameters = ['api', 'analytics', 'values', 'userTypes', 'genders', 'ages', 'countries', 'socialNetworks', 'channels', 'keywords', 'keywordsOrganic', 'referrals', 'searchEngines', 'searchEnginesOrganic', 'pagePath', 'landingPagePath', 'deviceCategory'];

        return view('website.analytics', compact($parameters));
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
                        $percentChange = ($data[$entry->getName()]['current'] && $values[$j] > 0.001) ? round((($data[$entry->getName()]['current'] - $values[$j]) / $values[$j]) * 100, 2) : 0;
                        $data[$entry->getName()]['percentChange'] = $percentChange;
                    } else {
                        $data[$entry->getName()][$i] = $values[$j];
                    }
                }
            }
        }

        return $data;
    }
}
