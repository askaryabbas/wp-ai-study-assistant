<?php
/**
 * WordPress PHPUnit test configuration loader.
 * Loaded by wp-phpunit/includes/install.php because WP_TESTS_CONFIG is set.
 */

declare(strict_types=1);

// DB (supports Local.app socket via "localhost:/absolute/path/to/mysql.sock")
define( 'DB_NAME', getenv( 'WP_DB_NAME' ) ?: 'wp_tests' );
define( 'DB_USER', getenv( 'WP_DB_USER' ) ?: 'root' );
define( 'DB_PASSWORD', getenv( 'WP_DB_PASSWORD' ) ?: '' );
define( 'DB_HOST', getenv( 'WP_DB_HOST' ) ?: '127.0.0.1' );
define( 'DB_CHARSET', getenv( 'WP_DB_CHARSET' ) ?: 'utf8' );
define( 'DB_COLLATE', getenv( 'WP_DB_COLLATE' ) ?: '' );

// Table prefix must be a variable for WP.
$table_prefix = getenv( 'WP_TABLE_PREFIX' ) ?: 'wptests_';

// Required by core test suite:
define( 'WP_TESTS_DOMAIN', getenv( 'WP_TESTS_DOMAIN' ) ?: '10up.local' );
define( 'WP_TESTS_EMAIL', getenv( 'WP_TESTS_EMAIL' ) ?: 'wordpress@10up.local' );
define( 'WP_TESTS_TITLE', getenv( 'WP_TESTS_TITLE' ) ?: 'WordPress Test Site' );
define( 'WP_PHP_BINARY', getenv( 'WP_PHP_BINARY' ) ?: 'php' );

// Useful during tests:
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', true );
