<?php
/**
 * Auto-prepend for PHPUnit: define WP test constants before anything else loads.
 * This runs *before* wp-phpunit/bootstrap/includes/functions.php to prevent
 * "Undefined constant WP_TESTS_DOMAIN" fatals.
 */

declare(strict_types=1);

// Load Composer autoloader so phpdotenv is available.
$autoload = dirname( __DIR__ ) . '/vendor/autoload.php';
if ( file_exists( $autoload ) ) {
	require_once $autoload;
}

// Load .env (safe if missing).
if ( class_exists( \Dotenv\Dotenv::class ) ) {
	\Dotenv\Dotenv::createImmutable( dirname( __DIR__ ) )->safeLoad();
}

/**
 * Define a constant if not already defined.
 *
 * @param string $name  Constant name.
 * @param mixed  $value Default value.
 */
function wpai_define_if_missing( string $name, $value ): void {
	if ( ! defined( $name ) ) {
		define( $name, $value );
	}
}

// === REQUIRED by core test suite ===
wpai_define_if_missing( 'WP_TESTS_DOMAIN', getenv( 'WP_TESTS_DOMAIN' ) ?: '10up.local' );
wpai_define_if_missing( 'WP_TESTS_EMAIL', getenv( 'WP_TESTS_EMAIL' ) ?: 'wordpress@10up.local' );
wpai_define_if_missing( 'WP_TESTS_TITLE', getenv( 'WP_TESTS_TITLE' ) ?: 'WordPress Test Site' );
wpai_define_if_missing( 'WP_PHP_BINARY', getenv( 'WP_PHP_BINARY' ) ?: 'php' );

// === DB (socket or TCP) ===
// Use your Local.app socket: "localhost:/absolute/path/to/mysqld.sock"
wpai_define_if_missing( 'DB_NAME', getenv( 'WP_DB_NAME' ) ?: 'wp_tests' );
wpai_define_if_missing( 'DB_USER', getenv( 'WP_DB_USER' ) ?: 'root' );
wpai_define_if_missing( 'DB_PASSWORD', getenv( 'WP_DB_PASSWORD' ) ?: '' );
wpai_define_if_missing( 'DB_HOST', getenv( 'WP_DB_HOST' ) ?: '127.0.0.1' );
wpai_define_if_missing( 'DB_CHARSET', getenv( 'WP_DB_CHARSET' ) ?: 'utf8' );
wpai_define_if_missing( 'DB_COLLATE', getenv( 'WP_DB_COLLATE' ) ?: '' );

// Table prefix must be a variable (not a constant) for WordPress.
$GLOBALS['table_prefix'] = getenv( 'WP_TABLE_PREFIX' ) ?: 'wptests_';

// Helpful defaults.
wpai_define_if_missing( 'WP_DEBUG', true );
wpai_define_if_missing( 'WP_DEBUG_DISPLAY', true );

// Tell wp-phpunit where it lives if not set.
wpai_define_if_missing( 'WP_PHPUNIT__DIR', getenv( 'WP_PHPUNIT__DIR' ) ?: 'vendor/wp-phpunit/wp-phpunit' );
