<?php
function tailbase_register_nav_menus()
{
    register_nav_menus([
        "main_menu" => "Main Menu",
        "footer_menu" => "Footer Menu",
    ]);
}
add_action("init", "tailbase_register_nav_menus");
