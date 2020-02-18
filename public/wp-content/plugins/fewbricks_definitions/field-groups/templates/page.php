<?php
use fewbricks\bricks AS bricks;
use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;

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
	    ]
    ]
];




/**
* Define the field group
 *
 * Field groups with a lower menu_order will appear first on the edit screens (change by 10,20,30 increments to give yourself space to add)
 */
$field_group = ( new fewacf\field_group( 'Hero', '202001031016a', $location, 10, [
	'position' => 'acf_after_title',
	'names_of_items_to_hide_on_screen' => [
		'excerpt'
	]
]));

/**
 * Define the fields
 */
$field_group->add_brick((new bricks\component_hero('hero', '202001031016b')) );

/*
 * Register the field group
 */
$field_group->register();




$fg5 = ( new fewacf\field_group( 'Lead Text', '201903111200a', $location, 20, [
    'position' => 'acf_after_title',
    'names_of_items_to_hide_on_screen' => [
    ]
] ));

$fg5->add_field( new acf_fields\text( 'Lead Text', 'page_lead_text', '201903111104b', [
    'instructions' => 'Optionally enter some lead text for the page (maximum length, 200 characters)',
    'maxlength' => 200
] ) );

$fg5->register();



$fg6 = ( new fewacf\field_group('Accordion', '201903111208a', $location, 30, [
    'names_of_items_to_hide_on_screen' => [
    ]
]) );

$fg6->add_field(( new acf_fields\repeater('Accordion', 'page_accordion', '201903111209a') )
    ->add_sub_field( new acf_fields\text( 'Title', 'page_accordion_item_title', '201903111210a' ) )
    ->add_sub_field( new acf_fields\wysiwyg( 'Content', 'page_accordion_item_content', '201903111210b' ) )
);

$fg6->register();



$fg8 = ( new fewacf\field_group( 'Full width content', '201903201810a', $location, 40, [
    'names_of_items_to_hide_on_screen' => [
    ]
] ));

$fg8->add_field( new acf_fields\wysiwyg( 'Full width content', 'full_width_content', '201903201810b', [
    'instructions' => 'Optionally enter some content to display at the full-width of the page template',
] ) );

$fg8->register();



/**
 * Define the field group
 *
 * Field groups with a lower menu_order will appear first on the edit screens (change by 10,20,30 increments to give yourself space to add)
 */
$field_group = ( new fewacf\field_group( 'Page components', '202001031015a', $location, 50, [
	'position' => 'normal',
]));

/**
 * Define the fields
 */
$field_group->add_brick((new bricks\group_page_content_default('page_components', '202001031015b')));

/*
 * Register the field group
 */
$field_group->register();
