<?php
require_once "manifest-mix.php";

// register editor styles, will be printed inline
$mixPublicPath = get_template_directory() . "/build";
add_editor_style( get_template_directory_uri() . '/build/' . mix("editor-styles.css", $mixPublicPath) );