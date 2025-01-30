<?php

use App\Search\AbstractSearchClient;
use App\Search\FrameworkSearchClient;
use App\Search\SupplierSearchClient;
use App\Repository\FrameworkRepository;
use Symfony\Component\Dotenv\Dotenv;

$rootDir = __DIR__ . '/../../';

require_once($rootDir . 'vendor/autoload.php');
$dotenv = new Dotenv(true);
$dotenv->load($rootDir . '.env');

function filtering($filterName){
    if (!is_array(($_GET[$filterName]))) {
        $filter = filter_var($_GET[$filterName], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    } else {
        foreach ($_GET[$filterName] as $each) {
            $filter[] = $each;
        }
    }

    return $filters[$filterName] = [
        'field'     => $filterName,
        'condition' => 'OR',
        'value'     => $filter
    ];
}

$searchClient = new FrameworkSearchClient();

$liveStatus = ['Live'];
$expiredStatus = ['Expired - Data Still Received'];
$upcomingStatus = ['Future (Pipeline)', 'Planned (Pipeline)', 'Underway (Pipeline)', 'Awarded (Pipeline)'];

// Set empty vars
$dataToReturn = [];
$statuses = [];
$rmNumbers = [];
$sortField = '';
$filters = [];
$keyword = '';
$page = 0;
$limit = 20;

if (isset($_GET['limit'])) {
    $limit = (int)filter_var($_GET['limit'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
}

if (isset($_GET['page'])) {
    $page = (int)filter_var($_GET['page'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
}

if (isset($_GET['keyword'])) {
    $keyword = filter_var($_GET['keyword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
}

if (isset($_GET['sort'])) {
    $sortField = filter_var($_GET['sort'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
}

if (isset($_GET['status'])) {
    if (!is_array(($_GET['status']))) {
        $statuses = filter_var($_GET['status'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    } else {
        foreach ($_GET['status'] as $status) {
            if (strtoupper(filter_var($status, FILTER_SANITIZE_FULL_SPECIAL_CHARS)) == 'ALL') {
                $statuses = array_merge($statuses, $liveStatus);
                $statuses = array_merge($statuses, $expiredStatus);
                $statuses = array_merge($statuses, $upcomingStatus);
                break;
            }else if (strtoupper(filter_var($status, FILTER_SANITIZE_FULL_SPECIAL_CHARS)) == 'EXPIRED') {
                $statuses = array_merge($statuses, $expiredStatus);
            }else if (strtoupper(filter_var($status, FILTER_SANITIZE_FULL_SPECIAL_CHARS)) == 'LIVE') {
                $statuses = array_merge($statuses, $liveStatus);
            }else if (strtoupper(filter_var($status, FILTER_SANITIZE_FULL_SPECIAL_CHARS)) == 'UPCOMING') {
                $statuses = array_merge($statuses, $upcomingStatus);
                $rmNumbers = extractRmNumbersFromUpcoming(get_upcoming_deals());
            }
        }
    }
} else {
    $statuses = array_merge($statuses, $liveStatus);
}

if (!empty($statuses)){
    $filters['status'] = [
        'field'     => 'status',
        'condition' => 'OR',
        'value'     => $statuses
      ];
}

if (isset($_GET['category'])) {
    $filters['category'] = filtering("category");
}

if (isset($_GET['pillar'])) {
    $filters['pillar'] = filtering("pillar");
}

if (isset($_GET['regulation'])) {
    $filters['regulation'] = filtering("regulation");
}

if (isset($_GET['regulation_type'])) {
    $filters['regulation_type'] = filtering("regulation_type");
}

if (isset($_GET['terms'])) {
    $filters['terms'] = filtering("terms");
}

function get_upcoming_deals()
{
    $frameworkRepository = new FrameworkRepository();
    $frameworks = $frameworkRepository->findUpcomingDeals();

    $futureFrameworks = [];
    $plannedFrameworks = [];
    $underwayFrameworks = [];
    $awardedFrameworks = [];
    $dynamicFrameworks = [];


    foreach ($frameworks as $framework) {
        if (!empty($framework->getStatus())) {

            if ($framework->getStatus() === 'Future (Pipeline)') {
                $futureFrameworks[] = $framework->toArray();
            }

            if ($framework->getStatus() === 'Planned (Pipeline)') {
                $plannedFrameworks[] = $framework->toArray();
            }

            if ($framework->getStatus() === 'Underway (Pipeline)') {
                $underwayFrameworks[] = $framework->toArray();
            }

            if ($framework->getStatus() === 'Awarded (Pipeline)' || ($framework->getStatus() === 'Live' &&
                $framework->getTerms() !== 'DPS')) {

                $frameworkExpectedLiveDate = $framework->getExpectedLiveDate();

                if ($frameworkExpectedLiveDate == NULL) {
                    continue;
                } elseif ($framework->getExpectedLiveDate()->format('Y-m-d') > date("Y-m-d")) {
                    $awardedFrameworks[] = $framework->toArray();
                }
            }

            if ($framework->getStatus() === 'Live' && $framework->getTerms() === 'DPS') {
                $dynamicFrameworks[] = $framework->toArray();
            }
        }
    }

    $upcomingAgreements =
    array_merge(
        $futureFrameworks,
        $plannedFrameworks,
        $underwayFrameworks,
        $awardedFrameworks,
        $dynamicFrameworks
    );

    return $upcomingAgreements;
}

function extractRmNumbersFromUpcoming ($upcomingAgreements) {
    $rm_numbers = [];
    // filter by rm number to only include upcoming agreements

    foreach ($upcomingAgreements as $agreement) {
        foreach ($agreement as $filter => $val) {
            if ($filter == "rm_number") {
                $rm_numbers[] = preg_replace("/[^0-9]/", "", $val);
            }
        }
    }

    return $rm_numbers;
}

$resultSet = $searchClient->queryByKeyword($keyword, $page, $limit, $filters, $sortField, $rmNumbers);
$frameworks = $resultSet->getResults();
$buckets = $resultSet->getAggregations();

/** @var \Elastica\Result $supplier */
foreach ($frameworks as $framework)
{
    $dataToReturn[] = $framework->getSource();
}

$meta = [
  'total_results' => $resultSet->getTotalHits(),
  'limit'         => $limit,
  'results'       => count($frameworks),
  'page'          => $page == 0 ? 1 : $page
];

header('Content-Type: application/json');

echo json_encode(['meta' => $meta, 'results' => $dataToReturn, 'buckets' => $buckets]);
exit;