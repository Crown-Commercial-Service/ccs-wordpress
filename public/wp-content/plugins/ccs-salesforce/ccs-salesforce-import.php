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

require __DIR__ . '/includes/wp-rest-api/CustomOptionCardsApi.php';

require __DIR__ . '/includes/wp-rest-api/CustomUpcomingDealsApi.php';

require __DIR__ . '/includes/wp-rest-api/CustomHomepageComponentsApi.php';

require __DIR__ . '/includes/wp-rest-api/CustomRedirectionApi.php';

require __DIR__ . '/includes/wp-rest-api/GlossaryApi.php';

require __DIR__ . '/includes/wp-rest-api/cscMessageApi.php';

require __DIR__ . '/includes/wp-rest-api/MessageBannerApi.php';

require __DIR__ . '/includes/wp-rest-api/customNewsApi.php';
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
    $optionCardsApi = new CustomOptionCardsApi();
    $upcomingDealsApi = new CustomUpcomingDealsApi();
    $homepageComponentsApi = new CustomHomepageComponentsApi();
    $redirectionApi = new CustomRedirectionApi();
    $glossaryApi = new GlossaryApi();
    $cscMessageApi = new cscMessageApi();
    $messageBannerApi = new MessageBannerApi();
    $newsApi = new customNewsApi();


    //Get all frameworks
    add_action('rest_api_init', function () use ($frameworkApi) {
        register_rest_route('ccs/v1', '/frameworks', array(
            'methods' => 'GET',
            'callback' => [$frameworkApi, 'get_frameworks']
        ));
    });

    //Get an individual framework
    add_action('rest_api_init', function () use ($frameworkApi) {
        register_rest_route('ccs/v1', '/frameworks/(?P<rm_number>[a-zA-Z0-9-.]+)', array(
            'methods' => 'GET',
            'callback' => [$frameworkApi, 'get_individual_framework']
        ));
    });

     //Get an individual lot
     add_action('rest_api_init', function () use ($frameworkApi) {
        register_rest_route('ccs/v1', '/frameworks/(?P<rm_number>[a-zA-Z0-9-.]+)/lot/(?P<lot_number>[a-zA-Z0-9-.]+)', array(
            'methods' => 'GET',
            'callback' => [$frameworkApi, 'get_individual_lot']
        ));
    });

    //Get suppliers on a framework
    add_action('rest_api_init', function () use ($frameworkApi) {
        register_rest_route('ccs/v1', '/framework-suppliers/(?P<rm_number>[a-zA-Z0-9-.]+)', array(
            'methods' => 'GET',
            'callback' => [$frameworkApi, 'get_framework_suppliers']
        ));
    });

    //Get upcoming deals
    // @todo Had to append "0" to end of URL since Frontend uses getOne() method which expects an ID, to review
    add_action('rest_api_init', function () use ($frameworkApi) {
        register_rest_route('ccs/v1', '/upcoming-deals/0', array(
            'methods' => 'GET',
            'callback' => [$frameworkApi, 'get_upcoming_deals']

        ));
    });

    //Get suppliers on a lot
    add_action('rest_api_init', function () use ($lotApi) {
        register_rest_route('ccs/v1', '/lot-suppliers/(?P<rm_number>[a-zA-Z0-9-.]+)/lot/(?P<lot_number>[a-zA-Z0-9-.]+)', array(
            'methods' => 'GET',
            'callback' => [$lotApi, 'get_lot_suppliers']
        ));
    });


    //Get all suppliers
    add_action('rest_api_init', function () use ($supplierApi) {
        register_rest_route('ccs/v1', '/suppliers', array(
            'methods' => 'GET',
            'callback' => [$supplierApi, 'get_suppliers']
        ));
    });

    //Get an individual supplier
    add_action('rest_api_init', function () use ($supplierApi) {
        register_rest_route('ccs/v1', '/suppliers/(?P<id>[a-zA-Z0-9-.]+)', array(
            'methods' => 'GET',
            'callback' => [$supplierApi, 'get_individual_supplier']
        ));
    });

    //Get the training dates requeired for the eSourcing Training form
    add_action('rest_api_init', function () use ($trainingApi) {
        register_rest_route('ccs/v1', '/esourcing-dates/0', array(
            'methods' => 'GET',
            'callback' => [$trainingApi, 'get_esourcing_dates']
        ));
    });

    //Saving wordpress data into the custom database
    add_action('post_updated', 'updated_post_details', 20, 3);
    add_action('acf/save_post', 'updated_post_meta', 20, 1);


     //Get the option cards data required for all pages
     add_action('rest_api_init', function () use ($optionCardsApi) {
        register_rest_route('ccs/v1', '/option-cards/0', array(
            'methods' => 'GET',
            'callback' => [$optionCardsApi, 'get_option_cards']
        ));
    });

    //Get the upcoming deals data required for upcoming deals page
    add_action('rest_api_init', function () use ($upcomingDealsApi) {
        register_rest_route('ccs/v1', '/upcoming-deals-page/0', array(
            'methods' => 'GET',
            'callback' => [$upcomingDealsApi, 'get_upcoming_deals']
        ));
    });

     //Get the homepage components data required for homepage
     add_action('rest_api_init', function () use ($homepageComponentsApi) {
        register_rest_route('ccs/v1', '/homepage-components/0', array(
            'methods' => 'GET',
            'callback' => [$homepageComponentsApi, 'get_homepage_components']
        ));
    });

    add_action('rest_api_init', function () use ($redirectionApi) {
        register_rest_route('ccs/v1', '/redirections/0', array(
            'methods' => 'GET',
            'callback' => [$redirectionApi, 'getListOfRedirections']
        ));
    
    });

    add_action('rest_api_init', function () use ($glossaryApi) {
        register_rest_route('ccs/v1', '/glossary/0', array(
            'methods' => 'GET',
            'callback' => [$glossaryApi, 'getListOfGlossary']
        ));
    
    });

    add_action('rest_api_init', function () use ($cscMessageApi) {
        register_rest_route('ccs/v1', '/csc-message/0', array(
            'methods' => 'GET',
            'callback' => [$cscMessageApi, 'getMessage']
        ));
    
    });

    add_action('rest_api_init', function () use ($messageBannerApi) {
        register_rest_route('ccs/v1', '/message-banner/0', array(
            'methods' => 'GET',
            'callback' => [$messageBannerApi, 'getMessageBanner']
        ));
    });

    add_action('rest_api_init', function () use ($newsApi) {
        register_rest_route('ccs/v1', '/news', array(
            'methods' => 'GET',
            'callback' => [$newsApi, 'getNewsPageContent']
        ));
    });
}

function import_all()
{
    $import = new Import();
    return $import->all();
}

run_plugin();






