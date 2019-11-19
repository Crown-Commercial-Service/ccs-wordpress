<?php

function admin_style() {
    $plugin_library_directory = plugin_dir_url( __FILE__ );
    wp_enqueue_style('admin-styles', $plugin_library_directory . '../css/admin.css');
}
add_action('admin_enqueue_scripts', 'admin_style');
