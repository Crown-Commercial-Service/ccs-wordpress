<?php

/**
 * Class CustomCallToActionApi
 */
class CustomCallToActionApi
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
    public function get_call_to_actions()
    {
        $firstCallAction = [];
        if (have_rows('first_call_to_action', 'option')):
            while (have_rows('first_call_to_action', 'option')): the_row();
                $firstCallAction[] = ['title' => get_sub_field('title')];
                $firstCallAction[] = ['description' => get_sub_field('description')];
                $firstCallAction[] = ['link' => get_sub_field('link')];
            endwhile;
            
        endif;

        $secondCallAction = [];
        if (have_rows('second_call_to_action', 'option')):
            while (have_rows('second_call_to_action', 'option')): the_row();
                $secondCallAction[] = ['title' => get_sub_field('title')];
                $secondCallAction[] = ['description' => get_sub_field('description')];
                $secondCallAction[] = ['link' => get_sub_field('link')];
            endwhile;
        endif;

        return [
          'first_call_to_action'  => $firstCallAction,
          'second_call_to_action' => $secondCallAction
        ];
    }

}
