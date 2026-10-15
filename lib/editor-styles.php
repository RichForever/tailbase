<?php
require_once __DIR__ . "/asset-manifest.php";

// register editor styles, will be printed inline
function tailbase_add_editor_styles()
{
    add_editor_style(tailbase_asset("editor-styles.css"));
}
add_action("after_setup_theme", "tailbase_add_editor_styles");
