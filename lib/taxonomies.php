<?php
function tailbase_register_taxonomies()
{
    // Example
    // $labels = [
    //     "name" => "Example",
    //     "singular_name" => "Example",
    // ];

    // $args = [
    //     "label" => "Example",
    //     "labels" => $labels,
    //     "public" => true,
    //     "publicly_queryable" => true,
    //     "hierarchical" => false,
    //     "show_ui" => true,
    //     "show_in_menu" => true,
    //     "show_in_nav_menus" => true,
    //     "query_var" => true,
    //     "rewrite" => ['slug' => 'example', 'with_front' => true,],
    //     "show_admin_column" => false,
    //     "show_in_rest" => true,
    //     "rest_base" => "example",
    //     "rest_controller_class" => "WP_REST_Terms_Controller",
    //     "show_in_quick_edit" => false,
    // ];

    // register_taxonomy("example", ["posttype"], $args);
}
add_action("init", "tailbase_register_taxonomies");
