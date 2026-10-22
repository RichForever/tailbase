<?php
/**
 * <meta name="description">
 *
 * Editors set it per page/post in the "Meta description" box. When it is empty the tag falls back to
 * the excerpt / start of the content (single), the term description (archives) or the site tagline.
 * Does nothing when an SEO plugin is active, so the tag is never printed twice.
 */
const TAILBASE_META_DESCRIPTION_KEY = "_tailbase_meta_description";

function tailbase_has_seo_plugin()
{
    return defined("WPSEO_VERSION") ||
        defined("RANK_MATH_VERSION") ||
        defined("AIOSEO_VERSION") ||
        defined("SEOPRESS_VERSION");
}

function tailbase_clean_description($text)
{
    $text = wp_strip_all_tags(strip_shortcodes((string) $text), true);
    return wp_trim_words(trim(preg_replace("/\s+/", " ", $text)), 30, "…");
}

function tailbase_get_meta_description()
{
    if (is_singular()) {
        $post = get_queried_object();
        $custom = trim(
            (string) get_post_meta(
                $post->ID,
                TAILBASE_META_DESCRIPTION_KEY,
                true,
            ),
        );
        if ($custom !== "") {
            return $custom;
        }
        $text = has_excerpt($post) ? $post->post_excerpt : $post->post_content;
        $text = tailbase_clean_description(
            has_excerpt($post) ? $text : do_blocks($text),
        );
        if ($text !== "") {
            return $text;
        }
    } elseif (is_category() || is_tag() || is_tax()) {
        $text = tailbase_clean_description(term_description());
        if ($text !== "") {
            return $text;
        }
    }

    return get_bloginfo("description");
}

function tailbase_print_meta_description()
{
    if (tailbase_has_seo_plugin() || is_404() || is_search()) {
        return;
    }
    $description = tailbase_get_meta_description();
    if ($description !== "") {
        printf(
            '<meta name="description" content="%s">' . "\n",
            esc_attr($description),
        );
    }
}
add_action("wp_head", "tailbase_print_meta_description", 1);

// Meta box on every public post type
function tailbase_add_meta_description_box()
{
    foreach (get_post_types(["public" => true]) as $post_type) {
        if ($post_type !== "attachment") {
            add_meta_box(
                "tailbase_meta_description",
                "Meta description",
                "tailbase_render_meta_description_box",
                $post_type,
                "normal",
                "default",
            );
        }
    }
}
add_action("add_meta_boxes", "tailbase_add_meta_description_box");

function tailbase_render_meta_description_box($post)
{
    wp_nonce_field(
        "tailbase_meta_description",
        "tailbase_meta_description_nonce",
    );
    $value = get_post_meta($post->ID, TAILBASE_META_DESCRIPTION_KEY, true);
    ?>
    <textarea name="tailbase_meta_description" rows="3" style="width:100%" maxlength="300"><?php echo esc_textarea(
        $value,
    ); ?></textarea>
    <p class="description">Shown by search engines under the page title. Recommended: 120–160 characters. Leave empty to use the excerpt or the start of the content.</p>
    <?php
}

function tailbase_save_meta_description($post_id)
{
    if (
        !isset($_POST["tailbase_meta_description_nonce"]) ||
        !wp_verify_nonce(
            $_POST["tailbase_meta_description_nonce"],
            "tailbase_meta_description",
        ) ||
        (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) ||
        !current_user_can("edit_post", $post_id)
    ) {
        return;
    }
    $value = sanitize_textarea_field(
        wp_unslash($_POST["tailbase_meta_description"] ?? ""),
    );
    if ($value === "") {
        delete_post_meta($post_id, TAILBASE_META_DESCRIPTION_KEY);
    } else {
        update_post_meta($post_id, TAILBASE_META_DESCRIPTION_KEY, $value);
    }
}
add_action("save_post", "tailbase_save_meta_description");
