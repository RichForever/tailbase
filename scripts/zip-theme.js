const fs = require("fs");
const archiver = require("archiver");
const path = require("path");

// Define the theme name
const themeName = path.basename(__dirname);

// Ensure the build directory exists
const buildDir = path.join(__dirname, "build");
if (!fs.existsSync(buildDir)) {
  fs.mkdirSync(buildDir);
}

// Specify the output file name and path
const output = fs.createWriteStream(
  path.join(buildDir, `${themeName}-theme.zip`),
);
const archive = archiver("zip", {
  zlib: { level: 9 }, // Compression level
});

// Handle output events
output.on("close", function () {
  console.log(archive.pointer() + " total bytes");
  console.log(
    "archiver has been finalized and the output file descriptor has closed.",
  );
});

output.on("end", function () {
  console.log("Data has been drained");
});

archive.on("warning", function (err) {
  if (err.code !== "ENOENT") {
    throw err;
  }
  console.warn(err);
});

archive.on("error", function (err) {
  throw err;
});

// Pipe the archive data to the file
archive.pipe(output);

// List of directories to include in the zip
const directories = ["assets", "lib", "dist"];

// Append directories with theme name prefix
directories.forEach((dir) => {
  archive.directory(dir, `${themeName}/${dir}`);
});

// List of files to include in the zip
const files = [
  "auth.json",
  "screenshot.png",
  "theme.json",
  "style.css", // Added this line
  // Add other standard WordPress files here
];

// Append files with theme name prefix
files.forEach((file) => {
  archive.file(file, { name: `${themeName}/${file}` });
});

// Append all PHP files in the root directory with theme name prefix
fs.readdirSync(__dirname).forEach((file) => {
  if (file.endsWith(".php") && !files.includes(file)) {
    archive.file(file, { name: `${themeName}/${file}` });
  }
});

// Finalize the archive (i.e. we are done appending files but streams have to finish yet)
archive.finalize();
