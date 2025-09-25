<?php
/**
 * WordPress PHPUnit test configuration for WP AI Study Assistant.
 *
 * Loads .env (if present) and defines the constants the core test suite requires
 * *before* it boots. This file is loaded by wp-phpunit when WP_TESTS_CONFIG is set.
 */

declare(strict_types=1);

// Load Composer autoloader for phpdotenv (dev dependency).
$autoload = dirname( __DIR__ ) . '/vendor/autoload.php';
if ( file_exists( $autoload ) ) {
	require_once $autoload;
}

if ( class_exists( \Dotenv\Dotenv::class ) ) {
	$dotenv = \Dotenv\Dotenv::createImmutable( dirname( __DIR__ ) );
	$dotenv->safeLoad(); // Do not error if .env missing.
}

// ---- Database (socket or TCP supported) ----
define( 'DB_NAME', getenv( 'WP_DB_NAME' ) ?: 'wp_tests' );
define( 'DB_USER', getenv( 'WP_DB_USER' ) ?: 'root' );
define( 'DB_PASSWORD', getenv( 'WP_DB_PASSWORD' ) ?: '' );
define( 'DB_HOST', getenv( 'WP_DB_HOST' ) ?: '127.0.0.1' ); // I added socket in .env
define( 'DB_CHARSET', getenv( 'WP_DB_CHARSET' ) ?: 'utf8' );
define( 'DB_COLLATE', getenv( 'WP_DB_COLLATE' ) ?: '' );

// Separate table prefix to avoid collisions.
$table_prefix = getenv( 'WP_TABLE_PREFIX' ) ?: 'wptests_';

// ---- Required by core test suite ----
define( 'WP_TESTS_DOMAIN', getenv( 'WP_TESTS_DOMAIN' ) ?: 'example.org' );
define( 'WP_TESTS_EMAIL', getenv( 'WP_TESTS_EMAIL' ) ?: 'admin@example.org' );
define( 'WP_TESTS_TITLE', getenv( 'WP_TESTS_TITLE' ) ?: 'WordPress Test Site' );
define( 'WP_PHP_BINARY', getenv( 'WP_PHP_BINARY' ) ?: 'php' );

// Debug flags (recommended during tests).
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', true );
