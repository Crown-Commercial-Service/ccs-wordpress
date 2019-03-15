<?php

add_action( 'init', 'ccs_register_my_taxonomies' );
function ccs_register_my_taxonomies() {
    register_taxonomy( 'framework_type', array( 'framework' ), array('hierarchical' => true, 'label' => 'Framework Type', 'capabilities' => ['assign_terms' => 'edit_frameworks'], 'show_in_rest' => true ));
    register_taxonomy( 'framework_status', array( 'framework' ), array('hierarchical' => true, 'label' => 'Framework Status', 'capabilities' => ['assign_terms' => 'edit_frameworks'], 'show_in_rest' => true ));
}
