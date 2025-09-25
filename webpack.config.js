/**
 * Webpack configuration for WP AI Study Assistant.
 *
 * Responsibilities:
 * - Bundle and transpile plugin assets for both the block editor (editor.js/css)
 *   and the frontend shortcode UI (public.js/css).
 * - Compile SCSS → CSS with PostCSS + Autoprefixer.
 * - Extract CSS into separate files via MiniCssExtractPlugin.
 * - Optimize/minify CSS and JS in production mode.
 * - Generate source maps in development mode.
 *
 * Entry points:
 * - assets/src/editor/index.js   → assets/dist/editor.js + editor.css
 * - assets/src/public/index.js   → assets/dist/public.js + public.css
 *
 * Usage:
 * - Development build:  NODE_ENV=development npm run build
 * - Production build:   NODE_ENV=production npm run build
 *
 * @package   WP_AI_Study_Assistant
 * @since     1.0.0
 */

import path from 'path';
import { fileURLToPath } from 'url';
import MiniCssExtractPlugin from 'mini-css-extract-plugin';
import CssMinimizerPlugin from 'css-minimizer-webpack-plugin';
import TerserPlugin from 'terser-webpack-plugin';

const __filename = fileURLToPath( import.meta.url );
const __dirname  = path.dirname( __filename );

const isProd = process.env.NODE_ENV === 'production';

export default {
	mode: isProd ? 'production' : 'development',
		entry: {
			editor: path.resolve( __dirname, 'assets/src/editor/index.js' ),
			public: path.resolve( __dirname, 'assets/src/public/index.js' )
	},
	output: {
		path: path.resolve( __dirname, 'assets/dist' ),
		filename: '[name].js',
		clean: true
	},
	devtool: isProd ? false : 'source-map',
	module: {
		rules: [
			{
				test: /\.s?css$/i,
				use: [
					MiniCssExtractPlugin.loader,
					{ loader: 'css-loader', options: { sourceMap: ! isProd } },
					{ loader: 'postcss-loader', options: { sourceMap: ! isProd } },
					{
						loader: 'sass-loader',
						options: {
							sourceMap: ! isProd,
							sassOptions: {
								includePaths: [ path.resolve( __dirname, 'assets/src' ) ]
							}
						}
				}
				]
		}
		]
	},
	plugins: [
		new MiniCssExtractPlugin(
			{
				filename: '[name].css'
			}
		)
	],
optimization: {
	minimize: isProd,
	minimizer: [
		new TerserPlugin( { extractComments: false } ),
		new CssMinimizerPlugin()
	]
	},
	stats: 'minimal'
	};
