<?php
/**
 * PHPUnit bootstrap for WP AI Study Assistant.
 * Constants are defined in tests/prepend.php via auto_prepend_file.
 */

$_tests_dir = defined( 'WP_PHPUNIT__DIR' ) ? constant( 'WP_PHPUNIT__DIR' ) : 'vendor/wp-phpunit/wp-phpunit';

require_once $_tests_dir . '/includes/functions.php';

// Load the plugin under test.
tests_add_filter(
	'muplugins_loaded',
	function () {
		require dirname( __DIR__ ) . '/wp-ai-study-assistant.php';
	}
);

require $_tests_dir . '/includes/bootstrap.php';
