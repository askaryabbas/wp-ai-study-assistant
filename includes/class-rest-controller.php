<?php
/**
 * REST endpoints for AI features.
 *
 * Exposes REST routes for generating flashcards (Q/A pairs) and meta
 * descriptions using an AI provider. Includes capability checks, nonce
 * support (handled by apiFetch on the client), basic caching, and simple
 * input sanitization.
 *
 * @package WP_AI_Study_AssISTANT
 */

namespace Askary\AIStudyAssistant;

use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * REST controller for AI Study Assistant plugin.
 *
 * Responsibilities:
 * - Register REST API routes under the plugin namespace.
 * - Validate/sanitize input payloads.
 * - Call the configured Provider to generate responses.
 * - Return normalized JSON responses and appropriate HTTP status codes.
 *
 * @since 1.0.0
 */
class Rest_Controller {

	/**
	 * REST API namespace.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $ns = 'askary-ai/v1';

	/**
	 * Register REST API routes for flashcards and meta description generation.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_routes(): void {
		// POST /flashcards → Generate Q/A pairs from source text.
		register_rest_route(
			$this->ns,
			'/flashcards',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'flashcards' ),
					'permission_callback' => static function () {
						return current_user_can( 'edit_posts' );
					},
					'args'                => array(
						'text' => array(
							'type'        => 'string',
							'required'    => true,
							'description' => __( 'Source text to extract questions and answers from.', 'wp-ai-study-assistant' ),
						),
					),
				),
			)
		);

		// POST /meta → Generate SEO meta description from title/content.
		register_rest_route(
			$this->ns,
			'/meta',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'meta' ),
					'permission_callback' => static function () {
						return current_user_can( 'edit_posts' );
					},
					'args'                => array(
						'title'   => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'Post/page title used as context.', 'wp-ai-study-assistant' ),
						),
						'content' => array(
							'type'        => 'string',
							'required'    => true,
							'description' => __( 'Main content used to compose a concise meta description.', 'wp-ai-study-assistant' ),
						),
					),
				),
			)
		);
	}

	/**
	 * Resolve the AI provider.
	 *
	 * Uses a filter to allow replacement (e.g., Anthropic, Bedrock).
	 *
	 * @since 1.0.0
	 * @return Provider Configured provider instance.
	 */
	private function provider(): Provider {
		$provider = new OpenAI_Provider();

		/**
		 * Filter the provider instance.
		 *
		 * Enables swapping or decorating the provider implementation.
		 *
		 * @since 1.0.0
		 *
		 * @param Provider $provider The provider instance.
		 */
		return apply_filters( 'wpai_provider', $provider );
	}

	/**
	 * Handle POST /flashcards.
	 *
	 * Accepts JSON payload: { "text": "..." }
	 * Returns: { ok: true, cards: [ { q: "...", a: "..." }, ... ] }
	 *
	 * Caches the card list in a transient keyed by the source text (hash),
	 * honoring the "cache_ttl" plugin setting.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $req REST request instance.
	 * @return WP_REST_Response JSON response.
	 */
	public function flashcards( WP_REST_Request $req ): WP_REST_RESPONSE {
		$data = $req->get_json_params();
		$text = isset( $data['text'] ) ? (string) $data['text'] : '';
		$text = wp_strip_all_tags( $text );

		if ( '' === $text ) {
			return new WP_REST_Response(
				array( 'error' => __( 'Empty text.', 'wp-ai-study-assistant' ) ),
				400
			);
		}

		// Cache lookup.
		$opt    = get_option( 'wpai_settings', array() );
		$ttl    = isset( $opt['cache_ttl'] ) ? absint( $opt['cache_ttl'] ) : 900;
		$key    = 'wpai_fc_' . md5( $text );
		$cached = get_transient( $key );

		if ( $cached ) {
			return new WP_REST_Response(
				array(
					'ok'     => true,
					'cards'  => $cached,
					'cached' => true,
				)
			);
		}

		// Compose provider prompt.
		$messages = array(
			array(
				'role'    => 'system',
				/* translators: Instruction to AI model for returning pure JSON array of Q/A pairs. */
				'content' => __( 'You generate exactly 5 flashcards. Output pure JSON: [{"q":"...", "a":"..."}]. No preamble.', 'wp-ai-study-assistant' ),
			),
			array(
				'role'    => 'user',
				'content' => $text,
			),
		);

		// Call provider.
		$res = $this->provider()->chat( $messages, array() );

		if ( empty( $res['ok'] ) ) {
			$error = isset( $res['error'] ) ? (string) $res['error'] : __( 'Provider error.', 'wp-ai-study-assistant' );

			return new WP_REST_Response(
				array( 'error' => $error ),
				500
			);
		}

		// Parse JSON array of cards. Try to salvage if model returns surrounding text.
		$json = json_decode( (string) $res['content'], true );
		if ( ! is_array( $json ) && preg_match( '/\[[\s\S]*\]/', (string) $res['content'], $m ) ) {
			$json = json_decode( $m[0], true );
		}
		if ( ! is_array( $json ) ) {
			return new WP_REST_Response(
				array( 'error' => __( 'Malformed provider response.', 'wp-ai-study-assistant' ) ),
				500
			);
		}

		// Cache and return.
		set_transient( $key, $json, $ttl );

		return new WP_REST_Response(
			array(
				'ok'    => true,
				'cards' => $json,
			)
		);
	}

	/**
	 * Handle POST /meta.
	 *
	 * Accepts JSON payload: { "title": "...", "content": "..." }
	 * Returns: { ok: true, meta: "…", readingEase: 0.00 }
	 *
	 * Uses the Analyzer to compute Flesch Reading Ease and sends context to
	 * the AI provider. Strips surrounding quotes from the returned text.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $req REST request instance.
	 * @return WP_REST_Response JSON response.
	 */
	public function meta( WP_REST_Request $req ): WP_REST_RESPONSE {
		$data    = $req->get_json_params();
		$title   = isset( $data['title'] ) ? sanitize_text_field( (string) $data['title'] ) : '';
		$content = isset( $data['content'] ) ? (string) $data['content'] : '';
		$content = wp_strip_all_tags( $content );

		if ( '' === $content ) {
			return new WP_REST_Response(
				array( 'error' => __( 'Empty content.', 'wp-ai-study-assistant' ) ),
				400
			);
		}

		// Compute readability to aid the model.
		$score = Analyzer::flesch_reading_ease( $content );

		// Compose provider prompt.
		$messages = array(
			array(
				'role'    => 'system',
				/* translators: Instruction to AI model to produce a concise SEO meta description. */
				'content' => __( 'Write a compelling meta description (<= 155 chars). No quotes.', 'wp-ai-study-assistant' ),
			),
			array(
				'role'    => 'user',
				'content' => 'Title: ' . $title . "\n\nContent:\n" . $content . "\n\nReadingEase:" . $score,
			),
		);

		// Slightly higher temperature for creative phrasing.
		$res = $this->provider()->chat(
			$messages,
			array(
				'temperature' => 0.4,
			)
		);

		if ( empty( $res['ok'] ) ) {
			$error = isset( $res['error'] ) ? (string) $res['error'] : __( 'Provider error.', 'wp-ai-study-assistant' );

			return new WP_REST_Response(
				array( 'error' => $error ),
				500
			);
		}

		// Normalize output: trim and strip stray quotes sometimes returned by models.
		$desc = trim( (string) $res['content'] );
		$desc = preg_replace( '/^"+|"+$/', '', $desc );

		return new WP_REST_Response(
			array(
				'ok'          => true,
				'meta'        => $desc,
				'readingEase' => $score,
			)
		);
	}
}
