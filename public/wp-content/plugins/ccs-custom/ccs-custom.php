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
include('library/image-sizes.php');
include('library/headless-cms.php');
include('library/options-page.php');
include('library/custom-revisionise.php');
include('library/admin-styles.php');
include('library/admin-scripts.php');
include('library/custom-queries.php');
include('library/restrict-api.php');
include('library/rest-api-modifications.php');

//include('library/usersnap.php');

// the following can be used to customise the registration email sent to new users
//include('library/registration-email-customisation.php');
