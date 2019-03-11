<?php

function ccs_new_user_email($email_details) {
    $email_details['message'] .= "\r\n\r\n" . 'This is a test lorem ipsum dolor sit amet.';

    return $email_details;
}
add_filter('wp_new_user_notification_email', 'ccs_new_user_email');
