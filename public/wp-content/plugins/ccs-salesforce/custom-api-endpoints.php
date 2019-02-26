<?php

use App\Repository\FrameworkRepository;
use App\Repository\LotRepository;


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

    $queryCondition = 'status = \'Live\' OR status = \'Expired - Data Still Received\'';

    $frameworkRepository = new FrameworkRepository();
    $frameworkCount = $frameworkRepository->countAll($queryCondition);
    $frameworks = $frameworkRepository->findWhere($queryCondition, true, $limit, $page);

    foreach ($frameworks as $index => $framework) {
        $frameworks[$index] = $framework->toArray();

        //Delete the last 3 elements from the frameworks array
        unset($frameworks[$index]['document_updates'], $frameworks[$index]['lots'], $frameworks[$index]['documents']);

    }

    $meta = [
        'total_results' => $frameworkCount,
        'limit'         => $limit,
        'results'       => count($frameworks),
        'page'          => $page == 0 ? 1 : $page
    ];


    header('Content-Type: application/json');
    echo json_encode(['meta' => $meta, 'results' => $frameworks]);
    exit;
}

/**
 * Endpoint that returns an individual framework and its corresponding lots in a json format based on the RM number
 *
 * @param WP_REST_Request $request
*/
function get_individual_framework(WP_REST_Request $request) {

    if (!isset($request['rm_number'])) {
        // @todo error
    }
    $frameworkId = $request['rm_number'];

    $frameworkRepository = new FrameworkRepository();

    if (!$frameworkRepository->findById($frameworkId, 'rm_number')) {
//        new WP_Error( 'empty_id', 'framework not found', array('status' => 404) );
        return;
    }

    $framework = $frameworkRepository->findById($frameworkId, 'rm_number');

    // Find all lots for the retrieved framework
    $lotRepository =  new lotRepository();

    if (!$lotRepository->findAllById($framework->getSalesforceId(), 'framework_id')) {
//        new WP_Error( 'empty_id', 'lot not found', array('status' => 404) );
        return;
    }

    $lots = $lotRepository->findAllById($framework->getSalesforceId(), 'framework_id');

    foreach ($lots as $index => $lot )
    {

        $lots[$index] = $lot->toArray();
    }

    header('Content-Type: application/json');
    echo json_encode(['framework' => $framework->toArray(), 'lots for framework' => $lots]);
    exit;
}


/**
 * Endpoint that returns a paginated list of upcoming deals in a json format
 *
 * @param WP_REST_Request $request
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
    echo json_encode(['meta' => $meta,'Future pipeline' => $futureFrameworks, 'Planned pipeline' => $plannedFrameworks,'Underway pipeline' => $underwayFrameworks, 'Awarded pipeline' => $awardedFrameworks ]);
    exit;
}


