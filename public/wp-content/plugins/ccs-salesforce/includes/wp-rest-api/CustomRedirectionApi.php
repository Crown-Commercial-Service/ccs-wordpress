<?php

class CustomRedirectionApi{

    /**
     * return a list of short and long url for for redirection
     *
     * @return mixed|WP_REST_Response
     */
    public function getListOfRedirections(){

        $results  = [];
        if (have_rows('list_of_redirection', 'option')):
            while (have_rows('list_of_redirection', 'option')): the_row();
                $results[] = ['shortUrl' => strtolower(get_sub_field('shorten_url')),'longUrl' => strtolower(get_sub_field('long_url'))];
            endwhile;

        endif;

        $meta = [
            'total_results' => count($results),
        ];

        header('Content-Type: application/json');

        return rest_ensure_response(['meta' => $meta, 'results' => $results]);

        }
}