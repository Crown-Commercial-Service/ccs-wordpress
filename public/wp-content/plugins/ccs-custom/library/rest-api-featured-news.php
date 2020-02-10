<?php


function processApiTaxonomyList($apiArrayData) {
    $taxonomyIDs = [];

    foreach ($apiArrayData as $item) {
        if(isset($item['term_id'])) {
            $taxonomyIDs[] = $item['term_id'];
        }
    }

    return $taxonomyIDs;
}


/**
 * Modify the whitepapers relationship field that is returned on relevant
 * pages so that it includes fields we need (featured image and whitepaper file)
 */
if (!function_exists('modify_featured_news')) {
    function modify_featured_news($response, $post) {
        // there are no components set on this page
        if(!isset($response->data['acf']['page_components_rows'])) {
            return $response;
        }

        $iteration = 0;
        foreach($response->data['acf']['page_components_rows'] as $component) {
            if($component['acf_fc_layout'] == 'feature_news_feature_news') {
                $articles = [];

                $cherryPickedArticles = $component['feature_news_feature_news_cherry_picked_articles'];
                if(count($cherryPickedArticles) >= 3) {
                    $articles = $cherryPickedArticles;
                }

                // do stuff here
                $newsTypes        = processApiTaxonomyList($component['feature_news_feature_news_news_type']);
                $productsServices = processApiTaxonomyList($component['feature_news_feature_news_products_and_services']);
                $sectors          = processApiTaxonomyList($component['feature_news_feature_news_sectors']);


                $args = array(
                    'post_type' => 'post',
                );


                if(!empty($newsTypes)) {
                    $args[] = array(
                        'category__in' => array()
                    );
                }

                if(!empty($productsServices) || !empty($sectors)) {
                    $args['tax_query'] = array(
                        'relation' => 'OR',
                    );
                }

                if(!empty($productsServices)) {
                    $args['tax_query'][] = array(
                        'taxonomy' => 'products_services',
                        'field'    => 'term_id',
                        'terms'    => array( 103, 115, 206 ),
                        'operator' => 'NOT IN',
                    );
                }

                if(!empty($sectors)) {
                    $args['tax_query'][] = array(
                        'taxonomy' => 'sectors',
                        'field'    => 'term_id',
                        'terms'    => array( 103, 115, 206 ),
                        'operator' => 'NOT IN',
                    );
                }


                $articles = new WP_Query($args);

                $response->data['acf']['page_components_rows'][$iteration]['articles'] = $articles;

            }

            $iteration++;
        }

        return $response;
    }
}
//add_filter('rest_prepare_page', 'modify_featured_news', 10, 3);
