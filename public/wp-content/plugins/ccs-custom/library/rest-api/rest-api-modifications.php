<?php

/**
 * Process taxonomy objects and return only their IDs
 *
 * @param null $apiArrayData
 * @return array|null
 */
function processApiTaxonomyList($apiArrayData = null) {
    $taxonomyIDs = [];

    if(empty($apiArrayData)) {
        return null;
    }

    foreach ($apiArrayData as $item) {
        if(isset($item->term_id)) {
            $taxonomyIDs[] = $item->term_id;
        }
    }

    return $taxonomyIDs;
}


/**
 * Query the REST API internally using an array of post IDs and return the
 * data as an array.
 *
 * Necessary as the REST API returns much more data than WP_Query does
 *
 * @param array $postIds
 * @param string $endpoint
 * @return array
 */
function additionalPostFormatting(array $postIds, $endpoint = '/wp/v2/posts') {
    $request = new WP_REST_Request( 'GET', $endpoint );
    $request->set_query_params( [ 'include' => $postIds ] );
    $response = rest_do_request( $request );
    $server = rest_get_server();
    $data = $server->response_to_data( $response, false );
    $json = wp_json_encode( $data );

    return $data;
}


require('rest-api-whitepapers.php');
require('rest-api-webinars.php');
require('rest-api-featured-news.php');
require('rest-api-featured-events.php');
require('rest-api-downloadable.php');
