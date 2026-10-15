<?php
require_once __DIR__ . "/asset-manifest.php";

function tailbase_enqueue_assets()
{
    wp_dequeue_style("wp-block-library");
    wp_dequeue_style("wp-block-library-theme");
    wp_dequeue_style("wc-block-style");
    if (!is_admin()) {
        wp_dequeue_script("jquery");
    }

    // Tailwind styles
    wp_enqueue_style("app-styles", tailbase_asset("app-styles.css"));

    // Theme specific styles
    wp_enqueue_style("theme-styles", tailbase_asset("theme-styles.css"));

    // Theme specific scripts
    wp_enqueue_script("app-scripts", tailbase_asset("app-scripts.js"), [], "", true);

    wp_localize_script("app-scripts", "wp", [
        "siteUrl" => home_url(),
    ]);
}
add_action("wp_enqueue_scripts", "tailbase_enqueue_assets");
