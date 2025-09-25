<?php
use PHPUnit\Framework\TestCase;
use Askary\AIStudyAssistant\Settings;

class SettingsSanitizeTest extends TestCase {
	public function test_sanitize_applies_defaults() {
		$clean = Settings::sanitize( array() );
		$this->assertSame( 'openai', $clean['provider'] );
		$this->assertSame( 'gpt-4o-mini', $clean['model'] );
		$this->assertSame( '', $clean['api_key'] );
		$this->assertSame( 900, $clean['cache_ttl'] );
	}

	public function test_sanitize_enforces_min_ttl() {
		$clean = Settings::sanitize( array( 'cache_ttl' => 10 ) );
		$this->assertSame( 60, $clean['cache_ttl'] );
	}

	public function test_sanitize_trims_api_key() {
		$clean = Settings::sanitize( array( 'api_key' => '  key  ' ) );
		$this->assertSame( 'key', $clean['api_key'] );
	}
}
