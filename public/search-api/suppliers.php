<?php

use App\Search\FacetDataResolver;
use App\Search\SupplierSearchClient;
use Symfony\Component\Dotenv\Dotenv;

$rootDir = __DIR__ . '/../../';

require_once($rootDir . 'vendor/autoload.php');
$dotenv = new Dotenv();
$dotenv->load($rootDir . '.env');

$searchClient = new SupplierSearchClient();
$facetDataResolver = new FacetDataResolver();

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

$filters = [];

if (isset($_GET['framework'])) {
    $frameworkRmNumber = filter_var($_GET['framework'], FILTER_SANITIZE_STRING);
    $filters[] = [
      'field' => 'live_frameworks.rm_number',
      'value' => $frameworkRmNumber
    ];
}

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
$suppliers = $resultSet->getResults();
$buckets = $facetDataResolver->prepareFacetsForView($resultSet->getAggregations());

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
  'page'          => $page == 0 ? 1 : $page,
  'facets'        => $buckets
];

header('Content-Type: application/json');

echo json_encode(['meta' => $meta, 'results' => $supplierDataToReturn]);
exit;