<?php
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class FrontendShortcodeTest extends TestCase {
	public function test_shortcode_renders_markup() {
		$html = do_shortcode( '[wpai_flashcards_3d max="3" title="T" subtitle="S"]' );
		$this->assertStringContainsString( 'class="wpai-hero"', $html );
		$this->assertStringContainsString( 'class="wpai-grid"', $html );
		$this->assertStringContainsString( 'data-max="3"', $html );
		$this->assertStringContainsString( 'T', $html );
		$this->assertStringContainsString( 'S', $html );
	}
}
