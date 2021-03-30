<?php

/**
 * Modify the digital brochures relationship field that is returned on relevant
 * pages so that it includes fields we need (featured image and digital brochure file)
 */
if (!function_exists('digital_brochures_add_data')) {
    function digital_brochures_add_data($response, $post) {
        if(!isset($response->data['acf']['digital_brochures_list_digital_brochures']) || empty($response->data['acf']['digital_brochures_list_digital_brochures'])) {
            return $response;
        }

        foreach($response->data['acf']['digital_brochures_list_digital_brochures'] as $key => $digital_brochure) {
            if(!isset($digital_brochure->ID)) {
                continue;
            }

            $postId = $digital_brochure->ID;

            $file = get_field('digital_brochure_file', $postId);

            $featuredImageId = get_post_thumbnail_id( $postId );
            $linkText = get_field('link_text', $postId);

            $response->data['acf']['digital_brochures_list_digital_brochures'][$key]->ccs_digital_brochure_file = $file;
            $response->data['acf']['digital_brochures_list_digital_brochures'][$key]->ccs_digital_brochure_image = ($featuredImageId != "" ? intval($featuredImageId) : null);
            $response->data['acf']['digital_brochures_list_digital_brochures'][$key]->link_text = $linkText;
        }

        return $response;
    }
}


add_filter('rest_prepare_page', 'digital_brochures_add_data', 10, 3);
