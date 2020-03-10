<?php

/**
 * Create two sub-pages for the page admin section
 *
 * General content - Shows all pages excluding the marketing pages
 * Marketing content - Shows only the marketing pages
 */


/**
 * Add the custom menu items to the WordPress admin
 */
function ccs_custom_page_filtering_menus() {
    add_submenu_page('edit.php?post_type=page', 'General content', 'General content', 'edit_pages', 'edit.php?post_type=page&content_pages=1', '', 1);
    add_submenu_page('edit.php?post_type=page', 'Marketing content', 'Marketing content', 'edit_pages', 'edit.php?post_type=page&marketing_pages=1', '', 1);
}

add_action('admin_menu', 'ccs_custom_page_filtering_menus', 5);


/**
 * Add two custom query vars that we can use to identify how to filter
 * the WordPress query
 *
 * @param $qvars
 * @return array
 */
function ccs_admin_query_vars( $qvars ) {
    $qvars[] = 'marketing_pages';
    $qvars[] = 'content_pages';
    return $qvars;
}
add_filter( 'query_vars', 'ccs_admin_query_vars' );


/**
 * Use pre_get_posts to filter the pages list appropriately.
 *
 * We can tell how we need to filter the list based on whether the query parameters
 * defined above are used in the URL or not.
 *
 * https://developer.wordpress.org/reference/classes/wp_query/#post-page-parameters
 *
 * @param $query
 */
function ccs_custom_page_filter($query) {
    /**
     * If we're not in the admin, and this isn't the main query, then return, as
     * it's impossible the query will match the one we're looking for
     */
    if ( !is_admin() && !$query->is_main_query() ) {
        return;
    }

    /**
     * Exclude Marketing Pages
     */
    if( !empty(get_query_var('content_pages')) ) {
        $excludeIds = [17779];
        $excludePages = get_pages(['child_of' => 17779]);
        foreach ($excludePages as $page) {
            $excludeIds[] = $page->ID;
        }

        $query->set( 'post__not_in', $excludeIds );
    }

    /**
     * Only show marketing pages
     */
    if( !empty(get_query_var('marketing_pages')) ) {
        $includeIds = [17779];
        $includePages = get_pages(['child_of' => 17779]);
        foreach ($includePages as $page) {
            $includeIds[] = $page->ID;
        }

        $query->set( 'post__in', $includeIds );
    }
}
add_action( 'pre_get_posts', 'ccs_custom_page_filter' );


/**
 * Fixes, what might be, a bug in WordPress, where is you define a sub-page
 * with an empty callback, it doesn't assign the admin menu class properly.
 *
 * (Doesn't feel like a great way of doing this, but I can't find another way)
 *
 * @param $parent_file
 * @return string
 */
function ccs_fix_admin_current_class($parent_file) {
    if(!is_admin()) {
        return $parent_file;
    }

    if( !empty(get_query_var('content_pages')) ) {
        return 'edit.php?post_type=page&content_pages=1';
    }

    if( !empty(get_query_var('marketing_pages')) ) {
        return 'edit.php?post_type=page&marketing_pages=1';
    }

    return $parent_file;
}
add_filter('submenu_file', 'ccs_fix_admin_current_class');
