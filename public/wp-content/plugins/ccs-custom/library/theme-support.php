<?php

add_action( 'after_setup_theme' , 'ccs_theme_support' );

function ccs_theme_support() {
    add_theme_support('post-thumbnails' , array('post') );
}
