<?php

/**
 * Class CustomOptionCardsApi
 */
class CustomOptionCardsApi
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
     * Get call to action data from Wordpress
     *
     * @return array
     */
    public function get_option_cards()
    {
        $firstOptionCard = [];
        if (have_rows('first_option_card', 'option')):
            while (have_rows('first_option_card', 'option')): the_row();
                $firstOptionCard['title'] = get_sub_field('title');
                $firstOptionCard['description'] = get_sub_field('description');
                $firstOptionCard['link'] = get_sub_field('link');
            endwhile;
            
        endif;

        $secondOptionCard = [];
        if (have_rows('second_option_card', 'option')):
            while (have_rows('second_option_card', 'option')): the_row();
                $secondOptionCard['title'] = get_sub_field('title');
                $secondOptionCard['description'] = get_sub_field('description');
                $secondOptionCard['link'] =  get_sub_field('link');
            endwhile;
        endif;

        return [
          'first_option_card'  => $firstOptionCard,
          'second_option_card' => $secondOptionCard,
        ];
    }

}
