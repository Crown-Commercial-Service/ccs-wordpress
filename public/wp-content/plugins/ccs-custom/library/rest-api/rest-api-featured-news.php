<?php

if (!function_exists('modify_featured_news')) {
    function modify_featured_news($response, $post) {
        // Check for both page and post components
        $component_types = ['page_components_rows', 'post_components_rows'];
        
        foreach ($component_types as $component_type) {
            if(empty($response->data['acf'][$component_type])) {
                continue;
            }

            $iteration = 0;
            foreach($response->data['acf'][$component_type] as $component) {
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

                $urlLink = '/news';
                $params = []; 
                $newsTypes ? $params['categories'] = implode(',', $newsTypes) : null;
                $productsServices ? $params['products_services'] = implode(',', $productsServices) : null;
                $sectors ? $params['sectors'] = implode(',', $sectors) : null;

                $response->data['acf'][$component_type][$iteration]['feature_news_link'] = !empty($params) ? $urlLink .= '?' . http_build_query($params) : $urlLink;;

                if($numQueryArticles > 0 && (!empty($productsServices) || !empty($sectors))) {
                    // Get query parameters (defined by the user in the CMS

                    // Build the query
                    $args = array(
                        'post_type' => 'post',
                        'posts_per_page' => $numQueryArticles,
                    );


                    if (!empty($newsTypes)) {
                        $args['tax_query']['relation'] = 'AND';
                        $args['tax_query'][0] = array(
                            'taxonomy' => 'category',
                            'field'    => 'term_id',
                            'terms'    => $newsTypes,
                            'operator' => 'IN'
                        );
                    }

                    if (!empty($productsServices) || !empty($sectors)) {
                        $args['tax_query'][1]['relation'] = 'OR';
                    }

                    if (!empty($productsServices)) {
                        $args['tax_query'][1][] = array(
                            'taxonomy' => 'products_services',
                            'field' => 'term_id',
                            'terms' => $productsServices,
                            'operator' => 'IN',
                        );
                    }

                    if (!empty($sectors)) {
                        $args['tax_query'][1][] = array(
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

                    $response->data['acf'][$component_type][$iteration]['articles'] = $articles;
                }

                $iteration++;
            }
        }

        return $response;
    }
}

// Add filters for both post and page types
add_filter('rest_prepare_page', 'modify_featured_news', 10, 3);
add_filter('rest_prepare_post', 'modify_featured_news', 10, 3);
