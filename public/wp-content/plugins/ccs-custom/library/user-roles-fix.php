<?php

/**
 * To fix a longstanding bug in WordPress
 *
 * https://core.trac.wordpress.org/ticket/16841#comment:60
 */

function ccs_user_register($user_id) {
    $user = new WP_User( $user_id );
    $user->add_cap( 'level_1' );
}
add_action('user_register', 'ccs_user_register');


add_filter( 'members_remove_old_levels', '__return_false' );

/**
 * Allow Framework Author to revisionize any framework they are authoring, including published ones
 */
add_filter('revisionize_user_can_revisionize', 'ccs_allow_framework_authors', 10, 0);

function ccs_allow_framework_authors(){
    return current_user_can('edit_posts') || current_user_can('edit_pages') || current_user_can('edit_frameworks');
}

add_filter('revisionize_is_create_enabled', 'ccs_enable_framework_authors', 20, 2);

function ccs_enable_framework_authors($is_enabled, $post) {
    if ( current_user_can('edit_frameworks') ) {
        return true;
    } else {
        return false;
    }
}
