<?php
/**
 * Theme Customizer.
 *
 * - "Custom scripts" panel (Header / Body / Footer sections): code printed in <head>,
 *   right after <body> opens and before </body>.
 * - "Theme settings" section: general settings, e.g. custom body classes.
 */

/**
 * Places: key => [label, hook].
 */
function tailbase_custom_scripts_places()
{
    return [
        "head" => ["Header", "wp_head"],
        "body" => ["Body", "wp_body_open"],
        "footer" => ["Footer", "wp_footer"],
    ];
}

function tailbase_sanitize_script($value)
{
    // Scripts are code: only users who may post unfiltered HTML can save them.
    return current_user_can("unfiltered_html")
        ? (string) $value
        : wp_kses_post((string) $value);
}

function tailbase_sanitize_body_classes($value)
{
    $classes = array_filter(
        array_map(
            "sanitize_html_class",
            preg_split("/\s+/", trim((string) $value)),
        ),
    );

    return implode(" ", array_unique($classes));
}

function tailbase_customize_register($wp_customize)
{
    // Custom scripts: panel > section per place > code field.
    $wp_customize->add_panel("tailbase_custom_scripts", [
        "title" => "Custom scripts",
        "description" =>
            "Scripts and tags (analytics, pixels, etc.) printed on every page.",
        "priority" => 200,
    ]);

    foreach (tailbase_custom_scripts_places() as $key => [$label]) {
        $wp_customize->add_section("tailbase_scripts_{$key}", [
            "title" => $label,
            "panel" => "tailbase_custom_scripts",
        ]);

        $wp_customize->add_setting("tailbase_scripts_{$key}", [
            "type" => "theme_mod",
            "default" => "",
            "sanitize_callback" => "tailbase_sanitize_script",
        ]);

        $wp_customize->add_control(
            new WP_Customize_Code_Editor_Control(
                $wp_customize,
                "tailbase_scripts_{$key}",
                [
                    "label" => "{$label} scripts",
                    "section" => "tailbase_scripts_{$key}",
                    "code_type" => "text/html",
                ],
            ),
        );
    }

    // General theme settings.
    $wp_customize->add_section("tailbase_theme_settings", [
        "title" => "Theme settings",
        "priority" => 190,
    ]);

    $wp_customize->add_setting("tailbase_body_classes", [
        "type" => "theme_mod",
        "default" => "",
        "sanitize_callback" => "tailbase_sanitize_body_classes",
    ]);

    $wp_customize->add_control("tailbase_body_classes", [
        "label" => "Custom body classes",
        "description" =>
            "CSS classes added to the body tag, separated by spaces.",
        "section" => "tailbase_theme_settings",
        "type" => "text",
    ]);
}
add_action("customize_register", "tailbase_customize_register");

function tailbase_output_custom_scripts()
{
    $places = tailbase_custom_scripts_places();
    $key = array_search(current_action(), array_column($places, 1), true);

    if (false === $key) {
        return;
    }

    $key = array_keys($places)[$key];
    $content = get_theme_mod("tailbase_scripts_{$key}", "");

    if ("" !== trim((string) $content)) {
        echo $content . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- raw scripts by design.
    }
}
foreach (tailbase_custom_scripts_places() as [$label, $hook]) {
    add_action($hook, "tailbase_output_custom_scripts");
}

function tailbase_custom_body_classes($classes)
{
    $custom = get_theme_mod("tailbase_body_classes", "");

    return array_merge(
        $classes,
        array_filter(preg_split("/\s+/", trim((string) $custom))),
    );
}
add_filter("body_class", "tailbase_custom_body_classes");
