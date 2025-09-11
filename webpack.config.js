// Generated using webpack-cli https://github.com/webpack/webpack-cli

const path = require("path");
const glob = require("glob");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const ImageMinimizerPlugin = require("image-minimizer-webpack-plugin");
const { WebpackAssetsManifest } = require("webpack-assets-manifest");
const { EsbuildPlugin } = require('esbuild-loader')
const ProgressPlugin = require('progress-webpack-plugin');
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");


const isProduction = process.env.NODE_ENV == "production";

const config = {
  blocks: "./blocks",
  src: {
    main: './src/',
    scripts: './src/js',
    styles: './src/scss',
    images: './src/images',
    fonts: './src/fonts',
  },
  dist: {
    main: './dist',
    images: '',
    fonts: '',
  }
}

module.exports = {
  mode: isProduction ? "production" : "development",
  entry: {
    "app-scripts": config.src.scripts + "/app.js",
    "blocks": config.src.scripts + "/blocks.js",
    "twig": config.src.scripts + "/twig.js",
    "editor-scripts": config.src.scripts + "/editor-scripts.js",
    "app-styles": config.src.styles + "/app.scss",
    "theme": config.src.styles + "/theme.scss",
    "editor-styles": config.src.styles + "/editor-styles.scss",
  },
  output: {
    path: path.resolve(__dirname, config.dist.main),
    filename: isProduction ? "[name].[chunkhash:8].js" : "[name].js",
    chunkFilename: isProduction ? "[name].[chunkhash:8].js" : "[name].js",
    clean: true,
  },
  devtool: isProduction ? 'source-map' : 'eval-cheap-module-source-map',
  plugins: [
    new ProgressPlugin(),
    new RemoveEmptyScriptsPlugin(),
    new WebpackAssetsManifest({
      output: path.resolve(__dirname, config.dist.main + "/manifest.json"),
      customize(entry, original, manifest, asset) {
        const pattern = /\.(js|css)$/i;
        if (!pattern.test(entry.key)) {
          return false;
        }
      },
    }),
    new MiniCssExtractPlugin({
      filename: isProduction ? "[name].[chunkhash:8].css" : "[name].css",
      chunkFilename: isProduction ? "[name].[chunkhash:8].css" : "[name].css",
    }),
    new BrowserSyncPlugin({
      host: 'localhost',
      port: 3000,
      proxy: 'http://dev.site',
      files: [
          "./**/*.twig",
          "./**/*.php",
          "./**/*.scss",
          "./**/*.css",
          "./**/*.js"
      ]
    })
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
        test: /\.s[ac]ss$/i,
        use: [
          MiniCssExtractPlugin.loader,
          "css-loader",
          "postcss-loader",
          {
            loader: "sass-loader",
            options: {
              sourceMap: true
            }
          }
        ],
      },
      {
        test: /\.css$/i,
        use: [MiniCssExtractPlugin.loader, "css-loader", "postcss-loader"],
      },
      {
        test: /\.(jpe?g|png|gif|svg)$/i,
        type: "asset/resource",
        generator: {
          filename: "images/[name][ext]",
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
      blocks: path.join(__dirname, config.blocks),
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
