<?php

use App\Repository\LotRepository;
use App\Search\FacetDataResolver;
use App\Search\FrameworkSearchClient;
use App\Search\SupplierSearchClient;
use Symfony\Component\Dotenv\Dotenv;

$rootDir = __DIR__ . '/../../';

require_once($rootDir . 'vendor/autoload.php');
$dotenv = new Dotenv();
$dotenv->load($rootDir . '.env');

$searchClient = new SupplierSearchClient();
$facetDataResolver = new FacetDataResolver();

if (isset($_GET['limit']) && !empty($_GET['limit'])) {
    $limit = (int)filter_var($_GET['limit'], FILTER_SANITIZE_STRING);
}
$limit = $limit ?? 20;

if (isset($_GET['page']) && !empty($_GET['page'])) {
    $page = (int)filter_var($_GET['page'], FILTER_SANITIZE_STRING);
}
$page = $page ?? 0;

$keyword = '';
if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {
    $keyword = filter_var($_GET['keyword'], FILTER_SANITIZE_STRING);
}

$filters = [];

if (isset($_GET['framework']) && !empty($_GET['framework'])) {
    $frameworkRmNumber = filter_var($_GET['framework'], FILTER_SANITIZE_STRING);
    $filters[] = [
      'field' => 'live_frameworks.rm_number',
      'value' => $frameworkRmNumber
    ];

    $lotRepository = new LotRepository();
    $lots = $lotRepository->findFrameworkLotsAndReturnAllFields($frameworkRmNumber);
}

$frameworkSearchClient = new FrameworkSearchClient();
$frameworks = $frameworkSearchClient->getAll();

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

$facets = $resultSet->getAggregations();
if (isset($lots))
{
    $facets['lots'] = $lots;
}
$buckets = $facetDataResolver->prepareFacetsForView($facets);

// If the lots facet is set, let's add them

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
  'facets'        => $buckets,
  'frameworks'    => $frameworks,
];

header('Content-Type: application/json');

echo json_encode(['meta' => $meta, 'results' => $supplierDataToReturn]);
exit;