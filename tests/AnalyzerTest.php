<?php
use PHPUnit\Framework\TestCase;
use Askary\AIStudyAssistant\Analyzer;

class AnalyzerTest extends TestCase {
	public function test_flesch_reading_ease_returns_float() {
		$score = Analyzer::flesch_reading_ease( 'This is a simple sentence. Another one follows.' );
		$this->assertIsFloat( $score );
	}

	public function test_flesch_on_empty_text_is_zero() {
		$this->assertSame( 0.0, Analyzer::flesch_reading_ease( '' ) );
	}

	public function test_flesch_reasonable_range() {
		$score = Analyzer::flesch_reading_ease( 'WordPress makes content management simple and powerful.' );
		$this->assertGreaterThanOrEqual( -100.0, $score );
		$this->assertLessThanOrEqual( 150.0, $score );
	}
}
