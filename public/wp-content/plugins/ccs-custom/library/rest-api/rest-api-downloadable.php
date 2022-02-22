<?php

/**
 * Modify the downloadable resources relationship field that is returned on relevant
 * pages so that it includes fields we need (featured image and downloadable resources file)
 */
if (!function_exists('downloadable_add_data')) {
    function downloadable_add_data($response, $post)
    {
        if (!isset($response->data['acf']['downloadable_list_downloadable']) || empty($response->data['acf']['downloadable_list_downloadable'])) {
            return $response;
        }

        foreach ($response->data['acf']['downloadable_list_downloadable'] as $key => $downloadable) {
            if (!isset($downloadable->ID)) {
                continue;
            }

            $postId = $downloadable->ID;

            $file = get_field('downloadable_file', $postId);

            $featuredImageId = get_post_thumbnail_id($postId);
            $linkText = get_field('link_text', $postId);

            $response->data['acf']['downloadable_list_downloadable'][$key]->ccs_downloadable_file = $file;
            $response->data['acf']['downloadable_list_downloadable'][$key]->ccs_downloadable_featured_image = ($featuredImageId != "" ? intval($featuredImageId) : null);
            $response->data['acf']['downloadable_list_downloadable'][$key]->link_text = $linkText;
        }

        return $response;
    }
}


add_filter('rest_prepare_page', 'downloadable_add_data', 10, 3);
