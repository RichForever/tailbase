<?php
function tailbase_register_post_types()
{
    // Example
    // $labels = [
    //     "name" => "Example",
    //     "singular_name" => "Example",
    // ];

    // $args = [
    //     "label" => "Example",
    //     "labels" => $labels,
    //     "description" => "",
    //     "public" => true,
    //     "publicly_queryable" => true,
    //     "show_ui" => true,
    //     "show_in_rest" => true,
    //     "rest_base" => "",
    //     "rest_controller_class" => "WP_REST_Posts_Controller",
    //     "has_archive" => false,
    //     "show_in_menu" => true,
    //     "show_in_nav_menus" => true,
    //     "delete_with_user" => false,
    //     "exclude_from_search" => false,
    //     "capability_type" => "post",
    //     "map_meta_cap" => true,
    //     "hierarchical" => false,
    //     "rewrite" => ["slug" => "example", "with_front" => true],
    //     "query_var" => true,
    //     "menu_icon" => "dashicons-groups",
    //     "supports" => ["title"],
    // ];

    // register_post_type("example", $args);
}
add_action("init", "tailbase_register_post_types");
