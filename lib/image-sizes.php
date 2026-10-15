<?php
function tailbase_register_image_sizes()
{
    //add_image_size( 'image-480', 480, 9999 );
    //add_image_size( 'image-480-square', 480, 480, true );
    //add_image_size( 'image-640', 640, 9999 );
    //add_image_size( 'image-640-square', 640, 640, true );
    //add_image_size( 'image-720', 720, 9999 );
    //add_image_size( 'image-720-square', 720, 720, true );
    //add_image_size( 'image-960', 960, 9999 );
    //add_image_size( 'image-960-square', 960, 960, true );
    //add_image_size( 'image-1168', 1168, 9999 );
    //add_image_size( 'image-1440', 1440, 9999 );
    //add_image_size( 'image-1920', 1920, 9999 );
}
add_action("init", "tailbase_register_image_sizes");
