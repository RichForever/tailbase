const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const ImageMinimizerPlugin = require("image-minimizer-webpack-plugin");
const { WebpackAssetsManifest } = require("webpack-assets-manifest");
const { EsbuildPlugin } = require('esbuild-loader')
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");



const config = {
  src: {
    main: './src/',
    scripts: './src/scripts',
    styles: './src/styles',
    images: './src/images',
    fonts: './src/fonts',
  },
  build: {
    main: './build',
    images: '',
    fonts: '',
  }
}

// webpack-cli 7 no longer has --node-env, so the mode comes from `--mode` (see package.json scripts).
module.exports = (env, argv) => {
  const isProduction = argv.mode === "production";

  return {
    mode: isProduction ? "production" : "development",
    entry: {
  	"app-styles": config.src.styles + "/app.css",
      "app-scripts": config.src.scripts + "/app.js",
      "theme-styles": config.src.styles + "/theme.css",
      "editor-styles": config.src.styles + "/editor.css",
    },
    output: {
      path: path.resolve(__dirname, config.build.main),
      filename: isProduction ? "[name].[chunkhash:8].js" : "[name].js",
      chunkFilename: isProduction ? "[name].[chunkhash:8].js" : "[name].js",
      clean: true,
    },
    devtool: isProduction ? 'source-map' : 'eval-cheap-module-source-map',
    plugins: [
      new RemoveEmptyScriptsPlugin(),
      new WebpackAssetsManifest({
        output: path.resolve(__dirname, config.build.main + "/manifest.json"),
        customize(entry, original, manifest, asset) {
          const isBundle = /\.(js|css)$/i.test(entry.key);
          const isImage = /^images\/.+\.(jpe?g|png|gif|svg|webp|avif)$/i.test(entry.key);
          if (!isBundle && !isImage) {
            return false;
          }
        },
      }),
      new MiniCssExtractPlugin({
        filename: isProduction ? "[name].[chunkhash:8].css" : "[name].css",
        chunkFilename: isProduction ? "[name].[chunkhash:8].css" : "[name].css",
      }),
    ],
    module: {
      rules: [
        {
          test: /\.(js|jsx)$/i,
          exclude: /node_modules/,
          use: {
            loader: 'esbuild-loader',
            options: {
              loader: 'js',
              target: 'es2015'
            }
          }
        },
        {
          test: /\.css$/i,
          use: [MiniCssExtractPlugin.loader, "css-loader", "postcss-loader"],
        },
        {
          test: /\.(jpe?g|png|gif|svg|webp|avif)$/i,
          type: "asset/resource",
          generator: {
            // Keep subfolders (src/images/a/x.jpg -> images/a/x.<hash>.jpg) so same-named files don't collide.
            filename: (pathData) =>
              path
                .relative(config.src.images, pathData.filename)
                .replace(/\.([^.]+)$/, ".[contenthash:8].$1")
                .replace(/^/, "images/"),
          },
        },
        {
          test: /\.(woff|woff2|eot|ttf|otf)$/i,
          type: "asset/resource",
          generator: {
            filename: "fonts/[name][ext]",
          },
        },
        // Add your rules for custom modules here
        // Learn more about loaders from https://webpack.js.org/loaders/
      ],
    },
    resolve: {
      alias: {
        images: path.join(__dirname, config.src.images),
        fonts: path.join(__dirname, config.src.fonts),
      },
    },
    optimization: {
      splitChunks: {
        cacheGroups: {
          vendor: {
            test: /[\\/]node_modules[\\/](?!alpinejs)/,
            name: "vendors",
            chunks: "all",
          },
        },
      },
      minimizer: [
        "...",
        new CssMinimizerPlugin(),
        new ImageMinimizerPlugin({
          severityError: "warning",
          minimizer: {
            implementation: ImageMinimizerPlugin.imageminMinify,
            options: {
              plugins: [
                "imagemin-gifsicle",
                "imagemin-mozjpeg",
                "imagemin-pngquant",
                "imagemin-svgo",
              ],
            },
          },
        }),
        new EsbuildPlugin(),
      ],
    },
  };
};
