/**
 * ESLint configuration for WP AI Study Assistant.
 *
 * Responsibilities:
 * - Apply the official WordPress ESLint ruleset (@wordpress/eslint-plugin).
 * - Enforce coding standards consistent with WordPress JavaScript guidelines.
 * - Enable linting for browser-based, ES2022 code.
 * - Parse ECMAScript modules (ESM) instead of CommonJS.
 *
 * Usage:
 * - Run `npm run lint:js` to check JS files in assets/src.
 * - Run `npm run lint:js:fix` to auto-fix common issues.
 *
 * @package   WP_AI_Study_Assistant
 * @since     1.0.0
 */

module.exports = {
  root: true,
  extends: [ 'plugin:@wordpress/eslint-plugin/recommended' ],
  env: { browser: true, es2022: true },
  parserOptions: { sourceType: 'module' },
};
