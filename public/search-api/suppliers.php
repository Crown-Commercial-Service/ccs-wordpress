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

// Set empty vars
$dataToReturn = [];
$filters = [];
$keyword = '';
$page = 0;
$limit = 20;

if (isset($_GET['limit']) && !empty($_GET['limit'])) {
    $limit = (int)filter_var($_GET['limit'], FILTER_SANITIZE_STRING);
}

if (isset($_GET['page']) && !empty($_GET['page'])) {
    $page = (int)filter_var($_GET['page'], FILTER_SANITIZE_STRING);
}

if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {
    $keyword = filter_var($_GET['keyword'], FILTER_SANITIZE_STRING);
}

if (isset($_GET['framework']) && !empty($_GET['framework'])) {
    $frameworkRmNumber = filter_var($_GET['framework'], FILTER_SANITIZE_STRING);
    $filters['live_frameworks']['rm_number'] = $frameworkRmNumber;
    $lotRepository = new LotRepository();
    $lots = $lotRepository->findFrameworkLotsAndReturnAllFields($frameworkRmNumber);
}

if (isset($_GET['lot']) && !empty($_GET['lot'])) {
    $lotId = filter_var($_GET['lot'], FILTER_SANITIZE_STRING);
    $filters['live_frameworks']['lot_ids'] = $lotId;
}

// Run the full query we want the results for
$resultSet = $searchClient->queryByKeyword($keyword, $page, $limit, $filters);
$suppliers = $resultSet->getResults();

// We then want to do a separate query to get the facet results we require
$facetSearch = $searchClient->queryByKeyword($keyword, $page, $limit);
$facets = $facetSearch->getAggregations();
// If the lots facet is set, let's add them in
if (isset($lots))
{
    $facets['lots'] = $lots;
}
$buckets = $facetDataResolver->prepareFacetsForView($facets);

/** @var \Elastica\Result $supplier */
foreach ($suppliers as $supplier)
{
    $dataToReturn[] = $supplier->getSource();
}

$meta = [
  'total_results' => $resultSet->getTotalHits(),
  'limit'         => $limit,
  'results'       => count($suppliers),
  'page'          => $page == 0 ? 1 : $page,
  'facets'        => $buckets,
];

header('Content-Type: application/json');

echo json_encode(['meta' => $meta, 'results' => $dataToReturn]);
exit;