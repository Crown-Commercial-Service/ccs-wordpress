<?php

/**
 * Modify the whitepapers relationship field that is returned on relevant
 * pages so that it includes fields we need (featured image and whitepaper file)
 */
if (!function_exists('modify_featured_events')) {
    function modify_featured_events($response, $post) {
        // there are no components set on this page
        if(empty($response->data['acf']['page_components_rows'])) {
            return $response;
        }

        $iteration = 0;
        foreach($response->data['acf']['page_components_rows'] as $component) {
            if($component['acf_fc_layout'] == 'feature_events_feature_events') {
                $articles = [];
                $articleIds = [];

                $productsServices = processApiTaxonomyList($component['feature_events_feature_events_event_categories']);
                $sectors          = processApiTaxonomyList($component['feature_events_feature_events_sectors']);

                if(!empty($productsServices) || !empty($sectors)) {
                    // Get query parameters (defined by the user in the CMS

                    // Build the query
                    $args = array(
                        'post_type' => 'event',
                        'posts_per_page' => 2,
                        'meta_key'	     => 'start_datetime',
                        'orderby'		 => 'meta_value',
                        'order'			 => 'ASC'
                    );

                    if (!empty($productsServices) || !empty($sectors)) {
                        $args['tax_query'] = array(
                            'relation' => 'OR',
                        );
                    }

                    if (!empty($productsServices)) {
                        $args['tax_query'][] = array(
                            'taxonomy' => 'products_services',
                            'field' => 'term_id',
                            'terms' => $productsServices,
                            'operator' => 'IN',
                        );
                    }

                    if (!empty($sectors)) {
                        $args['tax_query'][] = array(
                            'taxonomy' => 'sectors',
                            'field' => 'term_id',
                            'terms' => $sectors,
                            'operator' => 'IN',
                        );
                    }


                    $the_query = new WP_Query($args);

                    if ($the_query->have_posts()) {
                        while ($the_query->have_posts()) {
                            $the_query->the_post();
                            $articleIds[] = get_the_ID();
                        }
                    }
                    /* Restore original Post Data */
                    wp_reset_postdata();
                }

                if(!empty($articleIds)) {
                    $articles = additionalPostFormatting($articleIds, '/wp/v2/event');
                }

                foreach($articles as $key => $article) {
                    if(!isset($article['post_type'])) {
                        $articles[$key]['post_type'] = $articles[$key]['type'];
                    }
                }

                $response->data['acf']['page_components_rows'][$iteration]['articles'] = $articles;

            }

            $iteration++;
        }

        return $response;
    }
}
add_filter('rest_prepare_page', 'modify_featured_events', 10, 3);
