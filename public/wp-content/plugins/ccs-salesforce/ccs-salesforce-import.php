<?php
/**
 * Plugin Name:     CCS Salesforce Importer
 * Plugin URI:      http://www.studio24.net/
 * Description:     Imports required objects from Salesforce into Wordpress
 * Author:          Studio 24
 * Author URI:      http://www.studio24.net/
 * Text Domain:     ccs-salesforce-import
 * Version:         0.1.0
*/

// If this file is called directly, abort.
if (!defined('WPINC')) {
    throw new Exception('You cannot access this file directly');
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
if (class_exists('WP_CLI')) {
    require __DIR__ . '/includes/cli-commands.php';
    require __DIR__ . '/includes/SyncText.php';
}

require __DIR__ . '/includes/PluginCore.php';

require __DIR__ . '/includes/custom-framework-endpoints.php';

require __DIR__ . '/includes/merging-wp-data.php';

require __DIR__ . '/includes/CustomLotAPI.php';



/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1.0
 */
function run_plugin()
{
    $plugin = new PluginCore();

    $lotApi = new CustomLotAPI();

    register_activation_hook(__FILE__, array('PluginCore', 'activate'));
    register_deactivation_hook(__FILE__, array('PluginCore', 'deactivate'));


    $api = new CCS_Rest_Api();

    //Get all frameworks
    add_action( 'rest_api_init', function () {
        register_rest_route( 'ccs/v1', '/frameworks', array(
            'methods' => 'GET',
            'callback' => 'get_frameworks',
        ) );
    } );

    //Get an individual framework
    add_action( 'rest_api_init', function () {
        register_rest_route( 'ccs/v1', '/frameworks/(?P<rm_number>[a-zA-Z0-9-.]+)', array(
            'methods' => 'GET',
            'callback' => 'get_individual_framework',
        ) );
    } );

    //Get suppliers on a framework
    add_action( 'rest_api_init', function () {
        register_rest_route( 'ccs/v1', '/framework-suppliers/(?P<rm_number>[a-zA-Z0-9-.]+)', array(
            'methods' => 'GET',
            'callback' => 'get_framework_suppliers',
        ) );
    } );

    //Get upcoming deals
    add_action( 'rest_api_init', function () {
        register_rest_route( 'ccs/v1', '/upcoming-deals', array(
            'methods' => 'GET',
            'callback' => 'get_upcoming_deals',

        ) );
    } );

    //Get suppliers on a lot
    add_action( 'rest_api_init', function () use ($lotApi) {
        register_rest_route( 'ccs/v1', '/lot-suppliers/(?P<rm_number>[a-zA-Z0-9-.]+):(?P<lot_number>[a-zA-Z0-9-.]+)', array(
            'methods' => 'GET',
            'callback' => [$lotApi, 'get_lot_suppliers']
        ) );
    } );

    //Saving wordpress data into the custom database
    add_action( 'save_post', 'save_post_acf' );
}

run_plugin();






