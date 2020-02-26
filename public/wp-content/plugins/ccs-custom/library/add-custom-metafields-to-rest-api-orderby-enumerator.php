<?php



// https://github.com/WP-API/WP-API/issues/2308#issuecomment-266214066
// Allow custom metafields in orderby
add_filter( 'rest_endpoints', function ( $routes ) {
	foreach ( array( 'event' ) as $type ) {
		if ( ! ( $route =& $routes[ '/wp/v2/' . $type ] ) ) {
			continue;
		}

		/*
		 * Allow ordering by meta values
		 */
		$route[0]['args']['orderby']['enum'][] = 'start_datetime';

		/*
		 * Allow only specific meta keys
		 *
		 * Otherwise anyone can query any meta-values which opens up security risks
		 *
		 */
		$route[0]['args']['meta_key'] = array(
			'description'       => 'The meta key to query.',
			'type'              => 'string',
			'enum'              => [ 'start_datetime' ],
			'validate_callback' => 'rest_validate_request_arg',
		);
	}

	return $routes;
}, 10, 1 );

/*
 * Manipulate query
 */
add_filter( 'rest_event_query', function ( $args, $request ) {
	$order_key = $request->get_param( 'orderby' );
	if ( ! empty( $order_key ) && $order_key === 'start_datetime' ) {
		$args['meta_key'] = $order_key;
	}

	return $args;
}, 10, 2 );
