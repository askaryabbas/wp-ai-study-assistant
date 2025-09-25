<?php
use PHPUnit\Framework\TestCase;
use Askary\AIStudyAssistant\OpenAI_Provider;

class OpenAIProviderTest extends TestCase {
    public function test_missing_api_key_returns_error() {
        update_option('wpai_settings', ['api_key' => '', 'model' => 'gpt-4o-mini']);
        $provider = new OpenAI_Provider();
        $res = $provider->chat([['role' => 'user', 'content' => 'Hi']]);
        $this->assertFalse($res['ok']);
        $this->assertNotEmpty($res['error']);
    }
}