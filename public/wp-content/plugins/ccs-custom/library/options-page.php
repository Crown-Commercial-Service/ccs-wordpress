<?php

acf_add_options_page( array(

    /* (string) The title displayed on the options page. Required. */
    'page_title' => 'eSourcing Training Dates',

    /* (string) The title displayed in the wp-admin sidebar. Defaults to page_title */
    'menu_title' => 'eSourcing Training Dates',

    /* (string) The URL slug used to uniquely identify this options page.
    Defaults to a url friendly version of menu_title */
    'menu_slug' => 'esourcing-training-dates',

    /* (string) The capability required for this menu to be displayed to the user. Defaults to edit_posts.
    Read more about capability here: http://codex.wordpress.org/Roles_and_Capabilities */
    'capability' => 'edit_pages',

    /* (int|string) The position in the menu order this menu should appear.
    WARNING: if two menu items use the same position attribute, one of the items may be overwritten so that only one item displays!
    Risk of conflict can be reduced by using decimal instead of integer values, e.g. '63.3' instead of 63 (must use quotes).
    Defaults to bottom of utility menu items */
    'position' => 50,

    /* (string) The slug of another WP admin page. if set, this will become a child page. */
    'parent_slug' => '',

    /* (string) The icon class for this menu. Defaults to default WordPress gear.
    Read more about dashicons here: https://developer.wordpress.org/resource/dashicons/ */
    'icon_url' => "dashicons-calendar",

    /* (boolean) If set to true, this options page will redirect to the first child page (if a child page exists).
    If set to false, this parent page will appear alongside any child pages. Defaults to true */
    'redirect' => true,

    /* (int|string) The '$post_id' to save/load data to/from. Can be set to a numeric post ID (123), or a string ('user_2').
    Defaults to 'options'. Added in v5.2.7 */
    'post_id' => 'options',

    /* (boolean)  Whether to load the option (values saved from this options page) when WordPress starts up.
    Defaults to false. Added in v5.2.8. */
    'autoload' => true,

    /* (string) The update button text. Added in v5.3.7. */
    'update_button'		=> __('Update training dates', 'acf'),

    /* (string) The message shown above the form on submit. Added in v5.6.0. */
    'updated_message'	=> __("Training Dates Updated", 'acf'),

) );

acf_add_options_page( array(

    /* (string) The title displayed on the options page. Required. */
    'page_title' => 'Option Cards',

    /* (string) The title displayed in the wp-admin sidebar. Defaults to page_title */
    'menu_title' => 'Page Option Cards',

    /* (string) The URL slug used to uniquely identify this options page.
    Defaults to a url friendly version of menu_title */
    'menu_slug' => 'option-cards',

    /* (string) The capability required for this menu to be displayed to the user. Defaults to edit_posts.
    Read more about capability here: http://codex.wordpress.org/Roles_and_Capabilities */
    'capability' => 'edit_pages',

    /* (int|string) The position in the menu order this menu should appear.
    WARNING: if two menu items use the same position attribute, one of the items may be overwritten so that only one item displays!
    Risk of conflict can be reduced by using decimal instead of integer values, e.g. '63.3' instead of 63 (must use quotes).
    Defaults to bottom of utility menu items */
    'position' => 50.1,

    /* (string) The slug of another WP admin page. if set, this will become a child page. */
    'parent_slug' => '',

    /* (string) The icon class for this menu. Defaults to default WordPress gear.
    Read more about dashicons here: https://developer.wordpress.org/resource/dashicons/ */
    'icon_url' => false,

    /* (boolean) If set to true, this options page will redirect to the first child page (if a child page exists).
    If set to false, this parent page will appear alongside any child pages. Defaults to true */
    'redirect' => true,

    /* (int|string) The '$post_id' to save/load data to/from. Can be set to a numeric post ID (123), or a string ('user_2').
    Defaults to 'options'. Added in v5.2.7 */
    'post_id' => 'options',

    /* (boolean)  Whether to load the option (values saved from this options page) when WordPress starts up.
    Defaults to false. Added in v5.2.8. */
    'autoload' => true,

    /* (string) The update button text. Added in v5.3.7. */
    'update_button'		=> __('Update', 'acf'),

    /* (string) The message shown above the form on submit. Added in v5.6.0. */
    'updated_message'	=> __("Option Cards Updated", 'acf'),

) );

