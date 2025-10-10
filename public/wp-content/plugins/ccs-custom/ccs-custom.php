<?php

/*
Plugin Name: CCS Custom
Description: A WordPress plugin to contain custom code for the CCS website
Version: 0.1
*/

include('library/custom-pages-filtering.php');
//include('library/rest-api.php');
include('library/editor-customisation.php');
include('library/custom-taxonomies.php');
include('library/custom-post-types.php');
include('library/custom-event-archived-status.php');
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
include('library/admin-styles.php');
//include('library/admin-scripts.php');
include('library/custom-queries.php');
include('library/custom-excerpts-descriptions.php');

/**
 * Rest API Modifications
 */
include('library/rest-api/restrict-api.php');
include('library/rest-api/rest-api-modifications.php');
include('library/add-acf-fields-to-rest-api.php');
include('library/rest-api/rest-api-news-navigation.php');
include('library/add-custom-metafields-to-rest-api-orderby-enumerator.php');



include('library/logger.php');




//include('library/usersnap.php');

// the following can be used to customise the registration email sent to new users
//include('library/registration-email-customisation.php');
