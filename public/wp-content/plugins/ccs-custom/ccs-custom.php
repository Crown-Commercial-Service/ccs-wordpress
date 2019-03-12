<?php

/*
Plugin Name: CCS Custom
Description: A WordPress plugin to contain custom code for the CCS website
Version: 0.1
*/

//include('library/rest-api.php');
include('library/editor-customisation.php');
include('library/custom-taxonomies.php');
include('library/custom-post-types.php');
include('library/fewbricks-settings.php');
include('library/expose-lot-fields.php');
include('library/user-roles-fix.php');
include('library/s3-modifications.php');
include('library/theme-support.php');
include('library/remove-inline-styles-from-wysiwyg-images.php');
include('library/responsive-oembed-videos.php');
// the following can be used to customise the registration email sent to new users
//include('library/registration-email-customisation.php');
