<?php

/**
 * @package WordPress
 * @subpackage Tailbase
 * @since Tailbase 1.0.0
 */

$tailbase_modules = [
    "asset-manifest",
    "assets",
    "editor-styles",
    "head-cleanup",
    "icons",
    "image-sizes",
    "meta-description",
    "nav-menus",
    "plugin-compat",
    "post-types",
    "sidebars",
    "taxonomies",
    "theme-support",
];

foreach ($tailbase_modules as $module) {
    require_once get_template_directory() . "/lib/{$module}.php";
}

function tailbase_custom_login_error_message()
{
    return "Błąd!";
}
add_filter("login_errors", "tailbase_custom_login_error_message");

// load only on local
if ("local" === wp_get_environment_type()) {
    add_action("phpmailer_init", function ($phpmailer) {
        $phpmailer->Host = "mailhog";
        $phpmailer->Port = 1025;
        $phpmailer->IsSMTP();
    });
}
