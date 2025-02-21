import importPlugin from "eslint-plugin-import"; // Import rules
import js from "@eslint/js"; // ES6+ JavaScript
import babelParser from "@babel/eslint-parser";
export default [
    js.configs.recommended,
    importPlugin.flatConfigs.recommended,
    {
        rules: {
            "no-undef": "off",
            "no-unused-vars": "warn"
        }
    },
    {
        languageOptions: {
            parser: babelParser,
            parserOptions: {
                requireConfigFile: false,
                babelOptions: {
                    babelrc: false,
                    configFile: false,
                    presets: ["@babel/preset-env"]
                }
            }
        }
    },
    {
        ignores: [
            "acf-json",
            "auth.json",
            "webpack.config.js",
            "tailwind.theme.config.js",
            "tailwind.config.js",
            "eslint.config.mjs",
            "node_modules",
            "build",
            "dist"
        ]
    }
];
