<?php
/**
 * Remove inline width and height attributes for post thumbnails
 */

add_filter( 'post_thumbnail_html', 'remove_thumbnail_dimensions', 10, 3 );

add_shortcode( 'wp_caption', 'fixed_img_caption_shortcode' );
add_shortcode( 'caption', 'fixed_img_caption_shortcode' );

function remove_thumbnail_dimensions( $html, $post_id, $post_image_id ) {
	$html = preg_replace( '/(width|height)=\"\d*\"\s/', '', $html );

	return $html;
}

function fixed_img_caption_shortcode( $attr, $content = null ) {
	if ( ! isset( $attr['caption'] ) ) {
		if ( preg_match( '#((?:<a [^>]+>s*)?<img [^>]+>(?:s*</a>)?)(.*)#is', $content, $matches ) ) {
			$content         = $matches[1];
			$attr['caption'] = trim( $matches[2] );
		}
	}
	$output = apply_filters( 'img_caption_shortcode', '', $attr, $content );
	if ( $output != '' ) {
		return $output;
	}
	extract( shortcode_atts( array(
		'id'      => '',
		'align'   => 'alignnone',
		'width'   => '',
		'caption' => ''
	), $attr ) );
	if ( 1 > (int) $width || empty( $caption ) ) {
		return $content;
	}
	if ( $id ) {
		$id = 'id="' . esc_attr( $id ) . '" ';
	}

	return '<div ' . $id . 'class="wp-caption ' . esc_attr( $align ) . '" >' . do_shortcode( $content ) . '<p class="wp-caption-text">' . $caption . '</p></div>';
}
