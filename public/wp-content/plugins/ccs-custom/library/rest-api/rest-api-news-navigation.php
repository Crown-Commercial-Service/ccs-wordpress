<?php

if (!function_exists('modify_news_navigation')) {
    function modify_news_navigation($response, $post) {

        $allPost = get_posts( array('numberposts' => 10000) );
        $posts = array();
         
        foreach ( $allPost as $eachPost ) {
            $posts[] = array(
                'ID' => $eachPost->ID,
                'title' => $eachPost->post_title);
        }

        $currentindex = array_search( get_the_ID(), array_column($posts, 'ID') );

        $prevID = $posts[ $currentindex-1 ]['ID'];
        $nextID = $posts[ $currentindex+1 ]['ID'];

        $response->data['acf']["previous_text"] = getPostTitleFromPostID($prevID, $currentindex - 1, $posts);
		$response->data['acf']["previous_link"] = getSlugFromPostID($prevID);
        $response->data['acf']["next_text"] = getPostTitleFromPostID($nextID, $currentindex + 1, $posts);
        $response->data['acf']["next_link"] = getSlugFromPostID($nextID);

        return $response;
    }

    function getSlugFromPostID($ID){

        return ($ID == null) ? null : str_replace(getenv('WP_SITEURL_WITH_HTTP'), "", get_permalink( $ID ));
    }

    function getPostTitleFromPostID($ID, $index, $posts){
        
        return ($ID == null) ? null : $posts[ $index ]['title'];
    }
}

add_filter('rest_prepare_post', 'modify_news_navigation', 10, 3);