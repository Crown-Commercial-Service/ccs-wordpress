<?php

/**
 * Class CustomHomepageComponentsApi
 */
class CustomHomepageComponentsApi
{
    /**
     * Return error
     *
     * @todo Log this error?
     *
     * @param string $message Error message
     * @param int $statusCode HTTP response status code
     */
    public function error(string $message = '', int $statusCode = 500)
    {
        $data = json_encode([
            'message' => $message
        ]);

        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo $data;
        exit;
    }

    /**
     * Get homepage component data from Wordpress
     *
     * @return array
     */
    public function get_homepage_components()
    {
        $howToBuy = [];
        if (have_rows('how_to_buy', 'option')):
            while (have_rows('how_to_buy', 'option')): the_row();
                $howToBuy['title'] = get_sub_field('title');
                $howToBuy['description'] = get_sub_field('description');
                $howToBuy['link_text'] = get_sub_field('link_text');
                $howToBuy['link_url'] = get_sub_field('link');
            endwhile;
            
        endif;

        $agreements = [];
        if (have_rows('agreements', 'option')):
            while (have_rows('agreements', 'option')): the_row();
                $agreements['title'] = get_sub_field('title');
                $agreements['description'] = get_sub_field('description');
                $agreements['link_text'] = get_sub_field('link_text');
                $agreements['link_url'] = get_sub_field('link');
            endwhile;
            
        endif;

        $upcomingDeals = [];
        if (have_rows('upcoming_deals', 'option')):
            while (have_rows('upcoming_deals', 'option')): the_row();
                $upcomingDeals['title'] = get_sub_field('title');
                $upcomingDeals['description'] = get_sub_field('description');
                $upcomingDeals['link_text'] = get_sub_field('link_text');
                $upcomingDeals['link_url'] = get_sub_field('link');
            endwhile;
            
        endif;

        $catalogueTitle = [];
        if (have_rows('catalogue_title', 'option')):
            while (have_rows('catalogue_title', 'option')): the_row();
                $catalogueTitle['title'] = get_sub_field('title');
            endwhile;
            
        endif;

        $catalogue1 = [];
        if (have_rows('catalogue1', 'option')):
            while (have_rows('catalogue1', 'option')): the_row();
                $catalogue1['title'] = get_sub_field('title');
                $catalogue1['link_url'] = get_sub_field('link');
                $catalogue1['description'] = get_sub_field('description');
            endwhile;
            
        endif;

        $catalogue2 = [];
        if (have_rows('catalogue2', 'option')):
            while (have_rows('catalogue2', 'option')): the_row();
                $catalogue2['title'] = get_sub_field('title');
                $catalogue2['link_url'] = get_sub_field('link');
                $catalogue2['description'] = get_sub_field('description');
            endwhile;
            
        endif;

        $catalogue3 = [];
        if (have_rows('catalogue3', 'option')):
            while (have_rows('catalogue3', 'option')): the_row();
                $catalogue3['title'] = get_sub_field('title');
                $catalogue3['link_url'] = get_sub_field('link');
                $catalogue3['description'] = get_sub_field('description');
            endwhile;
            
        endif;

        $video= [];
        if (have_rows('video', 'option')):
            while (have_rows('video', 'option')): the_row();
                $video['title'] = get_sub_field('title');
                $video['video_link'] = get_sub_field('video_link');
                $video['video_caption'] = get_sub_field('video_caption');
                $video['link_text'] = get_sub_field('link_text');
                $video['link_url'] = get_sub_field('link');
            endwhile;
            
        endif;

        return [
          'how_to_buy'  => $howToBuy,
          'agreements'  => $agreements,
          'upcoming_deals'  => $upcomingDeals,
          'catalogue_title' => $catalogueTitle,
          'catalogue1'  => $catalogue1,
          'catalogue2'  => $catalogue2,
          'catalogue3'  => $catalogue3,
          'video' => $video, 
        ];
    }

}