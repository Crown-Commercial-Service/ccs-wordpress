<?php

use App\Search\AbstractSearchClient;
use Symfony\Component\Dotenv\Dotenv;

$rootDir = __DIR__ . '/../../';

require_once($rootDir . 'vendor/autoload.php');
$dotenv = new Dotenv();
$dotenv->load($rootDir . '.env');

$searchClient = new AbstractSearchClient();

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

$resultSet = $searchClient->querySupplierIndexByKeyword($searchClient::SUPPLIER_TYPE_NAME, $keyword, $page, $limit, $filters);
$suppliers = $resultSet->getResults();
$buckets = $resultSet->getAggregations();

$supplierDataToReturn = [];

/** @var \Elastica\Result $supplier */
foreach ($suppliers as $supplier)
{
    $supplierDataToReturn[] = $supplier->getSource();
}

$meta = [
  'total_results' => $resultSet->getTotalHits(),
  'limit'         => $limit,
  'results'       => count($suppliers),
  'page'          => $page == 0 ? 1 : $page
];

header('Content-Type: application/json');

echo json_encode(['meta' => $meta, 'results' => $supplierDataToReturn, 'buckets' => $buckets]);
exit;