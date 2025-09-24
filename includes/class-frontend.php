<?php
/**
 * Public-facing UI: shortcode + assets.
 *
 * Registers the 3D flashcards shortcode and enqueues the built
 * public assets only when the shortcode is present on the page.
 *
 * @package WP_AI_Study_Assistant
 */

namespace Askary\AIStudyAssistant;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handles the public-facing UI for the WP AI Study Assistant plugin.
 *
 * Responsibilities:
 * - Register the `[wpai_flashcards_3d]` shortcode.
 * - Enqueue the public JS/CSS bundles (built by Webpack) when needed.
 * - Localize runtime configuration and i18n strings for the public script.
 *
 * @since 1.0.0
 */
class Frontend {

	/**
	 * Register shortcode and enqueue hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function register(): void {
		add_shortcode( 'wpai_flashcards_3d', array( __CLASS__, 'shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Enqueue public assets when the shortcode is present on a singular view.
	 *
	 * Uses `filemtime()` for cache-busting in development while keeping
	 * stable handles. Falls back to `WPAI_VERSION` if files are missing.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function enqueue(): void {
		// Only enqueue on single post/page screens.
		if ( ! is_singular() ) {
			return;
		}

		global $post;

		// Abort if no post or the shortcode is not used in the content.
		if ( ! $post || false === has_shortcode( (string) $post->post_content, 'wpai_flashcards_3d' ) ) {
			return;
		}

		$public_js  = WPAI_PLUGIN_DIR . 'assets/dist/public.js';
		$public_css = WPAI_PLUGIN_DIR . 'assets/dist/public.css';

		wp_enqueue_style(
			'wpai-public',
			WPAI_PLUGIN_URL . 'assets/dist/public.css',
			array(),
			file_exists( $public_css ) ? (string) filemtime( $public_css ) : WPAI_VERSION
		);

		wp_enqueue_script(
			'wpai-public',
			WPAI_PLUGIN_URL . 'assets/dist/public.js',
			array(), // No WP script deps; vanilla frontend.
			file_exists( $public_js ) ? (string) filemtime( $public_js ) : WPAI_VERSION,
			true
		);

		// Provide runtime config + translatable UI strings to the public script.
		wp_localize_script(
			'wpai-public',
			'WPAI_PUBLIC',
			array(
				'ns'    => 'askary-ai/v1',                    // REST namespace (server routes).
				'nonce' => wp_create_nonce( 'wp_rest' ),      // REST nonce for authenticated calls.
				'root'  => esc_url_raw( get_rest_url() ),     // Site REST root URL.
				'i18n'  => array(
					'generate'    => __( 'Generate', 'wp-ai-study-assistant' ),
					'generating'  => __( 'Generating…', 'wp-ai-study-assistant' ),
					'placeholder' => __( 'Paste study text here…', 'wp-ai-study-assistant' ),
					'error'       => __( 'Something went wrong. Please try again.', 'wp-ai-study-assistant' ),
				),
			)
		);
	}

	/**
	 * Render the `[wpai_flashcards_3d]` shortcode.
	 *
	 * Output includes:
	 * - A hero section with title/subtitle.
	 * - A form (textarea + button) that triggers AI generation on the frontend.
	 * - A results grid where the 3D flip cards are injected by JS.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *                    Supported keys:
	 *                    - 'max'         (int)    Number of cards to show. Default 5.
	 *                    - 'placeholder' (string) Pre-filled text for the textarea. Default empty.
	 *                    - 'title'       (string) Hero title. Default translated "AI Study Assistant".
	 *                    - 'subtitle'    (string) Hero subtitle. Default translated "Turn your notes into flip flashcards".
	 * @return string HTML markup for the UI.
	 */
	public static function shortcode( $atts ): string {
		$atts = shortcode_atts(
			array(
				'max'         => 5,
				'placeholder' => '',
				'title'       => __( 'AI Study Assistant', 'wp-ai-study-assistant' ),
				'subtitle'    => __( 'Turn your notes into flip flashcards', 'wp-ai-study-assistant' ),
			),
			$atts,
			'wpai_flashcards_3d'
		);

		// Start output buffering to build the HTML.
		ob_start();
		?>
		<section class="wpai-hero">
			<div class="wpai-hero__inner">
				<h1 class="wpai-hero__title"><?php echo esc_html( $atts['title'] ); ?></h1>
				<p class="wpai-hero__subtitle"><?php echo esc_html( $atts['subtitle'] ); ?></p>

				<form class="wpai-form" aria-label="<?php esc_attr_e( 'Generate flashcards', 'wp-ai-study-assistant' ); ?>">
					<label for="wpai-text" class="screen-reader-text">
						<?php esc_html_e( 'Study text', 'wp-ai-study-assistant' ); ?>
					</label>

					<textarea
						id="wpai-text"
						class="wpai-textarea"
						rows="8"
						placeholder="<?php echo esc_attr( $atts['placeholder'] ? $atts['placeholder'] : __( 'Paste study text here…', 'wp-ai-study-assistant' ) ); ?>"
					></textarea>

					<div class="wpai-actions">
						<button type="submit" class="wpai-button" data-state="idle">
							<span class="wpai-button__label">
								<?php esc_html_e( 'Generate', 'wp-ai-study-assistant' ); ?>
							</span>
							<span class="wpai-button__spinner" aria-hidden="true"></span>
						</button>

						<span class="wpai-status" role="status" aria-live="polite"></span>
					</div>
				</form>
			</div>
		</section>

		<section class="wpai-results">
			<div
				class="wpai-grid"
				data-max="<?php echo (int) $atts['max']; ?>"
				aria-live="polite"
				aria-busy="false"
			></div>
		</section>
		<?php
		// Return the buffered HTML.
		return (string) ob_get_clean();
	}
}
