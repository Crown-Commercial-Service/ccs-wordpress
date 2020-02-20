<?php
//
//
//
///**
// * V2 (NOT NEEDED)
// */
////add_filter( 'acf/rest_api/{type}/get_fields', function ( $data, $request, $response ) {
////	if ( $response instanceof WP_REST_Response ) {
////		$data = $response->get_data();
////	}
////
////	if ( ! empty( $data ) ) {
////		array_walk_recursive( $data, 'get_fields_recursive' );
////	}
////
////	return $data;
////}, 10, 3 );
//
//
/**
 * V3
 */
/**
 * Define which post-types/taxonomies should get to pass along their ACF fields
 *
 * https://github.com/airesvsg/acf-to-rest-api/issues/109#issuecomment-286403063
 */
add_filter( 'acf/rest_api/page/get_fields', function ( $data ) {
	if ( ! empty( $data ) ) {
		array_walk_recursive( $data, 'get_fields_recursive' );
	}

	return $data;
} );



/**
 * Function
 */
function get_fields_recursive( $item ) {
	if ( is_object( $item ) ) {
		$item->acf = array();
		if ( $fields = get_fields( $item ) ) {
			$item->acf = $fields;
			array_walk_recursive( $item->acf, 'get_fields_recursive' );
		}
	}
}
