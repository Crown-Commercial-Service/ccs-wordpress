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

if (isset($_GET['limit'])) {
    $limit = (int)filter_var($_GET['limit'], FILTER_SANITIZE_STRING);
}
$limit = $limit ?? 20;

if (isset($_GET['page'])) {
    $page = (int)filter_var($_GET['page'], FILTER_SANITIZE_STRING);
}
$page = $page ?? 0;

$keyword = '';
if (isset($_GET['keyword'])) {
    $keyword = filter_var($_GET['keyword'], FILTER_SANITIZE_STRING);
}

// Reset filters to the empty state
$filters = [];

if (isset($_GET['category'])) {
    $category = filter_var($_GET['category'], FILTER_SANITIZE_STRING);
    $filters[] = [
      'field' => 'category',
      'value' => $category
    ];
}

if (isset($_GET['pillar'])) {
    $pillar = filter_var($_GET['pillar'], FILTER_SANITIZE_STRING);
    $filters[] = [
      'field' => 'pillar',
      'value' => $pillar
    ];
}


// Example of nested filter
//$filters[] = [
//  'field' => 'live_frameworks.title',
//  'value' => 'Management Consultancy Framework (MCF)'
//];

$resultSet = $searchClient->queryByKeyword($keyword, $page, $limit, $filters);
$frameworks = $resultSet->getResults();
$buckets = $resultSet->getAggregations();

$dataToReturn = [];

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