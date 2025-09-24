<?php
/**
 * Simple text analyzer (Flesch Reading Ease).
 *
 * Provides utilities for analyzing text readability using
 * the Flesch Reading Ease formula, along with a heuristic
 * syllable estimator.
 *
 * @package WP_AI_Study_Assistant
 */

namespace Askary\AIStudyAssistant;

/**
 * Analyzer class for computing readability metrics.
 *
 * Implements the Flesch Reading Ease formula:
 *   206.835 – (1.015 × ASL) – (84.6 × ASW)
 *
 * Where:
 * - ASL = Average Sentence Length (words ÷ sentences)
 * - ASW = Average Syllables per Word (syllables ÷ words)
 *
 * @since 1.0.0
 */
class Analyzer {

	/**
	 * Compute the Flesch Reading Ease score for a given text.
	 *
	 * Higher scores mean easier readability (e.g., scores above 60
	 * are typically considered easily readable by 13–15 year olds).
	 *
	 * @since 1.0.0
	 *
	 * @param string $text The text to analyze.
	 * @return float Flesch Reading Ease score, rounded to 2 decimals.
	 */
	public static function flesch_reading_ease( string $text ): float {
		// Strip HTML tags and trim whitespace.
		$text = trim( wp_strip_all_tags( $text ) );

		if ( '' === $text ) {
			return 0.0;
		}

		// Approximate sentence count based on punctuation.
		$sentences = max( 1, preg_match_all( '/[.!?]+/', $text ) );

		// Word count using built-in PHP function.
		$words = str_word_count( $text );

		// Estimate total syllables in the text.
		$syll = self::estimate_syllables( $text );

		// Average sentence length.
		$asl = $words / $sentences;

		// Average syllables per word.
		$asw = $syll / max( 1, $words );

		// Apply Flesch Reading Ease formula.
		$score = 206.835 - ( 1.015 * $asl ) - ( 84.6 * $asw );

		return round( $score, 2 );
	}

	/**
	 * Estimate the number of syllables in a block of text.
	 *
	 * Uses a simple heuristic:
	 * - Lowercases and strips non-alphabetic characters.
	 * - Counts groups of vowels (a, e, i, o, u, y) as syllables.
	 * - Guarantees at least 1 syllable per word.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text The text to analyze.
	 * @return int Estimated number of syllables.
	 */
	private static function estimate_syllables( string $text ): int {
		$words = preg_split( '/\s+/', strtolower( $text ) );
		$total = 0;

		foreach ( $words as $w ) {
			// Keep only letters.
			$w = preg_replace( '/[^a-z]/', '', $w );

			if ( '' === $w ) {
				continue;
			}

			// Count vowel groups.
			$groups = preg_match_all( '/[aeiouy]+/', $w );
			$total += max( 1, $groups );
		}

		return (int) $total;
	}
}
