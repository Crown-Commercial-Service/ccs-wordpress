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
<<<<<<< HEAD
        if (have_rows('how_to_buy', 'option')):
            while (have_rows('how_to_buy', 'option')): the_row();
=======
        if (have_rows('hc_how_to_buy', 'option')):
            while (have_rows('hc_how_to_buy', 'option')): the_row();
>>>>>>> 3e3a4475b94e9b217b06773b14ff36ba9c8e9629
                $howToBuy['title'] = get_sub_field('title');
                $howToBuy['description'] = get_sub_field('description');
                $howToBuy['link_text'] = get_sub_field('link_text');
                $howToBuy['link_url'] = get_sub_field('link');
            endwhile;
            
        endif;

        $agreements = [];
<<<<<<< HEAD
        if (have_rows('agreements', 'option')):
            while (have_rows('agreements', 'option')): the_row();
=======
        if (have_rows('hc_agreements', 'option')):
            while (have_rows('hc_agreements', 'option')): the_row();
>>>>>>> 3e3a4475b94e9b217b06773b14ff36ba9c8e9629
                $agreements['title'] = get_sub_field('title');
                $agreements['description'] = get_sub_field('description');
                $agreements['link_text'] = get_sub_field('link_text');
                $agreements['link_url'] = get_sub_field('link');
            endwhile;
            
        endif;

        $upcomingDeals = [];
<<<<<<< HEAD
        if (have_rows('upcoming_deals', 'option')):
            while (have_rows('upcoming_deals', 'option')): the_row();
=======
        if (have_rows('hc_upcoming_deals', 'option')):
            while (have_rows('hc_upcoming_deals', 'option')): the_row();
>>>>>>> 3e3a4475b94e9b217b06773b14ff36ba9c8e9629
                $upcomingDeals['title'] = get_sub_field('title');
                $upcomingDeals['description'] = get_sub_field('description');
                $upcomingDeals['link_text'] = get_sub_field('link_text');
                $upcomingDeals['link_url'] = get_sub_field('link');
            endwhile;
            
        endif;

        $catalogueTitle = [];
<<<<<<< HEAD
        if (have_rows('catalogue_title', 'option')):
            while (have_rows('catalogue_title', 'option')): the_row();
=======
        if (have_rows('hc_catalogue_title', 'option')):
            while (have_rows('hc_catalogue_title', 'option')): the_row();
>>>>>>> 3e3a4475b94e9b217b06773b14ff36ba9c8e9629
                $catalogueTitle['title'] = get_sub_field('title');
            endwhile;
            
        endif;

        $catalogue1 = [];
<<<<<<< HEAD
        if (have_rows('catalogue1', 'option')):
            while (have_rows('catalogue1', 'option')): the_row();
=======
        if (have_rows('hc_catalogue1', 'option')):
            while (have_rows('hc_catalogue1', 'option')): the_row();
>>>>>>> 3e3a4475b94e9b217b06773b14ff36ba9c8e9629
                $catalogue1['title'] = get_sub_field('title');
                $catalogue1['link_url'] = get_sub_field('link');
                $catalogue1['description'] = get_sub_field('description');
            endwhile;
            
        endif;

        $catalogue2 = [];
<<<<<<< HEAD
        if (have_rows('catalogue2', 'option')):
            while (have_rows('catalogue2', 'option')): the_row();
=======
        if (have_rows('hc_catalogue2', 'option')):
            while (have_rows('hc_catalogue2', 'option')): the_row();
>>>>>>> 3e3a4475b94e9b217b06773b14ff36ba9c8e9629
                $catalogue2['title'] = get_sub_field('title');
                $catalogue2['link_url'] = get_sub_field('link');
                $catalogue2['description'] = get_sub_field('description');
            endwhile;
            
        endif;

        $catalogue3 = [];
<<<<<<< HEAD
        if (have_rows('catalogue3', 'option')):
            while (have_rows('catalogue3', 'option')): the_row();
=======
        if (have_rows('hc_catalogue3', 'option')):
            while (have_rows('hc_catalogue3', 'option')): the_row();
>>>>>>> 3e3a4475b94e9b217b06773b14ff36ba9c8e9629
                $catalogue3['title'] = get_sub_field('title');
                $catalogue3['link_url'] = get_sub_field('link');
                $catalogue3['description'] = get_sub_field('description');
            endwhile;
            
        endif;

        $video= [];
<<<<<<< HEAD
        if (have_rows('video', 'option')):
            while (have_rows('video', 'option')): the_row();
=======
        if (have_rows('hc_video', 'option')):
            while (have_rows('hc_video', 'option')): the_row();
>>>>>>> 3e3a4475b94e9b217b06773b14ff36ba9c8e9629
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