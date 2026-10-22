import js from "@eslint/js"; // ES6+ JavaScript
export default [
  js.configs.recommended,
  {
    rules: {
      "no-undef": "off",
      "no-unused-vars": "warn",
    },
  },
  {
    languageOptions: {
      ecmaVersion: "latest",
      sourceType: "module",
    },
  },
  {
    ignores: [
      "auth.json",
      "webpack.config.js",
      "eslint.config.mjs",
      "node_modules",
      "build",
      "dist",
    ],
  },
];
