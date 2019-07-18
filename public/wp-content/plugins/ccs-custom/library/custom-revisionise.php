<?php

// Hooks to remove revisionise options if a post already has a revision. Aims to solve issue where multiple revisions were leading to duplicate pages being published.

function checkForRevisions($post) {

	global $wpdb;

    $check = $wpdb->get_results($wpdb->prepare("
        SELECT * FROM ".$wpdb->base_prefix."posts WHERE post_status='draft' AND post_parent= %d", $post->ID
    ));

	$rowcount = $wpdb->num_rows;

	return ($rowcount > 0) ? true : false;

}


// Post row (on listing)

function hideRevisioniseFromPostRow($actions, $post) {

	if (!checkForRevisions($post)) {
		return $actions;
	}

	// Revision found, so remove the option.

	unset($actions['create_revision']);
	return $actions;
}


add_filter('post_row_actions', 'hideRevisioniseFromPostRow', 100, 2);



// Post button.

function hideRevisioniseButton($post) {

	if (checkForRevisions($post)) {
		// Revision found, so remove the button.
		remove_action('post_submitbox_start','Revisionize\post_button',200,0);
	}

}

add_action('post_submitbox_start','hideRevisioniseButton',100,1);


