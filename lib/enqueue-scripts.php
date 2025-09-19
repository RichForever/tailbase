<?php
require_once "manifest-mix.php";

wp_dequeue_style("wp-block-library");
wp_dequeue_style("wp-block-library-theme");
wp_dequeue_style("wc-block-style");
if (!is_admin()) {
    wp_dequeue_script("jquery");
}

$mixPublicPath = get_template_directory() . "/build";

// Tailwind styles
wp_enqueue_style(
    "app-styles",
    get_template_directory_uri() .
        "/build/" .
        mix("app-styles.css", $mixPublicPath)
);

// Theme specific styles
wp_enqueue_style(
    "theme-styles",
    get_template_directory_uri() . "/build/" . mix("theme-styles.css", $mixPublicPath)
);

// Theme specific scripts
wp_enqueue_script(
    "app-scripts",
    get_template_directory_uri() .
        "/build/" .
        mix("app-scripts.js", $mixPublicPath),
    [],
    "",
    true
);

wp_localize_script("app", "wp", [
    "siteUrl" => home_url(),
]);
