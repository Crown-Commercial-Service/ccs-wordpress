<?php

/**
 * Add meta box to view page in front-end site
 *
 */
add_action("add_meta_boxes", function(){
    add_meta_box("ccs-headless-links", "Headless CMS", function() {
        $url = get_permalink();

        // Set frontend URL (without trailing slash)
        $frontendUrl = rtrim(getenv('CCS_FRONTEND_URL'), '/');

        $urlParts = parse_url($url);
        $link = $frontendUrl . $urlParts['path'];

        // Detect environment
        $env = getenv('CCS_FRONTEND_APP_ENV');
        if (empty($env)) {
            $env = 'unknown';
        }
        switch ($env) {
            case 'prod':
                $colour = '#3F3';
                break;
            case 'test':
            case 'pre-prod':
            case 'dev':
                $colour = '#FF0';
                break;
            default:
                $colour = '#CCC';
                break;
        }
        $env = ucfirst($env);

        // Detect post type
        $type = get_post_type();

        // URLs are correct in WordPress for the following post types
        $correctUrls = ['post', 'page'];

    if (in_array($type, $correctUrls)) {
        echo '<p><a href="' . esc_url($link) . '">View page on front-end website</a></p>';
    }
    echo '<p>Environment: <strong style="padding: 0.3em 0.6em; background-color: ' . esc_attr($colour) . '">' . esc_html($env) . '</strong></p>';

    }, null, "side", "high", null);
});