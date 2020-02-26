<?php

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


add_filter('rest_prepare_page', 'webinars_add_data', 10, 3);
