<?php

// register 'archived' to the post status for 'event'
function ccs_register_archived_post_status() {
    register_post_status('archived', array(
        'label'                     => _x('Archived', 'post'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop(
            'Archived <span class="count">(%s)</span>', 
            'Archived <span class="count">(%s)</span>'),
    ));
}
add_action('init', 'ccs_register_archived_post_status');


// Display 'Archived' label on events list view
function ccs_add_archived_to_list_view($statuses, $post) {
    if ($post->post_type == 'event' && $post->post_status == 'archived') {
        $statuses['archived'] = _x('Archived', 'post');
    }
    return $statuses;
}
add_filter('display_post_states', 'ccs_add_archived_to_list_view', 10, 2);


// Add 'archived' filter on events list view
function ccs_add_archived_to_event_views($views) {
    global $wpdb, $post_type, $current_user;

    if ($post_type !== 'event') {
        return $views;
    }

    $count = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'event' AND post_status = 'archived'"
    );

    $class = (isset($_GET['post_status']) && $_GET['post_status'] === 'archived') ? 'class="current"' : '';

    $url = add_query_arg(array(
        'post_type'   => 'event',
        'post_status' => 'archived',
        'all_posts'   => 1,
    ), admin_url('edit.php'));

    $views['archived'] = sprintf(
        '<a href="%s" %s>%s <span class="count">(%d)</span></a>',
        esc_url($url),
        $class,
        __('Archived'),
        $count
    );

    return $views;
}
add_filter('views_edit-event', 'ccs_add_archived_to_event_views');



// Add 'archived' to the status dropdown in edit event 
function ccs_append_archived_to_status_dropdown() {
    global $post;
    if ($post->post_type == 'event') {
        ?>
        <script>
        jQuery(document).ready(function($){
            var $statusSelect = $('#post_status');
            if ($statusSelect.length && $statusSelect.find('option[value="archived"]').length === 0) {
                $statusSelect.append('<option value="archived"><?php echo esc_html__('Archived'); ?></option>');
            }
        });
        </script>
        <?php
    }
}
add_action('admin_footer-post.php', 'ccs_append_archived_to_status_dropdown');
add_action('admin_footer-post-new.php', 'ccs_append_archived_to_status_dropdown');


// Show 'Archived' as the status in edit event 
function ccs_show_archived_status_in_submitbox() {
    global $post;
    if ($post->post_type == 'event' && $post->post_status == 'archived') {
        ?>
        <script>
        jQuery(document).ready(function($){
            var $statusDisplay = $('#post-status-display');
            if ($statusDisplay.length) {
                $statusDisplay.text('<?php echo esc_js(__('Archived')); ?>');
            }
        });
        </script>
        <?php
    }
}
add_action('admin_footer-post.php', 'ccs_show_archived_status_in_submitbox');
add_action('admin_footer-post-new.php', 'ccs_show_archived_status_in_submitbox');


// Override the save buttton text to "Save as Archived" when 'archived' is selected
function ccs_change_save_button_for_archived() {
    global $post;
    if ($post->post_type == 'event' ) {
        
        ?>
        <script>
        jQuery(document).ready(function($){
            function updateButtonLabel() {
                var $statusSelect = $('#post_status');
                var $saveButton = $('#save-post');
                if ($statusSelect.length && $saveButton.length) {
                    if ($statusSelect.val() === 'archived') {
                        $saveButton.val('<?php echo esc_js(__('Save as Archived')); ?>');
                    } else {
                        $saveButton.val('<?php echo esc_js(__('Save Draft')); ?>');
                    }
                }
            }

            updateButtonLabel();

            $(document).on('change', '#post_status', updateButtonLabel);

            var target = document.getElementById('submitdiv');
            if (target) {
                var observer = new MutationObserver(function(mutations) {
                    updateButtonLabel();
                });
                observer.observe(target, { childList: true, subtree: true });
            }
        });
        </script>
        <?php
    }
}
add_action('admin_footer-post.php', 'ccs_change_save_button_for_archived');
add_action('admin_footer-post-new.php', 'ccs_change_save_button_for_archived');
