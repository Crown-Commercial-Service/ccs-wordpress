<?php
/**
 * Wrap oEmbed in a DIV instead of a P
 */
add_filter( 'embed_oembed_html', 'wrap_oembed_html', 99, 4 );

function wrap_oembed_html( $cached_html, $url, $attr, $post_id ) {

	if ( false !== strpos( $url, "://youtube.com" ) || false !== strpos( $url, "://youtu.be" ) ) {

		$cached_html = '<div class="responsive-video widescreen">' . $cached_html . '</div>';

	}

	return $cached_html;

}
