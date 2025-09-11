<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$PATH_TO_LOG = '/var/log/user-activity-log.txt';

if ( file_exists($PATH_TO_LOG) && is_writable($PATH_TO_LOG) ) {
    define( 'LOG_FILE', $PATH_TO_LOG );
} else {
    define( 'LOG_FILE', __DIR__ . '/user-activity-log.txt' );
}

add_action('wp_login', 'user_login', 10, 2);
add_action('user_register', 'user_created', 10, 2);
add_action('password_reset', 'user_password_reset', 10, 2);
add_action( 'delete_user', 'user_deleted' );

function user_login( $user_login, $user ) {
    logEvent( "Username: {$user->data->user_nicename} logged in" );
}

function user_created( $user_id ) {
    $user = get_userdata( $user_id );
    $current_user = wp_get_current_user();

    if ( $user && $current_user && $current_user->ID != 0 ) {
        $acting_user = $current_user->user_login;
        logEvent( "New user created with ID=$user_id, Username={$user->user_login} | Actioned by: $acting_user" );
    }
}

function user_password_reset( $user_id, $new_password ) {
    $user = get_userdata( $user_id->ID );
    logEvent( "New password set for user: {$user->user_login}" );
}

function user_deleted( $user_id ) {
    $user = get_userdata( $user_id );
    $current_user = wp_get_current_user();

    if ( $user && $current_user && $current_user->ID != 0 ) {
        $acting_user = $current_user->user_login;
        logEvent( "User deleted with ID=$user_id, Username={$user->user_login} | Actioned by: $acting_user" );
    }
}


function ual_add_admin_menu() {
    add_menu_page(
        'User Activity Log',
        'User Activity Log',
        'manage_options',
        'ual-user-activity-log',
        'ual_display_log_page',
        'dashicons-list-view',
        80
    );
}
add_action( 'admin_menu', 'ual_add_admin_menu' );


function logEvent( $message ) {
    date_default_timezone_set('Europe/London');
    $date = date( 'Y-m-d H:i:s' );
    file_put_contents( LOG_FILE, "[$date] $message\n", FILE_APPEND );
}

function ual_display_log_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    echo '<div class="wrap">';
    echo '<h1>User Activity Log</h1>';

    if ( file_exists( LOG_FILE ) ) {
        $lines = file(LOG_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $reversed_lines = array_reverse($lines);

        $log_contents = esc_textarea( implode("\n", $reversed_lines) );
        echo '<textarea style="width:100%; height:500px;" readonly rows="20" cols="100">' . $log_contents . '</textarea>';
    } else {
        echo '<p>No log data available.</p>';
    }
    echo '</div>';
}
?>