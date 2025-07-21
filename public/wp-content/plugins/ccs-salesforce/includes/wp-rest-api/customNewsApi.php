<?php
// phpinfo();
class customNewsApi{

    private function getTerms($params, $termsName){
        $terms = $params[$termsName];
        return is_array($terms) ? $terms : explode(",", $terms);
    }

    private function getInTaxArray($name, $terms){
        return array(
            'taxonomy' => $name,
            'field'    => 'term_id',
            'terms'    => $terms,
            'operator' => 'IN',
        );
    }

    public function getNewsPageContent($request) {
        $params = $request->get_params();

        $per_page = isset($params['per_page']) ? max(1, intval($params['per_page'])) : 5;
        $page = isset($params['page']) ? max(1, intval($params['page'])) : 1;

        // Build post types array
        $post_types = array('post');

        if(( $request->get_param( 'noPost' ) ?? '') == '1') {
		    unset($post_types[0]);
        }
        if(( $request->get_param( 'whitepaper' ) ?? '') == '1') {
		    $post_types[] = 'whitepaper';
        }

        if(( $request->get_param( 'webinar' ) ?? '') == '1') {
            $post_types[] = 'webinar';
        }

        if (!empty($params['digitalDownload'])) {
            $post_types[] = 'downloadable';
        }

        $args = array(
            'post_type'      => $post_types,
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'post_status'    => 'publish',
            'orderby'        => 'modified',
            'order'          => 'DESC',
        );

        // If categories param is set, use the mixed tax_query
        if (!empty($params['categories'])) {
            $terms = $this->getTerms($params, 'categories');         
            
            $args['tax_query'] = array(
                'relation' => 'OR',
                $this->getInTaxArray('category', $terms),
                array(
                    'taxonomy' => 'category',
                    'operator' => 'NOT EXISTS',
                ),
            );
        };

        if (!empty($params['sectors']) ) {
            $terms = $this->getTerms($params, 'sectors');
            
            if (isset($args['tax_query']) && $args["tax_query"]["relation"] == 'OR' ) {
                $tempTaxArray = $args['tax_query'];

                $args['tax_query'] = array(
                    'relation' => 'AND',
                    $tempTaxArray,
                    $this->getInTaxArray('sectors', $terms),
                );
            }else {
                $args['tax_query'][] = $this->getInTaxArray('sectors', $terms);
            }
        }

        if (!empty($params['products_services'])) {
            $terms = $this->getTerms($params, 'products_services');
            
            if (isset($args['tax_query']) && $args["tax_query"]["relation"] == 'OR' ) {
                $tempTaxArray = $args['tax_query'];

                $args['tax_query'] = array(
                    'relation' => 'AND',
                    $tempTaxArray,
                    $this->getInTaxArray('products_services', $terms),
                );
            }else{
                $args['tax_query'][] = $this->getInTaxArray('products_services', $terms);

            }
        }

        if (!empty($params['digitalDownload'])) {
            $terms = $this->getTerms($params, 'digitalDownload');

            $defultContentType = array(
                'relation' => 'OR',
                $this->getInTaxArray('content_type', $terms),
                array(
                    'taxonomy' => 'content_type',
                    'operator' => 'NOT EXISTS',
                )
            );

            
            if (isset($args['tax_query']) ) {
                $tempTaxArray = $args['tax_query'];

                $args['tax_query'] = array(
                    'relation' => 'AND',
                    $tempTaxArray,
                    array(
                        'relation' => 'OR',
                        $defultContentType
                    )
                );
            }else{
                $args['tax_query'] = $defultContentType;
            }

            
            
        }

        
        //Hide post from "View All"
        if (!empty($params['digitalDownload'])) {
            if ( count($this->getTerms($params, 'digitalDownload')) == count(get_terms( array('taxonomy'   => 'content_type'))) 
                && ($request->get_param( 'whitepaper' ) ?? '') == '1' 
                && ($request->get_param( 'webinar' ) ?? '') == '1'
            ){
                $args['meta_query'] = array(
                    'relation'		=> 'AND',
                    array(
                        'key'	  	=> 'Hide_from_View_All',
                        'value'	  	=> '0',
                        'compare' 	=> '=',
                    ),
                );
            }
        }

        $query = new WP_Query($args);
        
        $items = array();
        foreach ($query->posts as $post) {
            $controller = new WP_REST_Posts_Controller($post->post_type);
            $item = $controller->prepare_item_for_response($post, $request)->get_data();


            $featured_image_id = get_post_thumbnail_id($post->ID);
            $featured_image_url = wp_get_attachment_image_url($featured_image_id, 'full');

            $item["acf"]['featured_image_url'] = $featured_image_url;
            $item["acf"]['alt_text'] = get_post_meta($featured_image_id, '_wp_attachment_image_alt', true);
            

            switch ($item["type"]) {
                case "post" :
                    $item["acf"]['category_type'] = get_the_category($item['id'])[0]->name;
                    break;
                case "whitepaper":
                    $item["acf"]['category_type'] = "Whitepaper";
                    $item["acf"]['categories'] = array(000);
                    break;
                case "webinar":
                    $item["acf"]['category_type'] = "Webinar";
                    $item["acf"]['categories'] = array(000);
                    break;
                case "downloadable":
                    $contentTerm = get_the_terms( $item["id"], 'content_type' )[0];

                    $item["acf"]['category_type'] = "Downloadable";
                    $item["acf"]['categories'] = array(000);
                    $item["acf"]['content_type_id'] = $contentTerm->term_id;
                    $item["acf"]['content_type_name'] = $contentTerm->name;
                    break;
                }

            
            $items[] = $item;
        }

        $rest_response = rest_ensure_response($items);
        $rest_response->header('X-WP-Total', (int) $query->found_posts);
        $rest_response->header('X-WP-TotalPages', (int) $query->max_num_pages);

        return $rest_response;
    }
}