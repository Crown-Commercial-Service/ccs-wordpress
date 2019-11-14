<?php

use App\Search\Client;
use Symfony\Component\Dotenv\Dotenv;

$rootDir = __DIR__ . '/../../';

require_once($rootDir . 'vendor/autoload.php');
$dotenv = new Dotenv();
$dotenv->load($rootDir . '.env');

$searchClient = new Client();

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

$resultSet = $searchClient->querySupplierIndexByKeyword($searchClient::SUPPLIER_TYPE_NAME, $keyword, $page, $limit);
$suppliers = $resultSet->getResults();

print_r($suppliers);
die();

$supplierDataToReturn = [];

/** @var \Elastica\Result $supplier */
foreach ($suppliers as $supplier)
{
    $supplierDataToReturn[] = $supplier->getSource();
}

// TODO: Need the array to be in the correct order, but just getting it to work for now.

$meta = [
  'total_results' => $resultSet->getTotalHits(),
  'limit'         => $limit,
  'results'       => count($suppliers),
  'page'          => $page == 0 ? 1 : $page
];

header('Content-Type: application/json');

echo json_encode(['meta' => $meta, 'results' => $supplierDataToReturn]);
exit;