<?php

use App\Search\AbstractSearchClient;
use App\Search\FrameworkSearchClient;
use App\Search\SupplierSearchClient;
use Symfony\Component\Dotenv\Dotenv;

$rootDir = __DIR__ . '/../../';

require_once($rootDir . 'vendor/autoload.php');
$dotenv = new Dotenv();
$dotenv->load($rootDir . '.env');

$searchClient = new FrameworkSearchClient();

// Set empty vars
$dataToReturn = [];
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

if (isset($_GET['category'])) {
    $category = filter_var($_GET['category'], FILTER_SANITIZE_STRING);

    $filters['category'] = [
      'field'     => 'category',
      'condition' => 'AND',
      'value'     => $category
    ];

    if ($category == "Energy"){
        $filters['category'] = [
            'field'     => 'category',
            'condition' => 'OR',
            'value'     => ["Energy", "Utilities & Fuels"]
        ];
    }
}

if (isset($_GET['status'])) {
    if (!is_array(($_GET['status']))) {
        $statuses = filter_var($_GET['status'], FILTER_SANITIZE_STRING);
    } else {
        foreach ($_GET['status'] as $status) {
            if (strtoupper(filter_var($status, FILTER_SANITIZE_STRING)) == 'EXPIRED') {
                $status = 'Expired - Data Still Received';
            } else if (strtoupper(filter_var($status, FILTER_SANITIZE_STRING)) == 'LIVE') {
                $status = 'Live';
            }
            $statuses[] = filter_var($status, FILTER_SANITIZE_STRING);
        }
    }

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


$resultSet = $searchClient->queryByKeyword($keyword, $page, $limit, $filters);
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
