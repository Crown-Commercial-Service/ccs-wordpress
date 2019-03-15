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
use CCS\SFI\Import;

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

require __DIR__ . '/includes/merging-wp-data.php';

require __DIR__ . '/includes/wp-rest-api/CustomFrameworkApi.php';

require __DIR__ . '/includes/wp-rest-api/CustomLotApi.php';

require __DIR__ . '/includes/wp-rest-api/CustomSupplierApi.php';

require __DIR__ . '/includes/wp-rest-api/CustomTrainingApi.php';


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

    register_activation_hook(__FILE__, array('PluginCore', 'activate'));
    register_deactivation_hook(__FILE__, array('PluginCore', 'deactivate'));

    $frameworkApi = new CustomFrameworkApi();
    $lotApi = new CustomLotApi();
    $supplierApi = new CustomSupplierApi();
    $trainingApi = new CustomTrainingApi();


    //Get all frameworks
    add_action( 'rest_api_init', function () use ($frameworkApi) {
        register_rest_route( 'ccs/v1', '/frameworks', array(
            'methods' => 'GET',
            'callback' => [$frameworkApi, 'get_frameworks']
        ) );
    } );

    //Get an individual framework
    add_action( 'rest_api_init', function () use ($frameworkApi) {
        register_rest_route( 'ccs/v1', '/frameworks/(?P<rm_number>[a-zA-Z0-9-.]+)', array(
            'methods' => 'GET',
            'callback' => [$frameworkApi, 'get_individual_framework']
        ) );
    } );

    //Get suppliers on a framework
    add_action( 'rest_api_init', function () use ($frameworkApi) {
        register_rest_route( 'ccs/v1', '/framework-suppliers/(?P<rm_number>[a-zA-Z0-9-.]+)', array(
            'methods' => 'GET',
            'callback' => [$frameworkApi, 'get_framework_suppliers']
        ) );
    } );

    //Get upcoming deals
    // @todo Had to append "0" to end of URL since Frontend uses getOne() method which expects an ID, to review
    add_action( 'rest_api_init', function () use ($frameworkApi) {
        register_rest_route( 'ccs/v1', '/upcoming-deals/0', array(
            'methods' => 'GET',
            'callback' => [$frameworkApi, 'get_upcoming_deals']

        ) );
    } );

    //Get suppliers on a lot
    add_action( 'rest_api_init', function () use ($lotApi) {
        register_rest_route( 'ccs/v1', '/lot-suppliers/(?P<rm_number>[a-zA-Z0-9-.]+)/lot/(?P<lot_number>[a-zA-Z0-9-.]+)', array(
            'methods' => 'GET',
            'callback' => [$lotApi, 'get_lot_suppliers']
        ) );
    } );


    //Get all suppliers
    add_action( 'rest_api_init', function () use ($supplierApi) {
        register_rest_route( 'ccs/v1', '/suppliers', array(
            'methods' => 'GET',
            'callback' => [$supplierApi, 'get_suppliers']
        ) );
    } );

    //Get an individual supplier
    add_action( 'rest_api_init', function () use ($supplierApi) {
        register_rest_route( 'ccs/v1', '/suppliers/(?P<id>[a-zA-Z0-9-.]+)', array(
            'methods' => 'GET',
            'callback' => [$supplierApi, 'get_individual_supplier']
        ) );
    } );

    //Get the training dates requeired for the eSourcing Training form
    add_action( 'rest_api_init', function () use ($trainingApi) {
        register_rest_route( 'ccs/v1', '/esourcing-dates/0', array(
            'methods' => 'GET',
            'callback' => [$trainingApi, 'get_esourcing_dates']
        ) );
    } );

    //Saving wordpress data into the custom database
    add_action( 'post_updated', 'updated_post_details', 20, 3);
    add_action('acf/save_post', 'updated_post_meta', 20, 1);

    add_action( 'ccs_salesforce_import_cron_hook', 'import_all' );
    register_activation_hook( __FILE__, 'ccs_salesforce_activate' );
    register_uninstall_hook( __FILE__, 'ccs_salesforce_deactivate' );
    register_deactivation_hook( __FILE__, 'ccs_salesforce_deactivate' );
}

/**
 * Schedule the cron job when we enable the plugin
 */
function ccs_salesforce_activate()
{
    wp_schedule_event(time(), 'hourly', 'ccs_salesforce_import_cron_hook');
}


/**
 * Disable the cron job when we deactivate or uninstall the plugin
 */
function ccs_salesforce_deactivate()
{
    wp_clear_scheduled_hook('ccs_salesforce_import_cron_hook');
}

function import_all()
{
    $import = new Import();
    return $import->all();
}

run_plugin();






