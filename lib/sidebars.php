<?php
function tailbase_register_sidebars()
{
    // register_sidebar( array(
    //     'name' => 'Example',
    //     'id' => 'example',
    //     'before_widget' => '',
    //     'after_widget' => '',
    //     'before_title' => '<h3>',
    //     'after_title' => '</h3>',
    // ) );
}
add_action("widgets_init", "tailbase_register_sidebars");
