<?php

class RevisionizeContributorsCan extends RevisionizeAddon {
  function name() {
    return 'contributors_can';
  }

  function version() {
    return '1.0.1';
  }

  function init() {
    add_action('admin_init', array($this, 'setup_settings'));
    add_action('admin_enqueue_scripts', array($this, 'check_capabilities'));
    add_action('admin_action_editpost', array($this, 'check_capabilities'));
    add_filter('revisionize_is_create_enabled', array($this, 'is_create_enabled'), 10, 2);
    add_filter('revisionize_show_dashboard_widget', array($this, 'filter_show_dashboard_widget'));
  }

  function is_create_enabled($is_enabled, $post) {
    $is_enabled = $post->post_status == 'publish' && !\Revisionize\get_revision_of($post) && ($post->post_type=='page' && current_user_can('edit_pages') || $post->post_type!='page' && current_user_can('edit_posts'));
    
    if ($is_enabled && !current_user_can('edit_post', $post->ID)) {
      $max = intval(\Revisionize\get_setting('max_revisions', 0));
      if ($max > 0) {
        // get number of revisions made for this post by this user. 
        $revisions = get_posts(array(
          'posts_per_page' => -1,
          'author' => get_current_user_id(),
          'meta_key' => '_post_revision_of',
          'meta_value' => $post->ID,
          'post_type' => 'any',
          'post_status' => array('draft', 'pending'),
        ));
        $is_enabled = count($revisions) < $max;
      }
    }
    return $is_enabled;
  }  

  function setup_settings() {
    add_settings_section('revisionize_section_contributors', '', '__return_null', 'revisionize');

    \Revisionize\input_setting('number', 'Maximum Revisions', 'max_revisions', "The maximum number of revisions contributors can make per post. 0 for unlimited.", 0, 'revisionize_section_contributors');
  
    \Revisionize\input_setting('checkbox', 'Show Dashboard Panel', 'show_dashboard_widget', "Show a dashboard panel that lists Revisionized posts that are pending review.", true, 'revisionize_section_contributors');    

    add_action('revisionize_settings_fields', array($this, 'settings_fields'), 9);
  }

  function settings_fields() {
    \Revisionize\do_fields_section('revisionize_section_contributors');
  }

  function filter_show_dashboard_widget($b) {
    return \Revisionize\is_checkbox_checked('show_dashboard_widget', true);
  }

  // if the current user cannot edit the post it is revisioning, then don't 
  // allow him to publish the revision.
  function check_capabilities() {
    global $post;

    if (empty($post) && !empty($_REQUEST['post_ID'])) {
      $post = get_post($_REQUEST['post_ID']);
    }
    if (!empty($post) && current_user_can('publish_post', $post->ID)) {
      $parent = \Revisionize\get_parent_post($post);
      if ($parent && !current_user_can('edit_post', $parent->ID)) {
        add_filter('user_has_cap', array($this, 'remove_publish_capabilities'));      
      }
    }
  }

  function remove_publish_capabilities($caps) {
    unset($caps['publish_posts']);
    unset($caps['publish_pages']);
    return $caps;
  }
}

