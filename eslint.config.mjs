import packageJson from "eslint-plugin-package-json/configs/recommended";
import importPlugin from "eslint-plugin-import"; // Import rules
import js from "@eslint/js"; // ES6+ JavaScript
export default [
    js.configs.recommended,
    importPlugin.flatConfigs.recommended,
    packageJson,
    {
        rules: {
            "no-undef": "off",
            "no-unused-vars": "warn"
        }
    },
    {
        ignores: [
            "acf-json",
            "auth.json",
            "webpack.config.js",
            "tailwind.theme.config.js",
            "tailwind.config.js",
            "node_modules",
            "build",
            "dist"
        ]
    }
];
