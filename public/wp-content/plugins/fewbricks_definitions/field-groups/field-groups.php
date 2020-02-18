<?php

use fewbricks\bricks AS bricks;
use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;


/**
 * Import fields for the framework custom post type
 */
include('post-types/framework.php');

/**
 * Import fields for the lot custom post type
 */
include('post-types/lot.php');

/**
 * Import fields for the supplier custom post type
 */
include('post-types/supplier.php');

/**
 * Import fields for the whitepaper custom post type
 */
include('post-types/whitepaper.php');

/**
 * Import fields for the whitepaper custom post type
 */
include('post-types/webinar.php');

/**
 * Import fields for the event custom post type
 */
include('post-types/event.php');




/**
 * Import fields for the default page template
 */
include('templates/page.php');


/**
 * Import fields for the default post type (news articles)
 */
include('templates/post.php');



/**
 * Import fields for the default post type (news articles)
 */
include('templates/landing.php');



/**
 * Import fields for the default post type (news articles)
 */
include('templates/products-and-services.php');



// --- Setting components on default page template ---

$location = [
    [
        [
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'page'
        ],
        [
            'param'    => 'page_template',
            'operator' => '!=',
            'value'    => 'page-templates/landing.php'
        ],
	    [
		    'param'    => 'page_template',
		    'operator' => '!=',
		    'value'    => 'page-templates/products-and-services.php'
	    ],
	    [
		    'param'    => 'page_template',
		    'operator' => '!=',
		    'value'    => 'page-templates/sectors.php'
	    ]
    ]
];

$fg3 = ( new fewacf\field_group( 'Keywords', '201902201440a', $location, 30 ));

$fg3->add_field( new acf_fields\textarea( 'Keywords', 'framework_keywords', '201902201448a', [
    'instructions' => 'Optionally enter some keywords (separated by comma\'s) which will be used to help ensure accurate search output (maximum combined length, 1000 character)',
    'maxlength' => 1000
] ) );

$fg3->register();
