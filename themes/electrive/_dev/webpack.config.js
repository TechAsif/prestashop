/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
const webpack = require('webpack');
const path = require('path');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const TerserPlugin = require("terser-webpack-plugin");

let config = {
  entry: {
    main: [
      './js/theme.js',
      './css/theme.scss'
    ]
  },
  output: {
    path: path.resolve(__dirname, '../assets/js'),
    filename: 'theme.js'
  },
  devtool: 'source-map',
  module: {
    rules: [
      {
        test: /\.js/,
        loader: 'babel-loader'
      },
  
      // compile all .scss files to plain old css
      {
        // test: /\.(c|sc|sa)ss$/,
        test: /\.scss$/,
        use: [
          MiniCssExtractPlugin.loader, 
          'css-loader', 
          'postcss-loader',
          'sass-loader'
        ]
      },
      // {
      //   test: /\.scss$/,
      //   use: ExtractTextPlugin.extract({
      //     fallback: 'style-loader',
      //     use: [
      //       {
      //         loader: 'css-loader',
      //         options: {
      //           minimize: true
      //         }
      //       },
      //       'postcss-loader',
      //       'sass-loader'
      //     ]
      //   })
      // },
      {
        test: /.(png|woff(2)?|eot|ttf|svg)(\?[a-z0-9=\.]+)?$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: '../css/[name].[ext]'
            }
          }
        ]
      },
      {
        test : /\.css$/,
        use: ['style-loader', 'css-loader', 'postcss-loader']
      }
    ]
  },
  externals: {
    prestashop: 'prestashop',
    $: '$',
    jquery: 'jQuery'
  },
  plugins: [
    new webpack.ProvidePlugin({
      $: "jquery",
      jQuery: "jquery",
      'window.jQuery': 'jquery',
      'window.Tether': 'tether',
      tether: 'tether',
      Tether: 'tether'
    }),
    new MiniCssExtractPlugin({
      filename: path.join('..', 'css', 'theme.css'),
      // filename: '../css/[name].css',
      // chunkFilename: "./css/build/[id].css"
    }),
    // new ExtractTextPlugin(path.join('..', 'css', 'theme.css'))
  ],
  optimization: {
    minimize: true,
    minimizer: [
      new TerserPlugin({
        test: /\.js(\?.*)?$/i,
        extractComments: false,
        terserOptions: {
          format: {
            comments: false,
          },
        },
      }),
    ],
  },
};


module.exports = config;
