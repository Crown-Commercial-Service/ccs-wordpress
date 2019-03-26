<?php

//Modify TinyMCE editor to hide H1.
function ccs_tiny_mce_remove_unused_formats( $initFormats ) {
    // Add block format elements you want to show in dropdown
    $initFormats['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4';
    return $initFormats;
}
add_filter( 'tiny_mce_before_init', 'ccs_tiny_mce_remove_unused_formats' );

// Add help text beneath framework & lot title
add_action( 'edit_form_after_title', function(WP_Post $post) {
    if ($post->post_type == 'framework') {
        echo '<div style="padding: 5px 10px; color: rgb(102,102,102);"><strong>Note:</strong> Framework title is not used on the frontend site and is only for informational purposes</div>';
    }
    if ($post->post_type == 'lot') {
        echo '<div style="padding: 5px 10px; color: rgb(102,102,102);"><strong>Note:</strong> Lot title is not used on the frontend site and is only for informational purposes</div>';
    }
});
