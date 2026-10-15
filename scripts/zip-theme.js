// Packages the theme into dist/<theme>-<version>.zip, ready to upload to WordPress.
// Run `npm run zip` (it builds first). Only the files listed below are included.
const fs = require("fs");
const path = require("path");
const archiver = require("archiver");

const root = path.resolve(__dirname, "..");
const themeName = path.basename(root);
const { version } = require(path.join(root, "package.json"));

const outDir = path.join(root, "dist");
const outFile = path.join(outDir, `${themeName}-${version}.zip`);

// Directories to include (source maps are left out).
const directories = ["assets", "lib", "build"];

// Files to include, in addition to every .php file in the theme root.
const files = ["style.css", "theme.json", "screenshot.png"];

const rootPhpFiles = fs
  .readdirSync(root)
  .filter((file) => file.endsWith(".php"));

// Fail early instead of shipping an incomplete theme.
const required = [
  ...directories,
  ...files,
  "build/manifest.json",
  "functions.php",
];
const missing = required.filter(
  (item) => !fs.existsSync(path.join(root, item)),
);
if (missing.length) {
  console.error(
    `Cannot create the zip, missing: ${missing.join(", ")}\n` +
      "Run `npm run zip` (it runs the build first).",
  );
  process.exit(1);
}

fs.mkdirSync(outDir, { recursive: true });
const output = fs.createWriteStream(outFile);
const archive = archiver("zip", { zlib: { level: 9 } });

output.on("close", () => {
  console.log(
    `${path.relative(root, outFile)} (${(archive.pointer() / 1024).toFixed(1)} KiB)`,
  );
});

archive.on("warning", (err) => {
  throw err;
});
archive.on("error", (err) => {
  throw err;
});

archive.pipe(output);

// Everything is placed inside a top-level folder named after the theme.
directories.forEach((dir) => {
  archive.glob(
    "**/*",
    { cwd: path.join(root, dir), ignore: ["**/*.map"] },
    { prefix: `${themeName}/${dir}` },
  );
});

[...files, ...rootPhpFiles].forEach((file) => {
  archive.file(path.join(root, file), { name: `${themeName}/${file}` });
});

archive.finalize();
