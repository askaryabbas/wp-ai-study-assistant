<?php
/**
 * OpenAI provider: chat completions.
 *
 * Provides a thin wrapper around the OpenAI Chat Completions API using
 * WordPress HTTP functions and plugin options for configuration.
 *
 * @package WP_AI_Study_Assistant
 */

namespace Askary\AIStudyAssistant;

/**
 * OpenAI_Provider class.
 *
 * Implements the Provider interface for the OpenAI Chat Completions API.
 * Expects an option named `wpai_settings` with:
 * - api_key   (string) Secret API key.
 * - model     (string) Default model ID, e.g. "gpt-4o-mini".
 *
 * @since 1.0.0
 */
class OpenAI_Provider implements Provider {

	/**
	 * Send a Chat Completions request to OpenAI.
	 *
	 * Example usage:
	 *
	 * $provider = new OpenAI_Provider();
	 * $resp = $provider->chat(
	 *     array(
	 *         array( 'role' => 'system', 'content' => 'You are helpful.' ),
	 *         array( 'role' => 'user',   'content' => 'Write a haiku.' ),
	 *     ),
	 *     array( 'model' => 'gpt-4o-mini', 'temperature' => 0.2 )
	 * );
	 *
	 * @since 1.0.0
	 *
	 * @param array $messages Array of chat messages, each having 'role' and 'content' keys.
	 *                        Example: array( array( 'role' => 'user', 'content' => 'Hello' ) ).
	 * @param array $args     Optional overrides:
	 *                        - 'model'       (string) OpenAI model ID. Defaults to settings->model or 'gpt-4o-mini'.
	 *                        - 'temperature' (float)  Sampling temperature. Default 0.2.
	 * @return array {
	 *     Response array.
	 *
	 *     @type bool        $ok      True on success, false on failure.
	 *     @type string|null $content Assistant message content when $ok is true; null otherwise.
	 *     @type string|null $error   Error message when $ok is false; null otherwise.
	 * }
	 */
	public function chat( array $messages, array $args = array() ): array {
		// Load settings.
		$opt   = get_option( 'wpai_settings', array() );
		$key   = isset( $opt['api_key'] ) ? trim( (string) $opt['api_key'] ) : '';
		$model = isset( $args['model'] ) ? (string) $args['model'] : ( isset( $opt['model'] ) ? (string) $opt['model'] : 'gpt-4o-mini' );

		// Guard: API key required.
		if ( '' === $key ) {
			return array(
				'ok'      => false,
				'content' => null,
				'error'   => __( 'Missing API key.', 'wp-ai-study-assistant' ),
			);
		}

		// Build request body with safe defaults.
		$body = array(
			'model'       => $model,
			'messages'    => $messages,
			'temperature' => isset( $args['temperature'] ) ? (float) $args['temperature'] : 0.2,
		);

		/**
		 * Filter the OpenAI request body before sending.
		 *
		 * Allows plugins/themes to modify the payload (e.g., add 'top_p', 'max_tokens', or tools).
		 *
		 * @since 1.0.0
		 *
		 * @param array $body Request body to send to OpenAI.
		 */
		$body = apply_filters( 'wpai_openai_request_body', $body );

		// Endpoint + default HTTP args.
		$endpoint = 'https://api.openai.com/v1/chat/completions';

		$http_args = array(
			'headers'    => array(
				'Authorization' => 'Bearer ' . $key,
				'Content-Type'  => 'application/json',
			),
			'body'       => wp_json_encode( $body ),
			'timeout'    => 30,
			'user-agent' => 'WP-AI-Study-Assistant/' . WPAI_VERSION . '; ' . home_url( '/' ),
		);

		/**
		 * Filter the HTTP request args for the OpenAI call.
		 *
		 * Useful to tweak timeouts, proxies, or headers (e.g., for enterprise gateways).
		 *
		 * @since 1.0.0
		 *
		 * @param array  $http_args WP HTTP API args.
		 * @param string $endpoint  Target endpoint URL.
		 */
		$http_args = apply_filters( 'wpai_openai_http_args', $http_args, $endpoint );

		// Execute HTTP request.
		$response = wp_remote_post( $endpoint, $http_args );

		// Transport-level failure.
		if ( is_wp_error( $response ) ) {
			return array(
				'ok'      => false,
				'content' => null,
				'error'   => $response->get_error_message(),
			);
		}

		// Parse response.
		$code = (int) wp_remote_retrieve_response_code( $response );
		$raw  = (string) wp_remote_retrieve_body( $response );
		$data = json_decode( $raw, true );

		// API-level failure.
		if ( 200 !== $code || empty( $data['choices'][0]['message']['content'] ) ) {
			$error = isset( $data['error']['message'] ) ? (string) $data['error']['message'] : __( 'Provider error.', 'wp-ai-study-assistant' );

			return array(
				'ok'      => false,
				'content' => null,
				'error'   => $error,
			);
		}

		// Success.
		return array(
			'ok'      => true,
			'content' => (string) $data['choices'][0]['message']['content'],
			'error'   => null,
		);
	}
}
