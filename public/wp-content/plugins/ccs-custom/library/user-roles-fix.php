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
