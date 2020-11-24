<?php

/**
 * Class CustomOptionCardsApi
 */
class CustomUpcomingDealsApi
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
    public function get_upcoming_deals()
    {
        $upcomingDealsInfo = [];
        if (have_rows('upcoming_deals', 'option')):
            while (have_rows('upcoming_deals', 'option')): the_row();
                $upcomingDealsInfo['title'] = get_sub_field('title');
                $upcomingDealsInfo['page_description'] = get_sub_field('page_description');
            endwhile;
            
        endif;

        $table1 = [];
        if (have_rows('table_1', 'option')):
            while (have_rows('table_1', 'option')): the_row();
                $table1['title'] = get_sub_field('title');
                $table1['caption'] = get_sub_field('caption');
            endwhile;
        endif;

        $table2 = [];
        if (have_rows('table_2', 'option')):
            while (have_rows('table_2', 'option')): the_row();
                $table2['title'] = get_sub_field('title');
            endwhile;
        endif;

        $table3 = [];
        if (have_rows('table_3', 'option')):
            while (have_rows('table_3', 'option')): the_row();
                $table3['title'] = get_sub_field('title');
            endwhile;
        endif;

        $table4 = [];
        if (have_rows('table_4', 'option')):
            while (have_rows('table_4', 'option')): the_row();
                $table4['title'] = get_sub_field('title');
            endwhile;
        endif;

        return [
          'upcomingDealsInfo'  => $upcomingDealsInfo,
          'table_1' => $table1,
          'table_2' => $table2,
          'table_3' => $table3,
          'table_4' => $table4,
        ];
    }

}