import themeConfig from './tailwind.theme.config'

module.exports = {
    content: [
        "./lib/**/*.php",
        "./src/**/*.js",
        "./*.php"
    ],
    theme: {
        extend: {
            colors: themeConfig.colors,
            fontSize: themeConfig.fontSize,
            fontFamily: themeConfig.fontFamily,
            container: themeConfig.container,
            gridTemplateRows: themeConfig.gridTemplateRows,
            gridRowEnd: themeConfig.gridRowEnd
        },
    },
    plugins: [
        // require('@tailwindcss/forms')
    ],
    safelist: [
        'navbar.active',
    ]
}
