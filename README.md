# Tailbase

A minimal WordPress theme built on plain PHP templates, Tailwind CSS v4, Alpine.js and Gutenberg.
There is no templating engine and no Composer dependency. Everything is bundled with webpack.

- Requires PHP 8.4+
- Node and npm (the repo enforces npm through `only-allow`)
- Parent theme. A child theme is `Tailbase-Child` (its `Template:` header must stay `Tailbase`)

## Table of contents

- [Quick start](#quick-start)
  - [Packaging](#packaging)
- [Project structure](#project-structure)
  - [Templates](#templates)
  - [`lib/` modules](#lib-modules)
- [Theme options (Customizer)](#theme-options-customizer)
  - [Security section](#security-section)
- [How the build works](#how-the-build-works)
- [Images](#images)
  - [Use an image in a PHP template](#use-an-image-in-a-php-template)
  - [Use an image in CSS](#use-an-image-in-css)
  - [Workflow](#workflow)
- [Styles](#styles)
  - [The three entry files](#the-three-entry-files)
  - [Adding new styles: which layer to use](#adding-new-styles-which-layer-to-use)
  - [Single source of truth: `theme.json`](#single-source-of-truth-themejson)
  - [Layers in the block editor](#layers-in-the-block-editor)
  - [Block editor styles](#block-editor-styles)
- [JavaScript](#javascript)
- [Using this theme with a Gutenberg-blocks plugin](#using-this-theme-with-a-gutenberg-blocks-plugin)
- [Option 1: share the theme's Tailwind](#option-1-share-the-themes-tailwind)
  - [1. Tell Tailwind to scan the plugin](#1-tell-tailwind-to-scan-the-plugin)
  - [2. Write the plugin's blocks with theme classes](#2-write-the-plugins-blocks-with-theme-classes)
  - [3. Rebuild the theme](#3-rebuild-the-theme)
  - [4. Guard against a different theme (recommended)](#4-guard-against-a-different-theme-recommended)
  - [Troubleshooting](#troubleshooting)
- [Option 2: the theme has its own styles, the plugin has its own](#option-2-the-theme-has-its-own-styles-the-plugin-has-its-own)
  - [1. Keep the theme's Tailwind out of the plugin](#1-keep-the-themes-tailwind-out-of-the-plugin)
  - [2. Give the plugin a prefixed Tailwind build](#2-give-the-plugin-a-prefixed-tailwind-build)
  - [3. Load the plugin's stylesheet on the front end and in the editor](#3-load-the-plugins-stylesheet-on-the-front-end-and-in-the-editor)
  - [4. Share design tokens through WordPress presets (optional)](#4-share-design-tokens-through-wordpress-presets-optional)
  - [5. Layers: what wins in the editor](#5-layers-what-wins-in-the-editor)
  - [6. Build](#6-build)
  - [Troubleshooting](#troubleshooting-1)

## Quick start

```bash
npm install
npm run dev        # webpack + BrowserSync (proxy is set in webpack.config.js)
npm run build      # production build (hashed file names, minified)
```

| Script              | What it does                                                                |
| ------------------- | --------------------------------------------------------------------------- |
| `npm run dev`       | One development build plus BrowserSync                                      |
| `npm run watch`     | Rebuilds on change                                                          |
| `npm run build`     | Production build into `build/`                                              |
| `npm run build:dev` | Development-mode build                                                      |
| `npm run tokens`    | Regenerates `src/styles/tokens.css` from `theme.json` (see below)           |
| `npm run zip`       | Production build, then packages the theme into `dist/<theme>-<version>.zip` |
| `npm run release`   | Bumps the version, updates `CHANGELOG.md` and `style.css` (release-it)      |

`dev`, `watch`, `build` and `build:dev` run `npm run tokens` first, so you rarely need to run it yourself.
In `watch` mode tokens are generated only at startup, so **restart the watcher after editing `theme.json`**.

### Packaging

`npm run zip` runs a production build and then `scripts/zip-theme.js`, which writes `dist/<theme>-<version>.zip`
(the version comes from `package.json`). The archive has one top-level folder named after the theme, so it can be
uploaded in Appearance, Themes, Add New.

It contains only what the theme needs at runtime: the root `*.php` files, `style.css`, `theme.json`,
`screenshot.png`, `lib/`, `assets/` and `build/` (without source maps). Sources (`src/`), `node_modules/`, config
files and `auth.json` are left out. The script stops with an error if a required file, such as `build/manifest.json`,
is missing, so it never produces an incomplete theme. To ship another file or folder, add it to `files` or
`directories` at the top of the script.

## Project structure

```
header.php            <head>, <body>, navbar (brand, hamburger, main_menu) and the Alpine navbar script
footer.php            scroll-up button, wp_footer(), closing tags
index.php             the only content template: the_content() inside <main class="container mx-auto">
404.php               redirects to the home page
functions.php         loads the lib/ modules, login error message, local mail catcher (Mailpit) SMTP setup
theme.json            single source of truth for colors, font sizes and font families
lib/                  one PHP module per concern (see below)
scripts/build-tokens.js   generates src/styles/tokens.css from theme.json
scripts/zip-theme.js      packages the theme into dist/<theme>-<version>.zip
src/scripts/          JS entry (Alpine + small components)
src/styles/           CSS entries and partials
src/images/           images for templates and CSS, optimised and hashed by webpack (see Images)
assets/icons/         SVG icons, printed with tailbase_get_icon('name')
assets/fonts/         Inter (latin-ext) web fonts, referenced from src/styles/base/fonts.css, not bundled
build/                webpack output + manifest.json (git-ignored)
```

### Templates

There is deliberately one template. `index.php` is used by WordPress for posts, pages, archives, categories,
authors and search alike, and prints `the_content()` for each post in the loop. Add a template file only when a
view really needs different markup (for example `single.php`, `archive.php`).

### `lib/` modules

`functions.php` loads every module listed in `$tailbase_modules`. Each module defines a `tailbase_*` function and
hooks it itself, so a file is self-contained.

| File                                                                  | Purpose                                                                                       |
| --------------------------------------------------------------------- | --------------------------------------------------------------------------------------------- |
| `asset-manifest.php`                                                  | `tailbase_asset_path()` / `tailbase_asset()` / `tailbase_image()`: resolve hashed build files |
| `assets.php`                                                          | Enqueues styles and scripts, dequeues core block CSS and jQuery on the front end              |
| `editor-styles.php`                                                   | Registers `build/editor-styles.*.css` for the block editor                                    |
| `theme-support.php`                                                   | `add_theme_support()` calls                                                                   |
| `head-cleanup.php`                                                    | Removes unneeded `wp_head` output (emoji, RSD, generator, ...)                                |
| `nav-menus.php`                                                       | Registers the `main_menu` and `footer_menu` locations                                         |
| `icons.php`                                                           | `tailbase_get_icon($name)` returns an SVG from `assets/icons/`                                |
| `image-sizes.php`, `post-types.php`, `taxonomies.php`, `sidebars.php` | Commented examples, ready to fill in                                                          |
| `plugin-compat.php`                                                   | Third-party plugin tweaks (Contact Form 7)                                                    |
| `meta-description.php`                                                | Meta description metabox                                                                      |
| `customizer.php`                                                      | Custom scripts (header/body/footer) and custom body classes                                   |
| `security.php`                                                        | Security section: hardening options and blocking PHP in `uploads/`                            |

To add a module, create `lib/<name>.php`, define a prefixed function, hook it, and add `"<name>"` to the list in
`functions.php`.

## Theme options (Customizer)

Site options live in **Appearance, Customize** and are stored as theme mods. There is no settings plugin.

| Where in the Customizer | Option                                                                            |
| ----------------------- | --------------------------------------------------------------------------------- |
| Custom scripts, Header  | Raw HTML printed in `wp_head`                                                     |
| Custom scripts, Body    | Raw HTML printed in `wp_body_open`                                                |
| Custom scripts, Footer  | Raw HTML printed in `wp_footer`                                                   |
| Theme settings          | Custom body classes (space separated). Add future general options to this section |
| Security                | Checkboxes, see below                                                             |

- **Scripts** are code, so users without the `unfiltered_html` capability get their input run through `wp_kses_post()`.
  Empty fields print nothing; there is no enable checkbox.

### Security section

All options need the `manage_options` capability.

| Option                          | Default | What it does                                                                                                                |
| ------------------------------- | ------- | --------------------------------------------------------------------------------------------------------------------------- |
| Hide WordPress version          | on      | Empties the `the_generator` output (the `wp_head` tag is already removed in `head-cleanup.php`)                             |
| Disable XML-RPC                 | on      | `xmlrpc_enabled` returns false                                                                                              |
| Disable plugin and theme editor | on      | Defines `DISALLOW_FILE_EDIT` (skipped if `wp-config.php` already defines it)                                                |
| Allow SVG uploads               | off     | Only for users with `unfiltered_html`; files with scripts or event handlers are rejected (safety net, not a full sanitizer) |
| Block user enumeration          | on      | Redirects `?author=N` and hides `/wp/v2/users` for logged-out visitors                                                      |
| Send security headers           | on      | `X-Content-Type-Options`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy`                                                  |

Always on (no option): on the first admin page load the theme writes `wp-content/uploads/.htaccess` that denies PHP
execution (Apache). On nginx add `location ~* ^/wp-content/uploads/.*\.php$ { deny all; }` yourself.

`wp-config.php` is not part of the theme. Keep `WP_DEBUG` off in production, for example:

```php
define(
  "WP_DEBUG",
  in_array(WP_ENVIRONMENT_TYPE, ["local", "development"], true),
);
define("WP_DEBUG_LOG", false);
```

## How the build works

`webpack.config.js` has four entries:

| Entry           | Source                  | Loaded on              |
| --------------- | ----------------------- | ---------------------- |
| `app-styles`    | `src/styles/app.css`    | front end (Tailwind)   |
| `theme-styles`  | `src/styles/theme.css`  | front end (theme CSS)  |
| `app-scripts`   | `src/scripts/app.js`    | front end (Alpine, UI) |
| `editor-styles` | `src/styles/editor.css` | block editor           |

Output goes to `build/` with content hashes in production. `build/manifest.json` maps logical names
(`app-styles.css`) to hashed files, and `tailbase_asset()` reads it. If the manifest or an entry is missing, the
theme throws, so run a build after cloning.

PostCSS uses only `@tailwindcss/postcss`. It also handles `@import` and CSS nesting.

## Images

Put images in `src/images/` (subfolders are fine). Webpack copies every image in that folder to `build/images/`,
compresses it (mozjpeg, pngquant, gifsicle, svgo) and adds a content hash to the file name, for example
`src/images/hero.jpg` becomes `build/images/hero.3fa9c1d2.jpg`. Supported formats: jpg, png, gif, svg, webp, avif.
`src/scripts/app.js` ends with a `require.context("../images", ...)` line, which is what makes webpack pick up
images that no CSS or JS refers to. Do not remove it.

### Use an image in a PHP template

`build/manifest.json` maps the original path to the hashed file, and `tailbase_image()` reads it:

```php
<img src="<?php echo esc_url(tailbase_image("hero.jpg")); ?>" alt="">
<img src="<?php echo esc_url(tailbase_image("team/anna.webp")); ?>" alt="Anna">
```

The argument is the path inside `src/images/`, without the hash. The manifest key is `images/<path>`, so you can
also call `tailbase_asset("images/hero.jpg")`.

### Use an image in CSS

Reference it with a relative path, for example `url("../images/hero.jpg")` in `src/styles/theme.css`. Webpack
rewrites the URL to the hashed file.

### Workflow

1. Add or replace the file in `src/images/`.
2. Run `npm run build`, or keep `npm run watch` running.
3. Use `tailbase_image("file.jpg")` in the template.

Things to know:

- **Rebuild after adding an image.** PHP can only find files that are in `build/` and in `manifest.json`. A name
  that is not in the manifest makes `tailbase_asset()` throw an exception, which also catches typos.
- **Hashed names change when the file changes.** Never hard-code a `build/images/...` URL, always use the helper.
- **`build/` is wiped on every build** and is git-ignored. Never put files there by hand, and run the build on
  deploy (or ship the folder; `npm run zip` does a build first).
- **Same file name in different folders is fine.** The folder is part of the path and the manifest key.
- **Static files that need no processing** (icons, favicons, logos that never change) can live in `assets/`
  instead and be loaded with `get_theme_file_uri("assets/...")`. `assets/icons/` is read by `tailbase_get_icon()`.
- **Image minification needs native binaries.** If the build warns `cjpeg ENOENT` (or the same for `pngquant` or
  `gifsicle`), npm skipped the install scripts. Run `npm rebuild mozjpeg gifsicle pngquant-bin` and build again.

## Styles

Tailwind v4 is configured **in CSS, not in a JS config**. There is no `tailwind.config.js`.

### The three entry files

| File                    | Built as              | Loaded on               | Used for                                         |
| ----------------------- | --------------------- | ----------------------- | ------------------------------------------------ |
| `src/styles/app.css`    | `app-styles.*.css`    | Front end               | Tailwind itself: reset, utility classes, tokens  |
| `src/styles/theme.css`  | `theme-styles.*.css`  | Front end (after `app`) | Your own CSS for the site                        |
| `src/styles/editor.css` | `editor-styles.*.css` | Block editor only       | Tailwind plus your styles, as seen in the editor |

- **`app.css`** is Tailwind's entry (`@import "tailwindcss"`, the generated `tokens.css`, the `container` utility).
  You rarely edit it. Touch it only to add an `@source`, an `@utility`, or a manual `@theme` override for something
  `theme.json` does not drive (spacing, breakpoints, ...).
- **`theme.css`** is where your own front-end CSS goes: global element styles, fonts, layout and components.
- **`editor.css`** imports `app.css`, so all Tailwind utilities work in the editor, and it imports the same base
  styles as the front end. Edit it only for editor-specific rules, or to make a new component look right in the
  editor.

`src/styles/tokens.css` is **generated** from `theme.json`. Never edit it (it is git-ignored).

Two rules for any CSS file:

- Imports in v4 need a relative path: `@import "./file.css"`.
- A file that is its own entry and uses `@apply` needs `@reference "./app.css";` at the top (see `theme.css`).

### Adding new styles: which layer to use

Prefer Tailwind utility classes in the markup. Write CSS only when utilities do not fit (a repeated component,
styles that depend on a parent class, third-party markup).

Tailwind v4 orders its CSS in cascade layers: `theme` < `base` < `components` < `utilities`. A later layer wins, and
**CSS outside any layer beats all layers**. So the layer you pick decides whether a utility class can override
your rule:

| What you are adding                         | Layer               | Utility classes can override it? |
| ------------------------------------------- | ------------------- | -------------------------------- |
| Reusable component (`.card`, `.btn`, ...)   | `@layer components` | Yes (recommended default)        |
| Global element styles (`h1`, `p`, `a`, ...) | `@layer base`       | Yes                              |
| A rule that must always win (rare)          | No layer            | No                               |

Add a component to `src/styles/theme.css`:

```css
@layer components {
  .card {
    @apply rounded-lg bg-white-500 p-6;
  }
}
```

In markup, `class="card p-10"` then works as expected: `p-10` overrides the padding from `.card`.

To keep `theme.css` short you can put the rules in their own file and import it **into a layer**:

```css
@import "./components/card.css" layer(components);
```

(An `@import` without `layer(...)` puts the file's rules outside any layer, so they would beat utility classes.)

For the block editor, add the same rule to `src/styles/editor.css` (or import the same file there) if the component
must look right in the editor. Editor-only rules also belong in `editor.css`.

`@apply` works because `theme.css` starts with `@reference "./app.css"`. Do not remove that line.

Colors, font sizes and fonts are not defined in these files. Change them in `theme.json` (next section).

### Single source of truth: `theme.json`

Colors, font sizes and font families live **only** in `theme.json`. `scripts/build-tokens.js` turns them into a
Tailwind `@theme static` block:

```
theme.json  ->  npm run tokens  ->  src/styles/tokens.css  ->  Tailwind utilities
        \->  Gutenberg editor palette / font size picker
```

`@theme static` makes sure every token is emitted as a CSS variable, even if no template uses it yet.

#### Define a color

Add an entry to `settings.color.palette` in `theme.json`:

```json
{ "slug": "brand-500", "color": "#0057ff", "name": "Brand 500" }
```

Rebuild (or restart `watch`). You now have:

- Tailwind classes: `bg-brand-500`, `text-brand-500`, `border-brand-500`, `ring-brand-500`, with opacity such as `bg-brand-500/50`
- CSS variable: `var(--color-brand-500)`
- The same color in the editor's color picker

The current palette uses `orange`, `white`, `black` and `purple`, each with steps `100` to `900`. Tailwind's default
colors still exist as utilities. The editor only offers the theme palette (`defaultPalette: false`, `custom: false`).

#### Define a font size

Add an entry to `settings.typography.fontSizes`:

```json
{ "name": "5xl", "slug": "5xl", "size": "4.5rem" }
```

You get `text-5xl`, `var(--text-5xl)` and a size in the editor's picker. Only the sizes in `theme.json` exist as
`text-*` utilities, because the generator clears Tailwind's default scale (`--text-*: initial`).

Current static scale: `xs .625`, `sm .8125`, `base 1`, `md 1.25`, `lg 1.562`, `xl 1.938`, `2xl 2.438`, `3xl 3.062`,
`4xl 3.812` (rem).

#### Fluid font sizes (`clamp()`)

Any `size` can be a `clamp()` expression. The theme ships `fluid-lg` to `fluid-4xl`, each ramping from the previous
step on a 320px viewport to the named step on a 1280px viewport, for example:

```json
{
  "name": "fluid-4xl",
  "slug": "fluid-4xl",
  "size": "clamp(3.062rem, 2.812rem + 1.25vw, 3.812rem)"
}
```

Use it as `text-fluid-4xl` or `var(--text-fluid-4xl)`. To compute a new one:
`slope = (max - min) / (maxViewport - minViewport)`, then `preferred = (min - slope * minViewport) + slope * 100vw`.

Headings use these tokens in `typography.css`: `h1` `fluid-4xl`, `h2` `fluid-3xl`, `h3` `fluid-2xl`, `h4` `fluid-xl`,
`h5` `fluid-lg`, `h6` `md`.

#### Fonts

`settings.typography.fontFamilies` defines `--font-sans` (Inter plus a system stack), used as `font-sans`. To add a
font family, add an entry with a `slug` (becomes `font-<slug>`) and declare its `@font-face` rules in `theme.css` (and in `editor.css` if the editor needs the font). The existing Inter faces are already loaded in both.

#### Things `theme.json` does not drive

Spacing, radii, breakpoints and shadows use Tailwind's defaults. To override one, add an `@theme` block to
`app.css` by hand (not in the generated `tokens.css`).

### Layers in the block editor

The rules above are for the front end. The block editor behaves differently: WordPress styles the editor wrapper
and resets headings with CSS that is **outside any layer**, so it beats layered theme CSS. For that reason
`editor.css` includes the global element styles (headings, body text) **without a layer**, and they win in the
editor. The trade-off: in the editor, a utility class on a heading loses to the global heading size, while on the
front end it wins. Component rules in `@layer components` are not affected, because WordPress does not style your
class names.

### Block editor styles

`lib/editor-styles.php` calls `add_editor_style()` with a **theme-relative path**
(`build/editor-styles.<hash>.css`). Do not pass a URL: WordPress would fetch it remotely and could not resolve
relative `url()` references such as the fonts. The editor stylesheet contains the full Tailwind utilities, so utility
classes work inside the editor canvas too.

## JavaScript

`src/scripts/app.js` starts Alpine.js (`window.Alpine`) and runs two small components (`anchorScroll`, `scrollToUp`).
The navbar's Alpine data (`HeaderData`) is defined inline in `header.php`.

## Using this theme with a Gutenberg-blocks plugin

A blocks plugin can get its styles in two ways. Pick one per project.

|                                              | Option 1: share the theme's Tailwind | Option 2: the plugin has its own styles |
| -------------------------------------------- | ------------------------------------ | --------------------------------------- |
| Tailwind builds                              | One, in the theme                    | Two: the theme's and the plugin's       |
| Who ships the CSS                            | The theme (it scans the plugin)      | The plugin (prefixed, no reset)         |
| Works with another theme                     | No                                   | Yes                                     |
| Rebuild the theme when plugin markup changes | Yes                                  | No                                      |
| Class name conflicts                         | None                                 | Avoided with a class prefix             |
| Best for                                     | A private plugin on one site         | A plugin you distribute or reuse        |

The `tailbase-blocks` plugin is an example of Option 2.

## Option 1: share the theme's Tailwind

Goal: a plugin ships blocks that use the theme's Tailwind utilities and tokens, and ships **no Tailwind CSS of
its own**. There are no duplicated resets and no class-name conflicts, because there is only one Tailwind build.

Trade-offs:

- The plugin **depends on this theme**. With another theme its blocks will be unstyled.
- Whenever the plugin's markup changes in a way that adds or removes classes, **the theme must be rebuilt**.
- Best for a private plugin used on a single site or in a single project. For a plugin you distribute, use a
  prefixed, self-contained build instead.

### 1. Tell Tailwind to scan the plugin

Tailwind finds class names by scanning source files. Files outside the theme are not scanned automatically, so add
an `@source` line to `src/styles/app.css`. The path is relative to that file
(`wp-content/themes/Tailbase/src/styles/`), so the plugins folder is `../../../../plugins`:

```css
@import "tailwindcss";
@import "./tokens.css";

@source "../../../../plugins/my-blocks";

@utility container {
  margin-inline: auto;
}
```

Scan only what contains classes, to keep builds fast, for example:

```css
@source "../../../../plugins/my-blocks/src";
@source "../../../../plugins/my-blocks/blocks/**/*.php";
```

Both `app-styles` (front end) and `editor-styles` (editor) import `app.css`, so the plugin's classes are generated
for both places from this one line.

### 2. Write the plugin's blocks with theme classes

Use the theme's tokens, for example `bg-purple-500`, `text-fluid-3xl`, `container`, `lg:grid-cols-3`, in:

- `render.php` / PHP templates for dynamic blocks
- JS or JSX for the block's `edit` and `save` components
- `block.json` needs no `style` or `editorStyle` for styling. Leave them out unless the plugin has extra CSS.

```php
// blocks/hero/render.php
<section <?php echo get_block_wrapper_attributes([
  "class" => "bg-purple-500 py-20 text-white-500",
]); ?>>
  <h2 class="text-fluid-3xl"><?php echo esc_html($attributes["title"]); ?></h2>
</section>
```

Rules so classes are detected:

- Write **complete class names** in the source. `"bg-" + color + "-500"` is not detected. Use a lookup object
  with full strings such as `{ primary: "bg-purple-500", accent: "bg-orange-500" }`.
- If a class comes only from the database or a block attribute and never appears in source, force it with
  `@source inline("bg-purple-500 bg-orange-500")` in `app.css`.

### 3. Rebuild the theme

```bash
npm run build      # or restart npm run watch
```

The plugin's classes are now part of `build/app-styles.*.css` and `build/editor-styles.*.css`. Commit or deploy the
rebuilt `build/` (it is git-ignored, so production must run the build).

### 4. Guard against a different theme (recommended)

Make the plugin fail soft when this theme is not active, so editors see a notice instead of a broken layout:

```php
add_action("admin_notices", function () {
  if (get_template() !== "tailbase") {
    echo '<div class="notice notice-warning"><p>My Blocks needs the Tailbase theme for its styles.</p></div>';
  }
});
```

`get_template()` returns the parent theme's folder name, so it also works with the `Tailbase-Child` child theme.

### Troubleshooting

| Symptom                                   | Likely cause                                                                                  |
| ----------------------------------------- | --------------------------------------------------------------------------------------------- |
| A plugin class has no effect              | Class not in a scanned path, built dynamically, or the theme was not rebuilt                  |
| Works on the front end, not in the editor | Editor CSS is stale: rebuild and hard-refresh; check `editor-styles` in `build/manifest.json` |
| Build is slow or scans too much           | `@source` points at a whole plugins folder; narrow the path                                   |
| Colors differ from the design             | Token missing in `theme.json`; add it and run `npm run tokens`                                |

## Option 2: the theme has its own styles, the plugin has its own

Goal: the theme and the plugin each ship their own Tailwind build. The plugin does not depend on this theme, and
changing the plugin never requires rebuilding the theme. The `tailbase-blocks` plugin is built this way, so it is the
reference implementation.

Trade-offs:

- Two stylesheets are loaded, so there is some duplicated CSS (a utility such as `flex` exists in both builds).
- Nothing is shared automatically. Design tokens (colors, sizes) have to be connected on purpose, see step 4.
- The plugin has to protect itself from the theme's CSS, and the theme from the plugin's. The steps below do that.

### 1. Keep the theme's Tailwind out of the plugin

Do **not** add an `@source` line for the plugin to `src/styles/app.css`. The theme only scans its own files, so
nothing from the plugin ends up in `build/app-styles.*.css`.

### 2. Give the plugin a prefixed Tailwind build

In the plugin, `src/styles/tailwind.css` sets up Tailwind with a prefix, without the reset (preflight) and without cascade layers. `source(none)` stops Tailwind from scanning the whole plugin on its own; the stylesheet entry adds `@source` explicitly:

```css
@import "tailwindcss/theme.css" prefix(tbb);
@import "tailwindcss/utilities.css" prefix(tbb) source(none);
@import "./tokens.css";
```

- **A prefix** (`tbb:`) puts every class in its own namespace: write `tbb:flex tbb:p-8` in the block markup. The theme's
  `flex` and the plugin's `tbb:flex` never collide, and theme variables become `--tbb-*`.
- **No preflight**: the plugin must not restyle the theme's elements. Set margins, sizes and colors on your own
  markup explicitly (for example `tbb:m-0` on a heading).
- **One utility stylesheet** (`global-styles.css`) scans the whole plugin `src/` folder and is the only file loaded
  for all blocks. With a copy per block, the cascade would depend on load order.

Write class names as **complete strings** (`'tbb:bg-primary'`), never built from parts, so Tailwind can find them.

### 3. Load the plugin's stylesheet on the front end and in the editor

The plugin enqueues its own file with `enqueue_block_assets`, which runs in both places:

```php
add_action("enqueue_block_assets", function () {
  wp_enqueue_style(
    "my-blocks-global-styles",
    plugins_url("build/global-styles.css", __FILE__),
    [],
    filemtime(__DIR__ . "/build/global-styles.css"),
  );
});
```

This is why the theme does not need to know about the plugin: `editor-styles.*.css` and `app-styles.*.css` stay as
they are.

### 4. Share design tokens through WordPress presets (optional)

The theme's colors and font sizes live in `theme.json`, and WordPress turns them into CSS variables such as
`--wp--preset--color--purple-500`. The plugin can read those variables and fall back to fixed values when the theme
does not define them:

```css
@theme {
  --color-primary: var(--wp--preset--color--purple-500, #383e61);
}
```

A block using `tbb:bg-primary` then follows the Tailbase palette, and still looks right on another theme. To change
a color for the plugin, edit its `tokens.css`. To change it for the theme, edit `theme.json`.

### 5. Layers: what wins in the editor

The theme's `editor.css` styles headings **outside any layer** (see "Layers in the block editor"), and unlayered CSS
beats layered CSS. The plugin's utilities should therefore be **unlayered** in its built stylesheet, or a theme rule
such as `h2 { font-size: ... }` would override `tbb:text-2xl` in the editor. Tailwind v4 emits `space-*` and `divide-*`
with `:where()` (zero specificity), so the plugin redefines them with normal specificity.

### 6. Build

```bash
# in the theme
npm run build
# in the plugin
npm run build
```

The two builds are independent. Deploy both `build/` folders (both are git-ignored).

### Troubleshooting

| Symptom                                                  | Likely cause                                                                                                      |
| -------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------- |
| A block is unstyled                                      | `build/global-styles.css` is missing: run `npm run build` in the plugin                                           |
| A plugin class has no effect                             | The class is missing the `tbb:` prefix, or is built dynamically instead of written as a full string               |
| The theme overrides a block's heading or paragraph       | The plugin's utility is in a cascade layer; keep it unlayered, or set the property explicitly on the block markup |
| Block colors do not match the theme                      | The token points at a preset that does not exist in `theme.json`: add the color there, or fix the token           |
| Block looks different in the editor and on the front end | An editor rule from the theme or WordPress is stronger; check specificity and layers (step 5)                     |
