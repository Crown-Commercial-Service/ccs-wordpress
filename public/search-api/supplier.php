<?php

use App\Search\Client;
use Symfony\Component\Dotenv\Dotenv;

$rootDir = __DIR__ . '/../../';

require_once($rootDir . 'vendor/autoload.php');
$dotenv = new Dotenv();
$dotenv->load($rootDir . '.env');

$searchClient = new Client();

$keyword = $_GET['keyword'];

$suppliers = $searchClient->queryIndexByKeyword($searchClient::SUPPLIER_TYPE_NAME, $keyword);

print_r($suppliers);
die();

$meta = [
  'total_results' => null,
  'limit'         => null,
//    'results' => $singleSupplier ? 1 : count($suppliers),
//    'page' => $page == 0 ? 1 : $page
];

header('Content-Type: application/json');

echo json_encode(['meta' => $meta, 'results' => $suppliers]);
exit;