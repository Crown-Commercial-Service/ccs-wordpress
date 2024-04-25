<?php

/**
 * Class MessageBannerApi
 */
class MessageBannerApi
{
    public function getMessageBanner()
    {
        $messageBanner = [];
        if (have_rows('message_banner', 'option')) :
            while (have_rows('message_banner', 'option')) : the_row();
            $messageBanner[] = [
                'show_banner' => get_sub_field('show_banner'),
                'title' => get_sub_field('title'),
                'description' => get_sub_field('description'),
                'link' => get_sub_field('link'),
                'link_url' => get_sub_field('link_url'),
            ];
            endwhile;
        endif;

        header('Content-Type: application/json');
        return rest_ensure_response(['message_banner' => $messageBanner]);
    }
}
