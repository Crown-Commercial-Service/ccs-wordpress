<?php
use fewbricks\bricks AS bricks;
use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;

// --- Setting components on default page template ---

$location = [
	[
		[
			'param' => 'page_template',
			'operator' => '==',
			'value' => 'page-templates/landing.php'
		]
	]
];




/**
 * Define the field group
 *
 * Field groups with a lower menu_order will appear first on the edit screens (change by 10,20,30 increments to give yourself space to add)
 */
$field_group = ( new fewacf\field_group( 'Hero', '202001081046a', $location, 10, [
	'position' => 'acf_after_title',
	'names_of_items_to_hide_on_screen' => [
		'excerpt'
	]
]));

/**
 * Define the fields
 */
$field_group->add_brick((new bricks\component_hero('hero', '202001081046b')) );

/*
 * Register the field group
 */
$field_group->register();



/**
 * Define the field group
 *
 * Field groups with a lower menu_order will appear first on the edit screens (change by 10,20,30 increments to give yourself space to add)
 */
$field_group = ( new fewacf\field_group( 'Page components', '202001081046c', $location, 50, [
	'position' => 'normal',
]));

/**
 * Define the fields
 */
$field_group->add_brick((new bricks\group_page_content_default('page_components', '202001081046d')));

/*
 * Register the field group
 */
$field_group->register();
