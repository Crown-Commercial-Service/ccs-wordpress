<?php
/**
 * A basic health check script used to check if the site is running without problems
 */

define('WP_USE_THEMES', false);
require('../wp-load.php');

$siteName = get_bloginfo('name');

if(!empty($siteName)) {
    echo 'PASS';
} else {
    header('HTTP/ 503 Health Check Failed');
    echo 'FAIL';
}
