<?php
// reference: https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
include 'queries.php';

class ReportsApi {

    function __construct() {
        add_action( 'rest_api_init', array($this, 'restApi') );
    }

    function restApi () {
        // DEV: https://webdev-cms.crowncommercial.gov.uk/wp-json/wp-reports-plugin/v2/authors
        // LOCAL: http://ccs-agreements.cabinetoffice.localhost/wp-json/wp-reports-plugin/v2/authors
        register_rest_route( 'wp-reports-plugin/v2', 'authors', array(
            'methods' => 'GET',
            'callback' => array($this, 'authors')
        ) );
    
        // DEV: https://webdev-cms.crowncommercial.gov.uk/wp-json/wp-reports-plugin/v2/frameworks
        // LOCAL: http://ccs-agreements.cabinetoffice.localhost/wp-json/wp-reports-plugin/v2/frameworks
        register_rest_route( 'wp-reports-plugin/v2', 'frameworks', array(
            'methods' => 'GET',
            'callback' => array($this,'frameworks'),
        ) );
    
        // DEV: https://webdev-cms.crowncommercial.gov.uk/wp-json/wp-reports-plugin/v2/documents/type=frameworks
        // LOCAL: http://ccs-agreements.cabinetoffice.localhost/wp-json/wp-reports-plugin/v2/documents/type=frameworks
        register_rest_route( 'wp-reports-plugin/v2', 'documents/type=(?P<type>[a-zA-Z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => array($this,'documents'),
            // 'args' => array(
            //     'type' => array(
            //       'validate_callback' => function($param, $request, $key) {
            //         return is_string($request['type']);
            //       }
            //     ),
            //   ),
            ) 
        );
    }

    function authors() {
       return authorsQuery();
    }
    
    function frameworks() {
        return frameworksQuery();
    } 
    
    function documents($request) {
        $type = $request['type'];
       return documentsQuery($type);
    }


}

$reportsApi = new ReportsApi();
