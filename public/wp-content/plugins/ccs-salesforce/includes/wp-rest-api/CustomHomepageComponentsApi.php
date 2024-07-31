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
        if (have_rows('hc_how_to_buy', 'option')):
            while (have_rows('hc_how_to_buy', 'option')): the_row();
                $howToBuy['title'] = get_sub_field('title');
                $howToBuy['description'] = get_sub_field('description');
                $howToBuy['link_text'] = get_sub_field('link_text');
                $howToBuy['link_url'] = get_sub_field('link');
            endwhile;
            
        endif;

        $agreements = [];
        if (have_rows('hc_agreements', 'option')):
            while (have_rows('hc_agreements', 'option')): the_row();
                $agreements['title'] = get_sub_field('title');
                $agreements['description'] = get_sub_field('description');
                $agreements['link_text'] = get_sub_field('link_text');
                $agreements['link_url'] = get_sub_field('link');
            endwhile;
            
        endif;

        $upcomingDeals = [];
        if (have_rows('hc_upcoming_deals', 'option')):
            while (have_rows('hc_upcoming_deals', 'option')): the_row();
                $upcomingDeals['title'] = get_sub_field('title');
                $upcomingDeals['description'] = get_sub_field('description');
                $upcomingDeals['link_text'] = get_sub_field('link_text');
                $upcomingDeals['link_url'] = get_sub_field('link');
            endwhile;
            
        endif;

        $campaign = [];
        if (have_rows('campaign', 'option')):
            while (have_rows('campaign', 'option')): the_row();
                $campaign['title'] = get_sub_field('title');
                $campaign['description'] = get_sub_field('description');
                $campaign['button_text'] = get_sub_field('button_text');
                $campaign['button_link'] = get_sub_field('button_link');
            endwhile;
            
        endif;

        return [
          'how_to_buy'  => $howToBuy,
          'agreements'  => $agreements,
          'upcoming_deals'  => $upcomingDeals,
          'campaign' => $campaign, 
        ];
    }

}