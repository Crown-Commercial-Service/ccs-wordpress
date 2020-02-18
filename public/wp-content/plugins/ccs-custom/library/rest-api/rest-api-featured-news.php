<?php

/**
 * Modify the whitepapers relationship field that is returned on relevant
 * pages so that it includes fields we need (featured image and whitepaper file)
 */
if (!function_exists('modify_featured_news')) {
    function modify_featured_news($response, $post) {
        // there are no components set on this page
        if(empty($response->data['acf']['page_components_rows'])) {
            return $response;
        }

        $iteration = 0;
        foreach($response->data['acf']['page_components_rows'] as $component) {
            if($component['acf_fc_layout'] == 'feature_news_feature_news') {
                $articles = [];
                $articleIds = [];
                $numCherryPicked = 0;

                $cherryPickedArticles = $component['feature_news_feature_news_cherry_picked_articles'];
                if(!empty($cherryPickedArticles)) {
                    $numCherryPicked = count($cherryPickedArticles);
                    foreach ($cherryPickedArticles as $article) {
                        $articleIds[] = $article->ID;
                    }

                }

                // calculate how many articles we need to query for
                $numQueryArticles = 3 - $numCherryPicked;

                $newsTypes        = processApiTaxonomyList($component['feature_news_feature_news_news_type']);
                $productsServices = processApiTaxonomyList($component['feature_news_feature_news_products_and_services']);
                $sectors          = processApiTaxonomyList($component['feature_news_feature_news_sectors']);

                if($numQueryArticles > 0 && (!empty($productsServices) || !empty($sectors))) {
                    // Get query parameters (defined by the user in the CMS

                    // Build the query
                    $args = array(
                        'post_type' => 'post',
                        'posts_per_page' => $numQueryArticles,
                    );


                    if (!empty($newsTypes)) {
                        $args[] = array(
                            'category__in' => array($newsTypes)
                        );
                    }

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
                    $articles = additionalPostFormatting($articleIds);
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
add_filter('rest_prepare_page', 'modify_featured_news', 10, 3);
