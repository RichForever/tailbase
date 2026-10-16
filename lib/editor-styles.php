<?php
require_once __DIR__ . "/asset-manifest.php";

// Register editor styles, will be printed inline. Must be a theme-relative path:
// a full URL is fetched remotely and its relative url()s (fonts) won't resolve.
function tailbase_add_editor_styles()
{
    add_editor_style(tailbase_asset_path("editor-styles.css"));
}
add_action("after_setup_theme", "tailbase_add_editor_styles");
