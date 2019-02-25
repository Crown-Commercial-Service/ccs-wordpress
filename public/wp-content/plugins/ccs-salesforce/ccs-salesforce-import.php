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
    require __DIR__ . '/cli-commands.php';
}

require __DIR__ . '/PluginCore.php';

require __DIR__ . '/custom-api-endpoints.php';

require __DIR__ . '/merging-wp-data.php';


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


    $api = new CCS_Rest_Api();

    //Get all frameworks
    add_action( 'rest_api_init', function () {
        register_rest_route( 'wp/v2', '/ccs/frameworks', array(
            'methods' => 'GET',
            'callback' => 'get_frameworks_json',
            'permission_callback' => function(){
                return true;
            }
        ) );
    } );

    //Get an individual framework
    add_action( 'rest_api_init', function () {
        register_rest_route( 'css/v1', '/frameworks/(?P<rm_number>[a-zA-Z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => 'get_individual_framework_json',
        ) );
    } );

    //Get upcoming deals
    add_action( 'rest_api_init', function () {
        register_rest_route( 'wp/v2', '/ccs/upcoming-deals', array(
            'methods' => 'GET',
            'callback' => 'get_upcoming_deals',

        ) );
    } );

    add_action( 'rest_api_init', function () use ($api) {
        register_rest_route( 'ccs/v1', '/error', array(
            'methods' => 'GET',
            'callback' => [$api, 'error']

        ) );
    } );

    //Saving wordpress data into the custom database
    add_action( 'save_post', 'save_post_acf' );
}

run_plugin();






