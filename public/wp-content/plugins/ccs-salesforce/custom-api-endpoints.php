<?php

use App\Repository\FrameworkRepository;

/**
 * Endpoint that returns a paginated list of frameworks in a json format
 *
 * @param WP_REST_Request $request
 */
function get_frameworks_json(WP_REST_Request $request)
{
    if(isset($request['limit']))
    {
        $limit = (int) $request['limit'];
    }
    $limit = $limit ?? 10;

    if(isset($request['page']))
    {
        $page = (int) $request['page'];
    }
    $page = $page ?? 0;

    $frameworkRepository = new FrameworkRepository();
    $frameworkCount = $frameworkRepository->countAll();
    $frameworks = $frameworkRepository->findAll(true, $limit, $page);

    foreach ($frameworks as $index => $framework)
    {
        $frameworks[$index] = $framework->toArray();
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
 * Endpoint that returns an individual framework in a json format based on the RM number
 *
 * @param WP_REST_Request $request
*/
function get_individual_framework_json(WP_REST_Request $request) {

    if(isset($request['rm_number']))
    {
        $frameworkId = $request['rm_number'];
    }

    $frameworkRepository = new FrameworkRepository();

    if (!$frameworkRepository->findById($frameworkId, 'rm_number')) {
//        new WP_Error( 'empty_id', 'framework not found', array('status' => 404) );
        return;
    }

    $framework = $frameworkRepository->findById($frameworkId, 'rm_number');

    header('Content-Type: application/json');
    echo json_encode([$framework->toArray()]);
    exit;
}

