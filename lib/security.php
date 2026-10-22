<?php
/**
 * Security and hardening options.
 *
 * Edited in the Customizer, section "Security".
 */

/**
 * Options: key => [label, description, default].
 */
function tailbase_security_options()
{
    return [
        "hide_wp_version" => [
            "Hide WordPress version",
            "Remove the WordPress version number from the site's frontend code and feeds.",
            true,
        ],
        "disable_xmlrpc" => [
            "Disable XML-RPC",
            "Turn off the XML-RPC API (a common target of brute force and pingback abuse).",
            true,
        ],
        "disable_file_edit" => [
            "Disable plugin and theme editor",
            "Remove the code editor from the admin panel.",
            true,
        ],
        "enable_svg" => [
            "Allow SVG uploads",
            "Only users who can post unfiltered HTML can upload SVG files, and files with scripts are rejected. Upload only SVG files you trust.",
            false,
        ],
        "block_user_enumeration" => [
            "Block user enumeration",
            "Stop visitors from listing usernames with ?author=1 and the public REST users endpoint.",
            true,
        ],
        "security_headers" => [
            "Send security headers",
            "Adds X-Content-Type-Options, X-Frame-Options and Referrer-Policy headers.",
            true,
        ],
    ];
}

/**
 * Current value of an option: the theme mod, else the default.
 */
function tailbase_security_enabled($key)
{
    return (bool) get_theme_mod(
        "tailbase_security_{$key}",
        tailbase_security_options()[$key][2],
    );
}

function tailbase_customize_security($wp_customize)
{
    $wp_customize->add_section("tailbase_security", [
        "title" => "Security",
        "description" => "Hardening options for the site.",
        "priority" => 195,
        "capability" => "manage_options",
    ]);

    foreach (
        tailbase_security_options()
        as $key => [$label, $description, $default]
    ) {
        $wp_customize->add_setting("tailbase_security_{$key}", [
            "type" => "theme_mod",
            "default" => $default,
            "capability" => "manage_options",
            "sanitize_callback" => "wp_validate_boolean",
        ]);

        $wp_customize->add_control("tailbase_security_{$key}", [
            "label" => $label,
            "description" => $description,
            "section" => "tailbase_security",
            "type" => "checkbox",
        ]);
    }
}
add_action("customize_register", "tailbase_customize_security");

// Hide the WordPress version (the generator tag itself is removed in head-cleanup.php).
add_filter("the_generator", function ($generator) {
    return tailbase_security_enabled("hide_wp_version") ? "" : $generator;
});

add_filter("xmlrpc_enabled", function ($enabled) {
    return tailbase_security_enabled("disable_xmlrpc") ? false : $enabled;
});

if (
    !defined("DISALLOW_FILE_EDIT") &&
    tailbase_security_enabled("disable_file_edit")
) {
    define("DISALLOW_FILE_EDIT", true);
}

/**
 * SVG uploads. SVG files can contain scripts, so they are only allowed for users who may post
 * unfiltered HTML (administrators on a single site), never for authors or editors.
 */
function tailbase_svg_allowed()
{
    return tailbase_security_enabled("enable_svg") &&
        current_user_can("unfiltered_html");
}

add_filter("upload_mimes", function ($mimes) {
    if (tailbase_svg_allowed()) {
        $mimes["svg"] = "image/svg+xml";
    }

    return $mimes;
});

// WordPress cannot always detect the type of an SVG from its content, so trust the extension.
add_filter(
    "wp_check_filetype_and_ext",
    function ($data, $file, $filename) {
        if (!empty($data["ext"]) && !empty($data["type"])) {
            return $data;
        }

        if (
            tailbase_svg_allowed() &&
            "svg" ===
                strtolower(pathinfo((string) $filename, PATHINFO_EXTENSION))
        ) {
            $data["ext"] = "svg";
            $data["type"] = "image/svg+xml";
        }

        return $data;
    },
    10,
    3,
);

// Basic safety net, not a full sanitizer: rejects SVG files that contain scripts or event handlers.
add_filter("wp_handle_upload_prefilter", function ($file) {
    $name = isset($file["name"]) ? (string) $file["name"] : "";

    if (
        "svg" !== strtolower(pathinfo($name, PATHINFO_EXTENSION)) ||
        empty($file["tmp_name"])
    ) {
        return $file;
    }

    $contents = file_get_contents(
        $file["tmp_name"],
        false,
        null,
        0,
        2 * MB_IN_BYTES,
    );

    if (
        false === $contents ||
        preg_match(
            "/<script|<foreignObject|<iframe|<embed|<object|<!ENTITY|javascript:|data:text\/html|\son[a-z]+\s*=/i",
            $contents,
        )
    ) {
        $file["error"] =
            "This SVG file contains potentially unsafe code and was rejected.";
    }

    return $file;
});

// User enumeration: ?author=1 redirects to /author/username/, and the REST API lists users.
add_action(
    "template_redirect",
    function () {
        if (
            tailbase_security_enabled("block_user_enumeration") &&
            isset($_GET["author"]) &&
            !is_user_logged_in()
        ) {
            wp_safe_redirect(home_url("/"), 301);
            exit();
        }
    },
    1, // Before redirect_canonical() turns ?author=1 into /author/username/.
);

add_filter("rest_endpoints", function ($endpoints) {
    if (
        tailbase_security_enabled("block_user_enumeration") &&
        !is_user_logged_in()
    ) {
        unset(
            $endpoints["/wp/v2/users"],
            $endpoints["/wp/v2/users/(?P<id>[\d]+)"],
        );
    }

    return $endpoints;
});

add_filter("wp_headers", function ($headers) {
    if (tailbase_security_enabled("security_headers")) {
        $headers["X-Content-Type-Options"] = "nosniff";
        $headers["X-Frame-Options"] = "SAMEORIGIN";
        $headers["Referrer-Policy"] = "strict-origin-when-cross-origin";
    }

    return $headers;
});

/**
 * Blocks PHP execution in the uploads directory (Apache), so a malicious file that gets uploaded cannot run.
 * Writes uploads/.htaccess once, from the admin. On nginx add the equivalent rule to the server config:
 * location ~* ^/wp-content/uploads/.*\.php$ { deny all; }
 */
function tailbase_block_php_in_uploads()
{
    $uploads = wp_get_upload_dir();
    $file = trailingslashit($uploads["basedir"]) . ".htaccess";

    if (!empty($uploads["error"]) || file_exists($file)) {
        return;
    }

    $rules = [
        '<FilesMatch "\.(php|phtml|php[0-9]|phar|pl|py|cgi)$">',
        "    <IfModule mod_authz_core.c>",
        "        Require all denied",
        "    </IfModule>",
        "    <IfModule !mod_authz_core.c>",
        "        Order allow,deny",
        "        Deny from all",
        "    </IfModule>",
        "</FilesMatch>",
    ];

    insert_with_markers($file, "Tailbase", $rules);
}
add_action("admin_init", "tailbase_block_php_in_uploads");
