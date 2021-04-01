<?php

add_action( 'after_setup_theme' , 'ccs_theme_support' );

function ccs_theme_support() {
    add_theme_support('post-thumbnails' , array('post') );
}




/**
 * Add label to post thumbnail meta box
 */
function s24_add_description_to_featured_image_metabox($content, $post_id, $thumbnail_id)
{
	$post = get_post($post_id);

	if ( $post->post_type == 'webinar' || $post->post_type == 'whitepaper' || $post->post_type == 'digital_brochure' ) {
		$content .= '<small><em>Minimum size: 640&times;427. Recommended size: 1280&times;854.</em></small>';
		return $content;
	}

	return $content;
}
add_filter('admin_post_thumbnail_html', 's24_add_description_to_featured_image_metabox', 10, 3);
