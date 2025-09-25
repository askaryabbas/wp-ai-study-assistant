<?php
use Yoast\PHPUnitPolyfills\TestCases\TestCase;
use Askary\AIStudyAssistant\Provider;
use WP_REST_Request;

class RestControllerTest extends TestCase {

	public static function set_up_before_class(): void {
		// Allow test to run routes by bootstrapping plugin already done in tests/bootstrap.php.
	}

	public function test_flashcards_requires_text() {
		$req = new WP_REST_Request( 'POST', '/askary-ai/v1/flashcards' );
		$req->set_body_params( array() );
		$res = rest_do_request( $req );
		$this->assertSame( 400, $res->get_status() );
	}

	public function test_flashcards_success_with_stub_provider() {
		// Stub provider via filter to avoid network calls.
		add_filter(
			'wpai_provider',
			function () {
				return new class() implements Provider {
					public function chat( array $messages, array $args = array() ): array {
						return array(
							'ok'      => true,
							'content' => '[{"q":"What?","a":"This."},{"q":"Why?","a":"Because."}]',
							'error'   => null,
						);
					}
				};
			}
		);

		$req = new WP_REST_Request( 'POST', '/askary-ai/v1/flashcards' );
		$req->set_body_params( array( 'text' => 'Some text' ) );
		$res = rest_do_request( $req );

		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertTrue( $data['ok'] );
		$this->assertIsArray( $data['cards'] );
		$this->assertNotEmpty( $data['cards'] );
	}

	public function test_meta_requires_content() {
		$req = new WP_REST_Request( 'POST', '/askary-ai/v1/meta' );
		$req->set_body_params( array( 'title' => 'Hello' ) );
		$res = rest_do_request( $req );
		$this->assertSame( 400, $res->get_status() );
	}

	public function test_meta_success_with_stub_provider() {
		add_filter(
			'wpai_provider',
			function () {
				return new class() implements Provider {
					public function chat( array $messages, array $args = array() ): array {
						return array(
							'ok'      => true,
							'content' => 'A concise meta description.',
							'error'   => null,
						);
					}
				};
			}
		);

		$req = new WP_REST_Request( 'POST', '/askary-ai/v1/meta' );
		$req->set_body_params(
			array(
				'title'   => 't',
				'content' => 'c',
			)
		);
		$res = rest_do_request( $req );

		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertTrue( $data['ok'] );
		$this->assertIsString( $data['meta'] );
	}
}
