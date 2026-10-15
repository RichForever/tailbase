<?php
function tailbase_configure_plugins()
{
    // Contact Form 7
    //add_filter( 'wpcf7_load_js', '__return_false' );
    //add_filter( 'wpcf7_load_css', '__return_false' );
    add_filter("wpcf7_autop_or_not", "__return_false");
}
add_action("after_setup_theme", "tailbase_configure_plugins");
