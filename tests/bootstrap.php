<?php
/**
 * PHPUnit bootstrap for WP AI Study Assistant.
 *
 * Defines required WordPress test constants BEFORE wp-phpunit loads anything,
 * then boots the WordPress test suite and loads this plugin.
 */

declare(strict_types=1);

// 1) Composer autoloader (phpdotenv etc.).
$autoload = dirname( __DIR__ ) . '/vendor/autoload.php';
if ( file_exists( $autoload ) ) {
	require_once $autoload;
}

// 2) Load .env if present (safe if missing).
if ( class_exists( \Dotenv\Dotenv::class ) ) {
	\Dotenv\Dotenv::createImmutable( dirname( __DIR__ ) )->safeLoad();
}

// 3) REQUIRED by core test suite — use your defaults unless env overrides.
if ( ! defined( 'WP_TESTS_DOMAIN' ) ) {
	define( 'WP_TESTS_DOMAIN', getenv( 'WP_TESTS_DOMAIN' ) ?: '10up.local' );
}
if ( ! defined( 'WP_TESTS_EMAIL' ) ) {
	define( 'WP_TESTS_EMAIL', getenv( 'WP_TESTS_EMAIL' ) ?: 'wordpress@10up.local' );
}
if ( ! defined( 'WP_TESTS_TITLE' ) ) {
	define( 'WP_TESTS_TITLE', getenv( 'WP_TESTS_TITLE' ) ?: 'WordPress 10up Test Site' );
}
if ( ! defined( 'WP_PHP_BINARY' ) ) {
	define( 'WP_PHP_BINARY', getenv( 'WP_PHP_BINARY' ) ?: 'php' );
}

// 4) Database — supports Local.app socket via host: "localhost:/absolute/path/to/mysql.sock"
if ( ! defined( 'DB_NAME' ) ) {
	define( 'DB_NAME', getenv( 'WP_DB_NAME' ) ?: 'wp_tests' );
}
if ( ! defined( 'DB_USER' ) ) {
	define( 'DB_USER', getenv( 'WP_DB_USER' ) ?: 'root' );
}
if ( ! defined( 'DB_PASSWORD' ) ) {
	define( 'DB_PASSWORD', getenv( 'WP_DB_PASSWORD' ) ?: '' );
}
if ( ! defined( 'DB_HOST' ) ) {
	define( 'DB_HOST', getenv( 'WP_DB_HOST' ) ?: '127.0.0.1' );
}
if ( ! defined( 'DB_CHARSET' ) ) {
	define( 'DB_CHARSET', getenv( 'WP_DB_CHARSET' ) ?: 'utf8' );
}
if ( ! defined( 'DB_COLLATE' ) ) {
	define( 'DB_COLLATE', getenv( 'WP_DB_COLLATE' ) ?: '' );
}

// 5) Table prefix must be a *variable* for WordPress.
$GLOBALS['table_prefix'] = getenv( 'WP_TABLE_PREFIX' ) ?: 'wptests_';

// 6) Useful debug flags during tests.
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', true );
}
if ( ! defined( 'WP_DEBUG_DISPLAY' ) ) {
	define( 'WP_DEBUG_DISPLAY', true );
}

// 7) wp-phpunit location (fallback if not already defined).
if ( ! defined( 'WP_PHPUNIT__DIR' ) ) {
	define( 'WP_PHPUNIT__DIR', getenv( 'WP_PHPUNIT__DIR' ) ?: 'vendor/wp-phpunit/wp-phpunit' );
}

// Visible markers so you can verify bootstrap executed and constants are set:
fwrite( STDERR, '[bootstrap] WP_TESTS_DOMAIN=' . WP_TESTS_DOMAIN . PHP_EOL );
fwrite( STDERR, '[bootstrap] WP_TESTS_EMAIL=' . WP_TESTS_EMAIL . PHP_EOL );
fwrite( STDERR, '[bootstrap] WP_TESTS_TITLE=' . WP_TESTS_TITLE . PHP_EOL );
fwrite( STDERR, '[bootstrap] DB_HOST=' . DB_HOST . PHP_EOL );
fwrite( STDERR, '[bootstrap] WP_PHPUNIT__DIR=' . WP_PHPUNIT__DIR . PHP_EOL );

// 8) Load WP test helpers *after* constants exist.
require_once WP_PHPUNIT__DIR . '/includes/functions.php';

// 9) Load the plugin under test.
tests_add_filter(
	'muplugins_loaded',
	static function (): void {
		require dirname( __DIR__ ) . '/wp-ai-study-assistant.php';
	}
);

// 10) Boot the WordPress test suite.
require WP_PHPUNIT__DIR . '/includes/bootstrap.php';
