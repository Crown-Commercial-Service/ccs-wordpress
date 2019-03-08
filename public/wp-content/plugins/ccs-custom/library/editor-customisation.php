<?php

//Modify TinyMCE editor to hide H1.
function ccs_tiny_mce_remove_unused_formats( $initFormats ) {
    // Add block format elements you want to show in dropdown
    $initFormats['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4';
    return $initFormats;
}
add_filter( 'tiny_mce_before_init', 'ccs_tiny_mce_remove_unused_formats' );
