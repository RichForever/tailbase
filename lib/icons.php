<?php
function tailbase_get_icon($filename)
{
    $path = get_stylesheet_directory() . "/assets/icons/" . $filename . ".svg";
    if (file_exists($path)) {
        return file_get_contents($path);
    }
}
