// Generates src/styles/tokens.css (Tailwind @theme) from theme.json,
// so theme.json stays the single source of truth for colors, fonts and font sizes.
const fs = require("fs");
const path = require("path");

const root = path.resolve(__dirname, "..");
const { settings } = JSON.parse(
  fs.readFileSync(path.join(root, "theme.json"), "utf8"),
);

const lines = [];
const add = (prefix, items, value) =>
  items.forEach((item) =>
    lines.push(`  --${prefix}-${item.slug}: ${item[value]};`),
  );

// Only the font sizes defined in theme.json should exist as text-* utilities.
lines.push("  --text-*: initial;");
add("text", settings.typography.fontSizes, "size");
add("font", settings.typography.fontFamilies, "fontFamily");
add("color", settings.color.palette, "color");

fs.writeFileSync(
  path.join(root, "src/styles/tokens.css"),
  `/* Generated from theme.json by scripts/build-tokens.js. Do not edit. */\n@theme static {\n${lines.join("\n")}\n}\n`,
);
