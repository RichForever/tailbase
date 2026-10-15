<?php
/**
 * Resolve a built asset (e.g. "app-styles.css") to its hashed file name
 * using the webpack manifest in /build.
 */
function tailbase_asset($file)
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

    return get_template_directory_uri() . "/build/" . $manifest[$file];
}
