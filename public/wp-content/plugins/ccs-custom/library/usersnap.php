<?php

function add_usersnap_js(){
    echo '<script type="text/javascript">';
    echo '(function() { var s = document.createElement("script"); s.type = "text/javascript"; s.async = true; s.src = \'//api.usersnap.com/load/5c0d7b68-6a20-4882-8277-074dd8f65f37.js\';';
    echo 'var x = document.getElementsByTagName(\'script\')[0]; x.parentNode.insertBefore(s, x); })();';
    echo '</script>';
}
add_action( 'admin_print_scripts', 'add_usersnap_js' );