acf_add_options_page( array(

    /* (string) The title displayed on the options page. Required. */
    'page_title' => 'Upcoming Deals Page',

    /* (string) The title displayed in the wp-admin sidebar. Defaults to page_title */
    'menu_title' => 'Upcoming Deals Page',

    /* (string) The URL slug used to uniquely identify this options page.
    Defaults to a url friendly version of menu_title */
    'menu_slug' => 'upcoming-deals-page',

    /* (string) The capability required for this menu to be displayed to the user. Defaults to edit_posts.
    Read more about capability here: http://codex.wordpress.org/Roles_and_Capabilities */
    'capability' => 'edit_pages',

    /* (int|string) The position in the menu order this menu should appear.
    WARNING: if two menu items use the same position attribute, one of the items may be overwritten so that only one item displays!
    Risk of conflict can be reduced by using decimal instead of integer values, e.g. '63.3' instead of 63 (must use quotes).
    Defaults to bottom of utility menu items */
    'position' => 50.2,

    /* (string) The slug of another WP admin page. if set, this will become a child page. */
    'parent_slug' => '',

    /* (string) The icon class for this menu. Defaults to default WordPress gear.
    Read more about dashicons here: https://developer.wordpress.org/resource/dashicons/ */
    'icon_url' => false,

    /* (boolean) If set to true, this options page will redirect to the first child page (if a child page exists).
    If set to false, this parent page will appear alongside any child pages. Defaults to true */
    'redirect' => true,

    /* (int|string) The '$post_id' to save/load data to/from. Can be set to a numeric post ID (123), or a string ('user_2').
    Defaults to 'options'. Added in v5.2.7 */
    'post_id' => 'options',

    /* (boolean)  Whether to load the option (values saved from this options page) when WordPress starts up.
    Defaults to false. Added in v5.2.8. */
    'autoload' => true,

    /* (string) The update button text. Added in v5.3.7. */
    'update_button'		=> __('Update', 'acf'),

    /* (string) The message shown above the form on submit. Added in v5.6.0. */
    'updated_message'	=> __("Upcoming Deals Page Updated", 'acf'),

) );

acf_add_options_page( array(

    /* (string) The title displayed on the options page. Required. */
    'page_title' => 'Homepage Components',

    /* (string) The title displayed in the wp-admin sidebar. Defaults to page_title */
    'menu_title' => 'Homepage Components',

    /* (string) The URL slug used to uniquely identify this options page.
    Defaults to a url friendly version of menu_title */
    'menu_slug' => 'homepage-components',

    /* (string) The capability required for this menu to be displayed to the user. Defaults to edit_posts.
    Read more about capability here: http://codex.wordpress.org/Roles_and_Capabilities */
    'capability' => 'edit_pages',

    /* (int|string) The position in the menu order this menu should appear.
    WARNING: if two menu items use the same position attribute, one of the items may be overwritten so that only one item displays!
    Risk of conflict can be reduced by using decimal instead of integer values, e.g. '63.3' instead of 63 (must use quotes).
    Defaults to bottom of utility menu items */
    'position' => 50.3,

    /* (string) The slug of another WP admin page. if set, this will become a child page. */
    'parent_slug' => '',

    /* (string) The icon class for this menu. Defaults to default WordPress gear.
    Read more about dashicons here: https://developer.wordpress.org/resource/dashicons/ */
    'icon_url' => false,

    /* (boolean) If set to true, this options page will redirect to the first child page (if a child page exists).
    If set to false, this parent page will appear alongside any child pages. Defaults to true */
    'redirect' => false,

    /* (int|string) The '$post_id' to save/load data to/from. Can be set to a numeric post ID (123), or a string ('user_2').
    Defaults to 'options'. Added in v5.2.7 */
    'post_id' => 'options',

    /* (boolean)  Whether to load the option (values saved from this options page) when WordPress starts up.
    Defaults to false. Added in v5.2.8. */
    'autoload' => true,

    /* (string) The update button text. Added in v5.3.7. */
    'update_button'		=> __('Update', 'acf'),

    /* (string) The message shown above the form on submit. Added in v5.6.0. */
    'updated_message'	=> __("Homepage Components Updated", 'acf'),

) );

acf_add_options_page(array(
    'page_title'    => __('Redirection'),
    'menu_title'    => __('Redirection'),
    'menu_slug'     => 'redirection',
    'position'      => 51,
    'capability'    => 'edit_pages',
    'icon_url' => 'dashicons-external',
    'redirect'      => false,
    'update_button'		=> __('Save', 'acf'),
    'updated_message'	=> __("Redirection Saved", 'acf')
));

acf_add_options_page(array(
    'page_title'    => __('Glossary'),
    'menu_title'    => __('Glossary'),
    'menu_slug'     => 'glossary',
    'position'      => 52,
    'capability'    => 'edit_pages',
    'icon_url' => 'dashicons-tag',
    'redirect'      => false,
    'update_button'		=> __('Save', 'acf'),
    'updated_message'	=> __("Glossary Saved", 'acf')
));

acf_add_options_page(array(
    'page_title'    => __('CSC Message'),
    'menu_title'    => __('CSC Message'),
    'menu_slug'     => 'CSC_Message',
    'position'      => 53,
    'capability'    => 'edit_pages',
    'icon_url' => 'dashicons-calendar',
    'redirect'      => false,
    'update_button'		=> __('Save', 'acf'),
    'updated_message'	=> __("CSC message updated", 'acf')
));

acf_add_options_page(array(
    'page_title'    => __('Homepage Banner'),
    'menu_title'    => __('Homepage Banner'),
    'menu_slug'     => 'Message_Banner',
    'parent_slug'   => 'homepage-components',
    'capability'    => 'edit_users',
    'icon_url'      => 'dashicons-tag',
    'redirect'      => false,
    'update_button' => __('Save', 'acf'),
    'updated_message'   => __("Message Banner Updated", 'acf')
));