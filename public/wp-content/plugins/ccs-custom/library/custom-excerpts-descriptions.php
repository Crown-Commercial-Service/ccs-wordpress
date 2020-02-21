<?php


add_filter( 'gettext', 'update_excerpt_metas', 10, 2 );


function update_excerpt_metas( $translation, $original ) {

	global $post;

	if ( !isset($post->post_type)) {
		return $translation;
	}

	if ( $post->post_type == 'whitepaper' || $post->post_type == 'webinar' ) {

		if ( 'Excerpt' == $original ) {
			//Change here to what you want Excerpt box to be called
			return 'Summary';
		} else {

			// Find the place where the string is stored
			$pos = strpos( $original, 'Excerpts are optional hand-crafted summaries of your' );

			if ( $pos !== false ) {
				//Change the default text you see below the box with link to learn more...
				return 'Write a short summary of the content. This should be no more than 280 characters.';
			}

		}

	}

	return $translation;

}
