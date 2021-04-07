<?php

add_action( 'init', 'ccs_register_my_taxonomies' );
function ccs_register_my_taxonomies() {
	register_taxonomy( 'framework_type', array( 'framework' ), array(
		'hierarchical' => true,
		'label'        => 'Framework Type',
		'capabilities' => [ 'assign_terms' => 'edit_frameworks' ],
		'show_in_rest' => true
	) );

	register_taxonomy( 'media_category', array( 'attachment' ), array(
		'hierarchical' => true,
		'label'        => 'Media Categories',
		'capabilities' => [ 'assign_terms' => 'upload_files' ],
		'show_in_rest' => true
	) );

	register_taxonomy( 'products_services', array( 'post', 'event' ), array(
		'hierarchical' => true,
		'label'        => 'Products and Services',
		'capabilities' => [ 'assign_terms' => 'edit_posts' ],
		'show_in_rest' => true
	) );

	register_taxonomy( 'audience_tag', array( 'event' ), array(
		'hierarchical' => true,
		'label'        => 'Audience Tag',
		'capabilities' => [ 'assign_terms' => 'edit_events' ],
		'show_in_rest' => true
	) );



	register_taxonomy( 'sectors', array( 'post', 'event', 'page' ), array(
		'hierarchical' => true,
		'label'        => 'Sectors',
		'capabilities' => [ 'assign_terms' => 'edit_posts' ],
		'show_in_rest' => true
	) );


}

/**
 * Make the framework_type taxonomy required (don't allow users to select
 * "No Framework Type" as a choice)
 */
add_filter( "radio_buttons_for_taxonomies_no_term_framework_type", "__return_FALSE" );


/**
 *
 * Register custom content types (called custom post types in Wordpress)
 *
 * https://codex.wordpress.org/Function_Reference/register_post_type
 * https://codex.wordpress.org/Function_Reference/_x
 * https://codex.wordpress.org/Function_Reference/_2
 * https://codex.wordpress.org/Reserved_Terms
 */

add_action( 'init', 'register_pillars_taxonomy' );
function register_pillars_taxonomy() {

	/**
	 * Register custom taxonomy (Pillars for the post type Pages)
	 *
	 * This taxonomy is hierarchical (like categories)
	 * https://codex.wordpress.org/Function_Reference/register_taxonomy
	 *
	 */
	$labels = array(
		'name'                       => _x( 'Pillars', 'taxonomy general name', 'crowncommercialservice' ),
		'singular_name'              => _x( 'Pillar', 'taxonomy singular name', 'crowncommercialservice' ),
		'menu_name'                  => __( 'Pillars', 'crowncommercialservice' ),
		'all_items'                  => __( 'All Pillars', 'crowncommercialservice' ),
		'edit_item'                  => __( 'Edit Pillar', 'crowncommercialservice' ),
		'view_item'                  => __( 'View Pillar', 'crowncommercialservice' ),
		'update_item'                => __( 'Update Pillar', 'crowncommercialservice' ),
		'add_new_item'               => __( 'Add New Pillar', 'crowncommercialservice' ),
		'new_item_name'              => __( 'New Pillar Name', 'crowncommercialservice' ),
		'parent_item'                => __( 'Parent Pillar', 'crowncommercialservice' ),
		'parent_item_colon'          => __( 'Parent Pillar:', 'crowncommercialservice' ),
		'search_items'               => __( 'Search Pillars', 'crowncommercialservice' ),
		'popular_items'              => __( 'Popular Pillars', 'crowncommercialservice' ),
		'separate_items_with_commas' => __( 'Separate Pillars with commas', 'crowncommercialservice' ),
		'add_or_remove_items'        => __( 'Add or remove Pillars', 'crowncommercialservice' ),
		'choose_from_most_used'      => __( 'Choose from the most used Pillars', 'crowncommercialservice' ),
		'not_found'                  => __( 'No Pillars found.', 'crowncommercialservice' ),
		'back_to_items'              => __( '← Back to Pillars', 'crowncommercialservice' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'show_in_rest'      => true,
		'rest_base'         => 'pillars',
	);

	// https://codex.wordpress.org/Reserved_Terms
	register_taxonomy( 'pillars', array(
		'page',
		'whitepaper',
		'digital_brochure',
		'webinar'
	), $args );
	// We'll use this to make sure post types are attached inside filter callback that run during parse_request or pre_get_posts
//	register_taxonomy_for_object_type( 'pillars', 'page' );
}
