<?php
// Revisionise adjustments
//
// Hooks to remove revisionise options if a post already has a revision. Aims to solve issue where multiple revisions were leading to duplicate pages being published.

function checkForRevisions($post) {

	global $wpdb;

    $check = $wpdb->get_results($wpdb->prepare("
        SELECT * FROM ".$wpdb->base_prefix."posts WHERE (post_status='in-progress' Or post_status='draft' OR post_status='pending') AND post_parent= %d", $post->ID
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

		// Since the revisionize plugin adds the button by directly outputting to the page, we can't use remove_action. 
		// Instead we've decided to strip it out via CSS.
		?>
		<style>
			a[href*="revisionize_create"] {
				display:none!important;
			}
		</style>
		<?php
	}

}

add_action('post_submitbox_start','hideRevisioniseButton',200,1);



function validateFrameworkDescription($post) {

	$frameworkDescription = $_POST['acf']["201902041237a_201902041416a"];
    $FrameworkType = $_POST['radio_tax_input']['framework_type'][0];

	if ($FrameworkType != "33" && $post["post_type"] == 'framework' && empty($frameworkDescription) && $post["post_status"] == 'pending') { 
		update_option('my_admin_errors', 'Please enter the framework description');
		return ;
	}

	return $post;
}

add_action( 'wp_insert_post_data',  'validateFrameworkDescription' );


function displayFrameworkDescriptionError() {

    $errors = get_option('my_admin_errors');

    if($errors) {
        echo '<div class="error"><p>' . $errors . '</p></div>';
    }

	update_option('my_admin_errors', false);
}

add_action( 'admin_notices', 'displayFrameworkDescriptionError' );


function disableSuccessMessage( $messages )
{
    unset($messages['post'][10]);
    return $messages;
}

add_filter( 'post_updated_messages', 'disableSuccessMessage' );


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


