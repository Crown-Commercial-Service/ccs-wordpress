<?php

add_action( 'init', 'ccs_register_my_cpts' );
function ccs_register_my_cpts() {

    // Framework(s) content type
    $labels = array(
        "name" => __('Frameworks', ''),
        "singular_name" => __('Framework', ''),
        "menu_name" => __('Frameworks', ''),
        "name_admin_bar" => __('Framework', ''),
        'add_new'            => __('Add new', 'framework', ''),
        'add_new_item'       => __('Add new Framework', ''),
        'new_item'           => __('New Framework', ''),
        'edit_item'          => __('Edit Framework', ''),
        'view_item'          => __('View Framework', ''),
        'all_items'          => __('All Frameworks', ''),
        'search_items'       => __('Search Frameworks', ''),
        'parent_item_colon'  => __('Parent Framework:', ''),
        'not_found'          => __('No Frameworks found.', ''),
        'not_found_in_trash' => __('No Frameworks found in Trash.', '')
    );

    $args = array(
        "labels" => $labels,
        "description" => "",
        "public" => true,
        "show_ui" => true,
        "show_in_rest" => true,
        "rest_base" => "",
        "has_archive" => false,
        "show_in_menu" => true,
        "exclude_from_search" => false,
        "capability_type" => "framework",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => array("slug" => "frameworks"),
        "query_var" => true,
        "menu_icon" => "dashicons-media-spreadsheet",
        "supports" => array("title", "excerpt", "revisions", "editor", "author"),
    );
    register_post_type("framework", $args);



    // Lot(s) content type
    $labels = array(
        "name" => __('Lots', ''),
        "singular_name" => __('Lot', ''),
        "menu_name" => __('Lots', ''),
        "name_admin_bar" => __('Lot', ''),
        'add_new'            => __('Add new', 'lot', ''),
        'add_new_item'       => __('Add new Lot', ''),
        'new_item'           => __('New Lot', ''),
        'edit_item'          => __('Edit Lot', ''),
        'view_item'          => __('View Lot', ''),
        'all_items'          => __('All Lots', ''),
        'search_items'       => __('Search Lots', ''),
        'parent_item_colon'  => __('Parent Lot:', ''),
        'not_found'          => __('No Lots found.', ''),
        'not_found_in_trash' => __('No Lots found in Trash.', '')
    );

    $args = array(
        "labels" => $labels,
        "description" => "",
        "public" => true,
        "show_ui" => true,
        "show_in_rest" => true,
        "rest_base" => "",
        "has_archive" => false,
        "show_in_menu" => true,
        "exclude_from_search" => false,
        "capability_type" => "lot",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => array("slug" => "lots"),
        "query_var" => true,
        "menu_icon" => "dashicons-editor-ol",
        "supports" => array("title", "excerpt", "revisions", "editor"),
    );
    register_post_type("lot", $args);



    // Supplier(s) content type
    $labels = array(
        "name" => __('Suppliers', ''),
        "singular_name" => __('Supplier', ''),
        "menu_name" => __('Suppliers', ''),
        "name_admin_bar" => __('Supplier', ''),
        'add_new'            => __('Add New', 'supplier', ''),
        'add_new_item'       => __('Add New Supplier', ''),
        'new_item'           => __('New Supplier', ''),
        'edit_item'          => __('Edit Supplier', ''),
        'view_item'          => __('View Supplier', ''),
        'all_items'          => __('All Suppliers', ''),
        'search_items'       => __('Search Suppliers', ''),
        'parent_item_colon'  => __('Parent Supplier:', ''),
        'not_found'          => __('No Supplier found.', ''),
        'not_found_in_trash' => __('No Supplier found in Trash.', '')
    );

    $args = array(
        "labels" => $labels,
        "description" => "",
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => true,
        "rest_base" => "",
        "has_archive" => true,
        "show_in_menu" => true,
        "exclude_from_search" => false,
        "capability_type" => "supplier",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => array("slug" => "suppliers"),
        "query_var" => true,
        "menu_icon" => "dashicons-groups",
        "supports" => array("title", "excerpt", "revisions"),
    );

    register_post_type( "supplier", $args) ;
}
