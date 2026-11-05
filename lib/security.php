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
            "Remove the generator tag from the page head and feeds, the RSD link, ?ver= on scripts and styles when it matches the WordPress version, and readme.html and license.txt (Apache, via .htaccess).",
            true,
        ],
        "disable_xmlrpc" => [
            "Disable XML-RPC",
            "Turn off the XML-RPC API (a common target of brute force and pingback abuse): login methods, pingbacks, the X-Pingback header and the pingback link are removed, and xmlrpc.php returns 404 (Apache, via .htaccess).",
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

/**
 * Hide the WordPress version.
 *
 * 1. Generator tag in <head>.
 * 2. Generator tag in every feed type (RSS, RSS 0.92, RDF, Atom, comment feeds, OPML, export).
 * 3. Really Simple Discovery link (only needed by XML-RPC clients).
 * 4. ?ver= on scripts and styles, only when it equals the core version.
 * 5. readme.html and license.txt in the site root (see tailbase_sync_htaccess()).
 */
add_action("after_setup_theme", function () {
    if (!tailbase_security_enabled("hide_wp_version")) {
        return;
    }

    remove_action("wp_head", "wp_generator");
    remove_action("wp_head", "rsd_link");
});

// Feeds print the generator through the_generator() for every type, so one filter covers them all.
add_filter("the_generator", function ($generator) {
    return tailbase_security_enabled("hide_wp_version") ? "" : $generator;
});

// Plugins and themes keep their own version strings, which are used for cache busting.
function tailbase_strip_core_version($src)
{
    if (
        !is_string($src) ||
        !tailbase_security_enabled("hide_wp_version") ||
        false === strpos($src, "ver=")
    ) {
        return $src;
    }

    $query = (string) wp_parse_url($src, PHP_URL_QUERY);
    parse_str($query, $args);

    if (isset($args["ver"]) && get_bloginfo("version") === $args["ver"]) {
        $src = remove_query_arg("ver", $src);
    }

    return $src;
}
add_filter("style_loader_src", "tailbase_strip_core_version", 20);
add_filter("script_loader_src", "tailbase_strip_core_version", 20);

/**
 * Disable XML-RPC. xmlrpc_enabled only stops methods that need a login, so pingbacks are removed
 * separately, the X-Pingback header and <link rel="pingback"> are dropped, and xmlrpc.php itself is
 * blocked in .htaccess (see tailbase_sync_htaccess()).
 */
add_filter("xmlrpc_enabled", function ($enabled) {
    return tailbase_security_enabled("disable_xmlrpc") ? false : $enabled;
});

add_filter("xmlrpc_methods", function ($methods) {
    if (tailbase_security_enabled("disable_xmlrpc")) {
        unset(
            $methods["pingback.ping"],
            $methods["pingback.extensions.getPingbacks"],
        );
    }

    return $methods;
});

add_filter("wp_headers", function ($headers) {
    if (tailbase_security_enabled("disable_xmlrpc")) {
        unset($headers["X-Pingback"]);
    }

    return $headers;
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
 * Root .htaccess rules (Apache), kept in their own "Tailbase" block and synced with the options:
 *  - "Hide WordPress version": 404 for readme.html and license.txt, which show the version.
 *  - "Disable XML-RPC": 404 for xmlrpc.php.
 * These are real files, so PHP never serves them and they have to be blocked by the web server.
 * Runs in the admin and only touches an existing, writable .htaccess. On nginx add the equivalent:
 * location ~* ^/(readme\.html|license\.txt|xmlrpc\.php)$ { return 404; }
 */
function tailbase_sync_htaccess()
{
    $file = trailingslashit(get_home_path()) . ".htaccess";

    if (!file_exists($file) || !is_writable($file)) {
        return;
    }

    $files = [];

    if (tailbase_security_enabled("hide_wp_version")) {
        $files[] = "readme\.html";
        $files[] = "license\.txt";
    }

    if (tailbase_security_enabled("disable_xmlrpc")) {
        $files[] = "xmlrpc\.php";
    }

    $rules = $files
        ? [
            "<IfModule mod_rewrite.c>",
            "    RewriteEngine On",
            "    RewriteRule ^(" . implode("|", $files) . ")$ - [R=404,L,NC]",
            "</IfModule>",
        ]
        : [];

    // Skip the write when the file already matches, so this costs one read per admin request.
    if (extract_from_markers($file, "Tailbase") !== $rules) {
        insert_with_markers($file, "Tailbase", $rules);
    }
}
add_action("admin_init", "tailbase_sync_htaccess");

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
