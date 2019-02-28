<?php

use fewbricks\bricks AS bricks;
use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;


/**
 * Import fields for the framework custom post type
 */
include('post-types/framework.php');

/**
 * Import fields for the lot custom post type
 */
include('post-types/lot.php');

/**
 * Import fields for the supplier custom post type
 */
include('post-types/supplier.php');
