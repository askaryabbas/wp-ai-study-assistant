/**
 * PostCSS configuration for WP AI Study Assistant.
 *
 * Responsibilities:
 * - Apply vendor prefixes automatically to compiled CSS using Autoprefixer.
 * - Ensures compatibility with supported browsers as defined in
 *   `.browserslistrc` or the `browserslist` field in package.json.
 *
 * This config is consumed by webpack via `postcss-loader`.
 *
 * @package   WP_AI_Study_Assistant
 * @since     1.0.0
 */

import autoprefixer from 'autoprefixer';

export default {
	plugins: [
		autoprefixer()
		]
	};
