<?php
/**
 * Social media links.
 *
 * Edited in the Customizer, section "Social media" (a sortable list of platform / URL / icon).
 * Stored as one JSON string in the theme mod "tailbase_social_links". In templates use
 * tailbase_get_social_links().
 */

const TAILBASE_MAX_SOCIAL_LINKS = 50;

/**
 * Saved links as an array of ["platform" => "", "url" => "", "icon" => ""].
 */
function tailbase_get_social_links()
{
    return tailbase_sanitize_social_links_array(json_decode((string) get_theme_mod("tailbase_social_links", "[]"), true));
}

/**
 * An icon is either a URL or the name of an SVG file in assets/icons (without the extension).
 */
function tailbase_sanitize_social_icon($icon)
{
    $icon = is_scalar($icon) ? trim((string) $icon) : "";

    if (preg_match('#^(https?:)?//#i', $icon) || str_starts_with($icon, "/")) {
        return esc_url_raw($icon);
    }

    return sanitize_file_name(preg_replace('/\.svg$/i', "", $icon));
}

function tailbase_sanitize_social_links_array($items)
{
    $out = [];

    foreach (is_array($items) ? $items : [] as $item) {
        if (!is_array($item)) {
            continue;
        }

        $platform = sanitize_text_field((string) ($item["platform"] ?? ""));
        $url = esc_url_raw((string) ($item["url"] ?? "")); // Drops javascript: and other unsafe protocols.

        if ("" === $platform || "" === $url) {
            continue;
        }

        $out[] = [
            "platform" => $platform,
            "url" => $url,
            "icon" => tailbase_sanitize_social_icon($item["icon"] ?? ""),
        ];

        if (count($out) >= TAILBASE_MAX_SOCIAL_LINKS) {
            break;
        }
    }

    return $out;
}

function tailbase_sanitize_social_links($value)
{
    return wp_json_encode(tailbase_sanitize_social_links_array(json_decode((string) $value, true)));
}

function tailbase_customize_social_links($wp_customize)
{
    $wp_customize->add_section("tailbase_social", [
        "title" => "Social media",
        "description" => "Links to social media profiles. Use the arrows to change the order.",
        "priority" => 192,
    ]);

    $wp_customize->add_setting("tailbase_social_links", [
        "type" => "theme_mod",
        "default" => "[]",
        "sanitize_callback" => "tailbase_sanitize_social_links",
    ]);

    $wp_customize->add_control(
        new Tailbase_Social_Links_Control($wp_customize, "tailbase_social_links", [
            "label" => "Profiles",
            "section" => "tailbase_social",
        ])
    );
}

/**
 * The list itself is drawn by assets/js/customizer-social.js.
 */
function tailbase_define_social_control()
{
    class Tailbase_Social_Links_Control extends WP_Customize_Control
    {
        public $type = "tailbase_social_links";

        public function enqueue()
        {
            wp_enqueue_script(
                "tailbase-customizer-social",
                get_template_directory_uri() . "/assets/js/customizer-social.js",
                ["customize-controls"],
                filemtime(get_template_directory() . "/assets/js/customizer-social.js"),
                true
            );
        }

        public function render_content()
        {
            ?>
            <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <div class="tailbase-social-list"></div>
            <?php
        }
    }
}
add_action("customize_register", function ($wp_customize) {
    tailbase_define_social_control();
    tailbase_customize_social_links($wp_customize);
});
