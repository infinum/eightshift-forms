<?php
namespace EightshiftFormsTests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use EightshiftForms\Main\Main;

class BaseTest extends \Codeception\Test\Unit
{

	public const HOME_URL = 'https://homeurl.com';
	public const WP_REDIRECT_ACTION = 'eightshift_forms_test/wp_safe_redirect_happened';
	public const WP_MAIL_ACTION = 'eightshift_forms_test/wp_mail_happened';

	protected function _before()
	{
		$this->main = new Main([], 'EightshiftFormsTests');
		$this->main->setTest(true);
		$this->diContainer = $this->main->buildDiContainer();

		Monkey\setUp();

		Functions\stubTranslationFunctions();

		// Given functions will return the first argument they will receive,
		// just like `when( $function_name )->justReturnArg()` was used for all of them.
		Functions\stubs(
			[
				'esc_attr',
				'esc_html',
				'esc_textarea',
				'__',
				'_x',
				'esc_html__',
				'esc_html_x',
				'esc_attr_x',
				'wp_unslash',
				'wp_check_invalid_utf8',
				'wp_delete_file'
			]
		);

		// Given functions can have a custom callback.
		Functions\stubs(
			[
				'wp_json_encode' => function ($data) {
					return json_encode($data);
				},
				'rest_ensure_response' => function ($response) {
					if (is_wp_error($response)) {
						return $response;
					}

					if ($response instanceof \WP_REST_Response) {
						return $response;
					}

					return new \WP_REST_Response($response);
				},
				'wp_safe_redirect' => function ($data) {
					do_action(self::WP_REDIRECT_ACTION, $this);
				},
				'wp_mail' => function ($to, $subject, $message, $headers = [], $attachments = []) {
					if (!empty($to) && !empty($subject) && !empty($message)) {
						do_action(self::WP_MAIL_ACTION, $this);
						return true;
					}

					return false;
				},
				'home_url' => function () {
					return self::HOME_URL;
				},
				'add_query_arg' => function ($data) {
					return $data;
				},
				'wp_verify_nonce' => function () {
					return true;
				},
				'is_email' => function (string $email) {
					return $email !== 'invalid';
				},
				'wp_pre_kses_less_than' => function ($text) {
					return preg_replace_callback(
						'%<[^>]*?((?=<)|>|$)%',
						function ($matches) {
							if (false === strpos($matches[0], '>')) {
								return esc_html($matches[0]);
							}
							return $matches[0];
						},
						$text
					);
				},
				'wp_strip_all_tags' => function ($string, $remove_breaks = false) {
					$string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);
					$string = strip_tags($string);

					if ($remove_breaks) {
						$string = preg_replace('/[\r\n\t ]+/', ' ', $string);
					}

					return trim($string);
				},
				'sanitize_text_field' => function ($str, $keep_newlines = false) {
					if (is_object($str) || is_array($str)) {
						return '';
					}

					$str = (string)$str;

					$filtered = wp_check_invalid_utf8($str);

					if (strpos($filtered, '<') !== false) {
						$filtered = wp_pre_kses_less_than($filtered);
						// This will strip extra whitespace for us.
						$filtered = wp_strip_all_tags($filtered, false);

						// Use HTML entities in a special case to make sure no later
						// newline stripping stage could lead to a functional tag.
						$filtered = str_replace("<\n", "&lt;\n", $filtered);
					}

					if (!$keep_newlines) {
						$filtered = preg_replace('/[\r\n\t ]+/', ' ', $filtered);
					}
					$filtered = trim($filtered);

					$found = false;
					while (preg_match('/%[a-f0-9]{2}/i', $filtered, $match)) {
						$filtered = str_replace($match[0], '', $filtered);
						$found = true;
					}

					if ($found) {
						// Strip out the whitespace that may now exist after removing the octets.
						$filtered = trim(preg_replace('/ +/', ' ', $filtered));
					}

					return $filtered;
				},
				'wp_handle_upload' => function() {
					return [
						'file' => 'test',
						'url' => 'test',
						'type' => 'application/pdf',
					];
				},
				'wp_remote_retrieve_body' => function($response) {
					if ( is_wp_error( $response ) || ! isset( $response['body'] ) ) {
						return '';
					}
			
					return $response['body'];
				}
			]
		);
	}

	protected function _after()
	{
		Monkey\tearDown();
	}
}
