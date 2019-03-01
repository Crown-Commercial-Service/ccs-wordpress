<?php

/**
 * To fix a longstanding bug in WordPress
 *
 * https://core.trac.wordpress.org/ticket/16841#comment:60
 */

add_filter( 'members_remove_old_levels', '__return_false' );
