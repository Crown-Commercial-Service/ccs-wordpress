<?php
// Revisionise adjustments
//
// Hooks to remove revisionise options if a post already has a revision. Aims to solve issue where multiple revisions were leading to duplicate pages being published.

function checkForRevisions($post) {

	global $wpdb;

    $check = $wpdb->get_results($wpdb->prepare("
        SELECT * FROM ".$wpdb->base_prefix."posts WHERE post_status='draft' AND post_parent= %d", $post->ID
    ));

	$rowcount = $wpdb->num_rows;
	return ($rowcount > 0) ? true : false;

}

function isViableForRevisionise($post) {


	// Disallow revisionise on archived posts.
	if ($post->post_status == 'archived') { 
		return false; 
	}

	// Disallow revisionise on pending posts.
	if ($post->post_status == 'pending') { 
		return false; 
	}

	// If post has a parent, and isn't published, remove option.
	if ($post->post_parent > 0 && $post->post_status != 'publish') {
		return false;
	}

	// If post already has revisions, disable revisionise option.
	if (checkForRevisions($post)) {
		return false;
	}

	return true;

}

// Wordpress Hooks

// Post row (on listing)

function hideRevisioniseFromPostRow($actions, $post) {

	if (!isViableForRevisionise($post)) {

		// Revision found, so remove the option.
		if (isset($actions['create_revision'])) {
			unset($actions['create_revision']);
		}

	}

	return $actions;
}


add_filter('post_row_actions', 'hideRevisioniseFromPostRow', 100, 2);



// Post button.

function hideRevisioniseButton($post) {

	if (!isViableForRevisionise($post)) {
		// Revision found, so remove the button.
		remove_action('post_submitbox_start','Revisionize\post_button',200,0);
	}

}

add_action('post_submitbox_start','hideRevisioniseButton',100,1);



// Admin bar

function hideRevisioniseAdminBar($admin_bar) {

	$post = get_post();

	if (!$post) { 
		return $admin_bar; 
	}

	if (!isViableForRevisionise($post) ) {
		// Revision found, so remove the link
		$admin_bar->remove_menu('revisionize');
	}
	return $admin_bar;
}

add_action('admin_bar_menu','hideRevisioniseAdminBar',200,1);


