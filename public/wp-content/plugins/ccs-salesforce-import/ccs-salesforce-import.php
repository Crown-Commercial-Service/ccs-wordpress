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
use App\Repository\FrameworkRepository;

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

function get_framework_json(WP_REST_Request $request)
{
    if(isset($request['limit']))
    {
        $limit = $request['limit'];
    }

    if(isset($request['page']))
    {
        $page = $request['page'];
    }

    $frameworkRepository = new FrameworkRepository();
    $frameworks = $frameworkRepository->findAll(true);

    var_dump($frameworks);
}

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

    add_action( 'rest_api_init', function () {
        register_rest_route( 'wp/v2', '/ccs/frameworks', array(
            'methods' => 'GET',
            'callback' => 'get_framework_json',
        ) );
    } );
}

run_plugin();


