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
    $filters['category'] = $category;
}

if (isset($_GET['pillar'])) {
    $pillar = filter_var($_GET['pillar'], FILTER_SANITIZE_STRING);
    $filters['pillar'] = $pillar;
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