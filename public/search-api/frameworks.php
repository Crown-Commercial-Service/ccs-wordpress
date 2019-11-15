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
    $limit = (int)$_GET['limit'];
}
$limit = $limit ?? 20;

if (isset($_GET['page'])) {
    $page = (int)$_GET['page'];
}
$page = $page ?? 0;

$keyword = '';
if (isset($_GET['keyword'])) {
    $keyword = $_GET['keyword'];
}

$filters = [];

// See examples of filters here. This should be passed from the frontend form
//$filters[] = [
//  'field' => 'city',
//  'value' => 'London'
//];

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