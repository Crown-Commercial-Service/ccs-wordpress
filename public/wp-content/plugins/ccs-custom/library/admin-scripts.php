<?php

function add_usersnap_code() {

    wp_enqueue_script('ccs_admin_modifications', plugin_dir_url(__FILE__) .'../js/ccs-admin-modificaitons.js', array(), '1.0', true);

    $script = <<<EOD
(function() {
    var s = document.createElement("script");
    s.type = "text/javascript";
    s.async = true;
    s.src = '//api.usersnap.com/load/c42d67c2-699b-4699-973f-78255aad58db.js';
    var x = document.getElementsByTagName('script')[0];
    x.parentNode.insertBefore(s, x);
})();
EOD;

    wp_add_inline_script('ccs_admin_modifications', $script);
}

add_action('admin_enqueue_scripts', 'add_usersnap_code');
