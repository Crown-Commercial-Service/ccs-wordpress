<?php

/**
 * @param $args
 * @return mixed
 *
 * Remove cache-control and expires response headers from user uploaded files
 * as this has the potential to create problems (and confusion for the client,
 * as they will be unaware that this is how the caching works)
 */
function ccs_modify_s3_cache_control($args) {
    unset($args['CacheControl']);
    unset($args['Expires']);

    return $args;
}
add_filter('as3cf_object_meta', 'ccs_modify_s3_cache_control');
