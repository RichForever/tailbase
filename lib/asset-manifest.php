<?php
/**
 * Resolve a built asset (e.g. "app-styles.css") to its hashed file using the
 * webpack manifest in /build. tailbase_asset_path() returns the theme-relative
 * path, tailbase_asset() the full URL.
 */
function tailbase_asset_path($file)
{
    static $manifest;

    if (!$manifest) {
        $manifestPath = get_template_directory() . "/build/manifest.json";

        if (!file_exists($manifestPath)) {
            throw new Exception("The asset manifest does not exist.");
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
    }

    if (!array_key_exists($file, $manifest)) {
        throw new Exception(
            "Unable to locate asset: {$file}. Please check your webpack output paths and try again."
        );
    }

    return "build/" . $manifest[$file];
}

function tailbase_asset($file)
{
    return get_template_directory_uri() . "/" . tailbase_asset_path($file);
}
