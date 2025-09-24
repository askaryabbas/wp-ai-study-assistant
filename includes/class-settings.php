<?php
/**
 * Settings page & option handling.
 *
 * Registers the "WP AI Assistant" settings page, stores provider options,
 * and renders fields using the WordPress Settings API.
 *
 * @package WP_AI_Study_Assistant
 */

namespace Askary\AIStudyAssistant;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Settings handler for WP AI Study Assistant.
 *
 * Responsibilities:
 * - Register the plugin option (`wpai_settings`) and sanitize values.
 * - Add the settings screen under Settings → WP AI Assistant.
 * - Render fields for provider, model, API key, and cache TTL.
 *
 * @since 1.0.0
 */
class Settings {

	/**
	 * Hook settings page and registration.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function register(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * Add "WP AI Assistant" under Settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function add_menu(): void {
		add_options_page(
			__( 'WP AI Study Assistant', 'wp-ai-study-assistant' ),
			__( 'WP AI Assistant', 'wp-ai-study-assistant' ),
			'manage_options',
			'wpai-settings',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Register setting, section, and fields.
	 *
	 * Option: `wpai_settings` (array)
	 *  - provider   (string) Provider slug, e.g. "openai".
	 *  - model      (string) Default model id, e.g. "gpt-4o-mini".
	 *  - api_key    (string) Secret API key.
	 *  - cache_ttl  (int)    Cache lifetime in seconds.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function register_settings(): void {
		register_setting(
			'wpai_settings_group',
			'wpai_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'show_in_rest'      => false,
				'default'           => array(
					'provider'  => 'openai',
					'model'     => 'gpt-4o-mini',
					'api_key'   => '',
					'cache_ttl' => 900,
				),
			)
		);

		add_settings_section(
			'wpai_main',
			__( 'Provider Settings', 'wp-ai-study-assistant' ),
			static function (): void {
				echo '<p>' . esc_html__( 'Configure your AI provider and defaults.', 'wp-ai-study-assistant' ) . '</p>';
			},
			'wpai-settings'
		);

		add_settings_field(
			'wpai_provider',
			__( 'Provider', 'wp-ai-study-assistant' ),
			array( __CLASS__, 'field_provider' ),
			'wpai-settings',
			'wpai_main'
		);

		add_settings_field(
			'wpai_model',
			__( 'Model', 'wp-ai-study-assistant' ),
			array( __CLASS__, 'field_model' ),
			'wpai-settings',
			'wpai_main'
		);

		add_settings_field(
			'wpai_api_key',
			__( 'API Key', 'wp-ai-study-assistant' ),
			array( __CLASS__, 'field_api_key' ),
			'wpai-settings',
			'wpai_main'
		);

		add_settings_field(
			'wpai_cache_ttl',
			__( 'Cache TTL (seconds)', 'wp-ai-study-assistant' ),
			array( __CLASS__, 'field_cache_ttl' ),
			'wpai-settings',
			'wpai_main'
		);
	}

	/**
	 * Sanitize settings payload before saving.
	 *
	 * Ensures valid defaults and types:
	 * - provider: text
	 * - model: text
	 * - api_key: trimmed string
	 * - cache_ttl: min 60 seconds
	 *
	 * @since 1.0.0
	 *
	 * @param array $value Raw submitted values.
	 * @return array Sanitized settings.
	 */
	public static function sanitize( $value ): array {
		$clean              = array();
		$clean['provider']  = isset( $value['provider'] ) ? sanitize_text_field( $value['provider'] ) : 'openai';
		$clean['model']     = isset( $value['model'] ) ? sanitize_text_field( $value['model'] ) : 'gpt-4o-mini';
		$clean['api_key']   = isset( $value['api_key'] ) ? trim( (string) $value['api_key'] ) : '';
		$clean['cache_ttl'] = isset( $value['cache_ttl'] ) ? max( 60, absint( $value['cache_ttl'] ) ) : 900;

		/**
		 * Filter sanitized settings before saving.
		 *
		 * Allows extending providers or altering defaults.
		 *
		 * @since 1.0.0
		 *
		 * @param array $clean Sanitized values.
		 * @param array $value Raw submitted values.
		 */
		return apply_filters( 'wpai_settings_sanitize', $clean, $value );
	}

	/**
	 * Render the provider selection field.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function field_provider(): void {
		$opt      = get_option( 'wpai_settings', array() );
		$provider = isset( $opt['provider'] ) ? $opt['provider'] : 'openai';
		?>
		<select name="wpai_settings[provider]">
			<option value="openai" <?php selected( $provider, 'openai' ); ?>>
				<?php esc_html_e( 'OpenAI (Chat Completions)', 'wp-ai-study-assistant' ); ?>
			</option>
		</select>
		<?php
	}

	/**
	 * Render the model input field.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function field_model(): void {
		$opt   = get_option( 'wpai_settings', array() );
		$model = isset( $opt['model'] ) ? $opt['model'] : 'gpt-4o-mini';
		?>
		<input
			type="text"
			class="regular-text"
			name="wpai_settings[model]"
			value="<?php echo esc_attr( $model ); ?>"
		/>
		<p class="description">
			<?php esc_html_e( 'Model ID supported by your provider (e.g., gpt-4o-mini).', 'wp-ai-study-assistant' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the API key input field.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function field_api_key(): void {
		$opt = get_option( 'wpai_settings', array() );
		?>
		<input
			type="password"
			class="regular-text"
			name="wpai_settings[api_key]"
			value="<?php echo esc_attr( $opt['api_key'] ?? '' ); ?>"
			autocomplete="off"
		/>
		<?php
	}

	/**
	 * Render the cache TTL input field.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function field_cache_ttl(): void {
		$opt = get_option( 'wpai_settings', array() );
		$ttl = isset( $opt['cache_ttl'] ) ? absint( $opt['cache_ttl'] ) : 900;
		?>
		<input
			type="number"
			min="60"
			step="30"
			name="wpai_settings[cache_ttl]"
			value="<?php echo esc_attr( $ttl ); ?>"
		/>
		<?php
	}

	/**
	 * Render the settings page markup.
	 *
	 * Uses Settings API helpers:
	 * - settings_fields( 'wpai_settings_group' )
	 * - do_settings_sections( 'wpai-settings' )
	 * - submit_button()
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'WP AI Study Assistant', 'wp-ai-study-assistant' ); ?></h1>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'wpai_settings_group' );
				do_settings_sections( 'wpai-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
