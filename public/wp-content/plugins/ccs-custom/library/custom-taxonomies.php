<?php

add_action( 'init', 'ccs_register_my_taxonomies' );
function ccs_register_my_taxonomies() {
    register_taxonomy( 'framework_type', array( 'framework' ), array('hierarchical' => true, 'label' => 'Framework Type', 'capabilities' => ['assign_terms' => 'edit_frameworks'], 'show_in_rest' => true ));
}

/**
 * Make the framework_type taxonomy required (don't allow users to select
 * "No Framework Type" as a choice)
 */
add_filter( "radio_buttons_for_taxonomies_no_term_framework_type", "__return_FALSE" );
