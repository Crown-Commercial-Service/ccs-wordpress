<?php

use App\Search\AbstractSearchClient;
use App\Search\FrameworkSearchClient;
use App\Search\SupplierSearchClient;
use Symfony\Component\Dotenv\Dotenv;

$rootDir = __DIR__ . '/../../';

require_once($rootDir . 'vendor/autoload.php');
$dotenv = new Dotenv(true);
$dotenv->load($rootDir . '.env');

function filtering($filterName){
    if (!is_array(($_GET[$filterName]))) {
        $filter = filter_var($_GET[$filterName], FILTER_SANITIZE_STRING);
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
$sortField = '';
$filters = [];
$keyword = '';
$page = 0;
$limit = 20;

if (isset($_GET['limit'])) {
    $limit = (int)filter_var($_GET['limit'], FILTER_SANITIZE_STRING);
}

if (isset($_GET['page'])) {
    $page = (int)filter_var($_GET['page'], FILTER_SANITIZE_STRING);
}

if (isset($_GET['keyword'])) {
    $keyword = filter_var($_GET['keyword'], FILTER_SANITIZE_STRING);
}

if (isset($_GET['sort'])) {
    $sortField = filter_var($_GET['sort'], FILTER_SANITIZE_STRING);
}

if (isset($_GET['category'])) {
    $category = filter_var($_GET['category'], FILTER_SANITIZE_STRING);

    $filters['category'] = [
      'field'     => 'category',
      'condition' => 'AND',
      'value'     => $category
    ];

    if ($category == "Office and Travel"){
        $filters['category'] = [
            'field'     => 'category',
            'condition' => 'OR',
            'value'     => ["Office and Travel", "Travel"]
        ];
    }
}

if (isset($_GET['status'])) {
    if (!is_array(($_GET['status']))) {
        $statuses = filter_var($_GET['status'], FILTER_SANITIZE_STRING);
    } else {
        foreach ($_GET['status'] as $status) {
            if (strtoupper(filter_var($status, FILTER_SANITIZE_STRING)) == 'ALL') {
                $statuses = array_merge($statuses, $liveStatus);
                $statuses = array_merge($statuses, $expiredStatus);
                $statuses = array_merge($statuses, $upcomingStatus);
                break;
            }else if (strtoupper(filter_var($status, FILTER_SANITIZE_STRING)) == 'EXPIRED') {
                $statuses = array_merge($statuses, $expiredStatus);
            }else if (strtoupper(filter_var($status, FILTER_SANITIZE_STRING)) == 'LIVE') {
                $statuses = array_merge($statuses, $liveStatus);
            }else if (strtoupper(filter_var($status, FILTER_SANITIZE_STRING)) == 'UPCOMING') {
                $statuses = array_merge($statuses, $upcomingStatus);
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

if (isset($_GET['pillar'])) {
    if (!is_array(($_GET['pillar']))) {
        $categories = filter_var($_GET['pillar'], FILTER_SANITIZE_STRING);
    } else {
        foreach ($_GET['pillar'] as $category) {
            $categories[] = filter_var($category, FILTER_SANITIZE_STRING);
        }
    }

    $filters['pillar'] = [
      'field'     => 'pillar',
      'condition' => 'OR',
      'value'     => $categories
    ];
}

if (isset($_GET['regulation'])) {
    $filters['regulation'] = filtering("regulation");
}

if (isset($_GET['regulation_type'])) {
    $filters['regulation_type'] = filtering("regulation_type");
}

$resultSet = $searchClient->queryByKeyword($keyword, $page, $limit, $filters, $sortField);
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