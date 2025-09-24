<?php
/**
 * Provider interface.
 *
 * Defines a common contract for AI providers used by the
 * WP AI Study Assistant plugin. Any provider implementation
 * (e.g., OpenAI, Anthropic, local LLM) must implement this
 * interface so it can be swapped interchangeably.
 *
 * @package WP_AI_Study_Assistant
 */

namespace Askary\AIStudyAssistant;

/**
 * Provider interface.
 *
 * @since 1.0.0
 */
interface Provider {

	/**
	 * Send a chat completion request to the provider and return a normalized response.
	 *
	 * Each provider must transform its raw API response into a standardized
	 * associative array with three keys: `ok`, `content`, and `error`.
	 *
	 * Example return on success:
	 * ```php
	 * array(
	 *     'ok'      => true,
	 *     'content' => 'The assistant reply text...',
	 *     'error'   => null,
	 * )
	 * ```
	 *
	 * Example return on failure:
	 * ```php
	 * array(
	 *     'ok'      => false,
	 *     'content' => null,
	 *     'error'   => 'Error message here',
	 * )
	 * ```
	 *
	 * @since 1.0.0
	 *
	 * @param array $messages Array of chat messages to send. Each element should be an
	 *                        associative array with at least `role` and `content` keys:
	 *                        `array( 'role' => 'user', 'content' => 'Hello' )`.
	 * @param array $args     Optional arguments for the request. Common keys include:
	 *                        - 'model'       (string) Model identifier to use.
	 *                        - 'temperature' (float)  Sampling temperature.
	 * @return array {
	 *     Normalized response.
	 *
	 *     @type bool        $ok      True if request succeeded, false otherwise.
	 *     @type string|null $content Assistant message text on success, null on failure.
	 *     @type string|null $error   Error message on failure, null on success.
	 * }
	 */
	public function chat( array $messages, array $args = array() ): array;
}
