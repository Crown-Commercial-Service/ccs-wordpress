<?php

/**
 * Return the RM Number for a framework if it is set
 *
 * @param $wordpress_framework_id
 * @return string|null
 */
function ccs_get_rm_number($wordpress_framework_id) {
    global $wpdb;

    $rm_number = $wpdb->get_var($wpdb->prepare(
        "SELECT 
                    rm_number 
                FROM 
                    ccs_frameworks 
                WHERE 
                    wordpress_id = %s"
        , $wordpress_framework_id));

    return $rm_number;
}

/**
 * Return the text description for a framework if it has one
 *
 * @param $rm_number
 */
function ccs_get_framework_description($wordpress_framework_id) {
    global $wpdb;

    $description = $wpdb->get_var($wpdb->prepare(
        "SELECT 
                    description 
                FROM 
                    ccs_frameworks 
                WHERE 
                    wordpress_id = %s"
        , $wordpress_framework_id));

    return $description;
}

/**
 * Return the title for a framework
 *
 * @param $rm_number
 */
function ccs_get_framework_title($wordpress_framework_id) {
    global $wpdb;

    $title = $wpdb->get_var($wpdb->prepare(
        "SELECT 
                    title 
                FROM 
                    ccs_frameworks 
                WHERE 
                    wordpress_id = %s"
        , $wordpress_framework_id));

    return $title;
}
