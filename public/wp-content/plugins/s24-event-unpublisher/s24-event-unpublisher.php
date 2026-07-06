<?php
/**
 * Plugin Name:     S24 Event Unpublisher
 * Plugin URI:      http://www.studio24.net/
 * Description:     Changes Events (post-type) entries to drafts after the custom date field is in the past
 * Author:          Studio 24
 * Author URI:      http://www.studio24.net/
 * Text Domain:     s24-event-unpublisher
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package    S24 Events Unpublisher
 */



//$host       = DB_HOST;
//$dbname     = DB_NAME;
//$username   = DB_USER;
//$password   = DB_PASSWORD;
//$connection = new \PDO( "mysql:host=$host;dbname=$dbname", $username, $password );

define("DB_TYPE" ,"mysql");



/**
 * Return all events that have an end date specified in the past
 */
function getAllEventPastEndDates() {

		$dbh = new \PDO(
			DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
			DB_USER,
			DB_PASSWORD
		);

	$dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

	$postsTable     = 'ccs_15423_posts';
	$postsMetaTable = 'ccs_15423_postmeta';

	$sql            = "SELECT DISTINCT $postsTable.ID from $postsTable ";
	$sql            .= "INNER JOIN $postsMetaTable ";
	$sql            .= "ON $postsTable.ID = $postsMetaTable.post_id ";
	$sql            .= "WHERE $postsTable.post_type = 'event' ";
	$sql            .= "AND $postsTable.post_status = 'publish' ";
	$sql            .= "AND $postsMetaTable.meta_value <> '' ";
	$sql            .= "AND $postsMetaTable.meta_key = 'end_datetime' ";
	$sql            .= "AND TIMESTAMP($postsMetaTable.meta_value) <= CURRENT_TIMESTAMP ";

	$query          = $dbh->prepare( $sql );

	$query->execute();

	$results = $query->fetchAll();
	if ( empty( $results ) ) {
		return [];
	}

	$items = [];

	foreach ( $results as $result ) {
		$items[] = $result['ID'];
	}


	return $items;
}



/**
 * Find all events that are now in the past and unpublish them
 */
function unPublishPastEvents() {

	// find all events with an end date, if that date if older than today, unpublish them
	$pastEventsFutureDate = getAllEventPastEndDates();

	foreach ( $pastEventsFutureDate as $postId ) {
		wp_update_post( array(
			'ID'          => $postId,
			'post_status' => 'archived'
		) );
	}
}



// Makes the update function public so it can be installed when the plugin is enabled
if ( ! function_exists( 'update_events_status' ) ) {
	function update_events_status() {
		unPublishPastEvents();
	}
}
add_action( 'check_event_dates', 'update_events_status' );



function scoped_function_name_activate() {
	/**
	 * Schedule cron hooks
	 */
	if ( ! wp_next_scheduled( 'check_event_dates' ) ) {
		wp_schedule_event( time(), 'hourly', 'check_event_dates' );
	}
}



function scoped_function_name_deactivate() {
	wp_clear_scheduled_hook( 'check_event_dates' );
}



register_activation_hook( __FILE__, 'scoped_function_name_activate' );
register_deactivation_hook( __FILE__, 'scoped_function_name_deactivate' );
