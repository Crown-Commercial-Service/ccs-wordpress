<?php

/**
 * Modify the whitepapers relationship field that is returned on relevant
 * pages so that it includes fields we need (featured image and whitepaper file)
 */
if (!function_exists('whitepapers_add_data')) {
    function whitepapers_add_data($response, $post) {
        if(!isset($response->data['acf']['whitepapers_list_whitepapers']) || empty($response->data['acf']['whitepapers_list_whitepapers'])) {
            return $response;
        }

        foreach($response->data['acf']['whitepapers_list_whitepapers'] as $key => $whitepaper) {
            if(!isset($whitepaper->ID)) {
                continue;
            }

            $postId = $whitepaper->ID;

            $file = get_field('whitepaper_file', $postId);

            $featuredImageId = get_post_thumbnail_id( $postId );
            $linkText = get_field('link_text', $postId);

            $response->data['acf']['whitepapers_list_whitepapers'][$key]->ccs_whitepaper_file = $file;
            $response->data['acf']['whitepapers_list_whitepapers'][$key]->ccs_whitepaper_featured_image = ($featuredImageId != "" ? intval($featuredImageId) : null);
            $response->data['acf']['whitepapers_list_whitepapers'][$key]->link_text = $linkText;
        }

        return $response;
    }
}

/**
 * Modify the webinars relationship field that is returned on relevant
 * pages so that it includes fields we need (featured image)
 */
if (!function_exists('webinars_add_data')) {
    function webinars_add_data($response, $post) {
        if(!isset($response->data['acf']['webinars_list_webinars']) || empty($response->data['acf']['webinars_list_webinars'])) {
            return $response;
        }

        foreach($response->data['acf']['webinars_list_webinars'] as $key => $webinar) {
            if(!isset($webinar->ID)) {
                continue;
            }

            $postId = $webinar->ID;

            $featuredImageId = get_post_thumbnail_id( $postId );
            $linkText = get_field('link_text', $postId);

            $response->data['acf']['webinars_list_webinars'][$key]->ccs_webinar_featured_image = ($featuredImageId != "" ? intval($featuredImageId) : null);
            $response->data['acf']['webinars_list_webinars'][$key]->link_text = $linkText;
        }

        return $response;
    }
}





add_filter('rest_prepare_page', 'whitepapers_add_data', 10, 3);
add_filter('rest_prepare_page', 'webinars_add_data', 10, 3);
