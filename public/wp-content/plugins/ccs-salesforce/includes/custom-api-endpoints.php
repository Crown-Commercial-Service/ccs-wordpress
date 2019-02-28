<?php

use App\Repository\FrameworkRepository;
use App\Repository\LotRepository;
use App\Repository\LotSupplierRepository;
use App\Repository\SupplierRepository;


class CCS_Rest_Api {

    /**
     * Return error
     *
     * @todo Log this error?
     *
     * @param string $message Error message
     * @param int $statusCode HTTP response status code
     */
    public function error(string $message = '', int $statusCode = 500)
    {
        $data = json_encode([
            'message' => $message
        ]);

        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo $data;
        exit;
    }

}

/**
 * Endpoint that returns a paginated list of frameworks in a json format
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function get_frameworks(WP_REST_Request $request)
{
    if (isset($request['limit'])) {
        $limit = (int)$request['limit'];
    }
    $limit = $limit ?? 20;

    if (isset($request['page'])) {
        $page = (int)$request['page'];
    }
    $page = $page ?? 0;

    $category = false;

    if (isset($request['category'])) {
        $category = $request['category'];
    }

    $queryCondition = 'published_status = \'publish\' AND (status = \'Live\' OR status = \'Expired - Data Still Received\')';

    //If the category search parameter is defined, add it in the SQL query
    if($category){
        $queryCondition = 'category = \'' . $category . '\' AND ' . $queryCondition;
    }

    $frameworkRepository = new FrameworkRepository();
    $frameworkCount = $frameworkRepository->countAll($queryCondition);
    $frameworks = $frameworkRepository->findAllWhere($queryCondition, true, $limit, $page);

    if ($frameworks === false) {
        $frameworks = [];

    } else {
        foreach ($frameworks as $index => $framework) {

            $frameworks[$index] = $framework->toArray();
            //Delete the last 3 elements from the frameworks array
            unset($frameworks[$index]['document_updates'], $frameworks[$index]['lots'], $frameworks[$index]['documents']);

        }
    }

    $meta = [
        'total_results' => $frameworkCount,
        'limit'         => $limit,
        'results'       => count($frameworks),
        'page'          => $page == 0 ? 1 : $page
    ];


    header('Content-Type: application/json');

    return rest_ensure_response(['meta' => $meta, 'results' => $frameworks]);
}

/**
 * Endpoint that returns an individual framework and the corresponding lots in a json format based on the RM number
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response | WP_Error
 */
function get_individual_framework(WP_REST_Request $request) {

    if (!isset($request['rm_number'])) {
        return new WP_Error( 'bad_request', 'request is invalid', array('status' => 400) );

    }
    $frameworkId = $request['rm_number'];

    $frameworkRepository = new FrameworkRepository();

    // @todo Move the SQL into the framework repository
    $queryCondition = 'rm_number = \'' . $frameworkId . '\' AND published_status = \'publish\' AND (status = \'Live\' OR status = \'Expired - Data Still Received\')';

    $framework = $frameworkRepository->findWhere($queryCondition);
    if ($framework === false) {
        return new WP_Error('rest_invalid_param', 'framework not found', array('status' => 404));
    }

    $lotRepository = new lotRepository();
    $supplierRepository = new SupplierRepository();

    // Find all lots for the retrieved framework
    $lots = $lotRepository->findAllById($framework->getSalesforceId(), 'framework_id');
    $lotsData = [];

    if ($lots === false) {
        $lotsData = [];

    } else {
        $uniqueSuppliers = [];

        foreach ($lots as $lot) {
            $lotData = $lot->toArray();
            $suppliersData = [];

            // Find all suppliers for the retrieved lots
            $suppliers = $supplierRepository->findAllWhere('salesforce_id IN (SELECT supplier_id FROM ccs_lot_supplier where lot_id=\'' . $lot->getSalesforceId() . '\')', false);
            if ($suppliers !== false) {
                foreach ($suppliers as $supplier) {
                    $suppliersData[] = $supplier->toArray();
                    $uniqueSuppliers[] = $supplier->getId();
                }
            }

            $lotData['suppliers'] = $suppliersData;
            $lotsData[$lot->getLotNumber()] = $lotData;
        }
    }

    // Natural sort lots array
    $lotNumbers = array_keys($lotsData);
    natsort($lotNumbers);
    $lotsDataCopy = $lotsData;
    $lotsData = [];
    foreach ($lotNumbers as $number) {
        $lotsData[] = $lotsDataCopy[$number];
    }

    // Get unique count of lot suppliers for a framework
    $uniqueSuppliers = count(array_unique($uniqueSuppliers));

    $frameworkData = $framework->toArray();
    $frameworkData['lots'] = $lotsData;
    $frameworkData['total_suppliers'] = $uniqueSuppliers;

    header('Content-Type: application/json');
    return rest_ensure_response($frameworkData);
}


/**
 * Endpoint that returns a paginated list of upcoming deals in a json format
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function get_upcoming_deals(WP_REST_Request $request)
{

//    if(isset($request['limit']))
//    {
//        $limit = (int) $request['limit'];
//    }
//    $limit = $limit ?? 10;
//
//    if(isset($request['page']))
//    {
//        $page = (int) $request['page'];
//    }
//    $page = $page ?? 0;

    $frameworkRepository = new FrameworkRepository();
    $frameworks = $frameworkRepository->findAll(false);

    $futureFrameworks = [];
    $plannedFrameworks = [];
    $underwayFrameworks = [];
    $awardedFrameworks = [];


    foreach ($frameworks as $framework)
    {
     if(!empty($framework->getStatus())) {

        if($framework->getStatus() === 'Future (Pipeline)'){
            $futureFrameworks[] = $framework->toArray();
        }

         if($framework->getStatus() === 'Planned (Pipeline)'){
             $plannedFrameworks[] = $framework->toArray();
         }

         if($framework->getStatus() === 'Underway (Pipeline)'){
             $underwayFrameworks[] = $framework->toArray();
         }

         if($framework->getStatus() === 'Awarded (Pipeline)'){
             $awardedFrameworks[] = $framework->toArray();
         }
     }

    }

    $meta = [
        'future pipline results' => count($futureFrameworks),
        'planned pipline results'         => count($plannedFrameworks),
        'underway pipline results'       => count($underwayFrameworks),
        'awarded pipline results'          => count($awardedFrameworks)
    ];

    header('Content-Type: application/json');

    return rest_ensure_response(['meta' => $meta,'Future pipeline' => $futureFrameworks, 'Planned pipeline' => $plannedFrameworks,'Underway pipeline' => $underwayFrameworks, 'Awarded pipeline' => $awardedFrameworks ]);
}

