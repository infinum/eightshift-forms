<?php

/**
 * The class register route for public form submiting endpoint - Captcha
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Helpers\Components;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Validation\SettingsCaptcha;

/**
 * Class FormSubmitCaptchaRoute
 */
class FormSubmitCaptchaRoute extends AbstractBaseRoute
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Instance variable of LabelsInterface data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param LabelsInterface $labels Inject LabelsInterface which holds labels data.
	 */
	public function __construct(LabelsInterface $labels)
	{
		$this->labels = $labels;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/form-submit-captcha';
	}

	/**
	 * Get callback arguments array
	 *
	 * @return array<string, mixed> Either an array of options for the endpoint, or an array of arrays for multiple methods.
	 */
	protected function getCallbackArguments(): array
	{
		return [
			'methods' => $this->getMethods(),
			'callback' => [$this, 'routeCallback'],
			'permission_callback' => [$this, 'permissionCallback'],
		];
	}

	/**
	 * Method that returns rest response
	 *
	 * @param \WP_REST_Request $request Data got from endpoint url.
	 *
	 * @return \WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function routeCallback(\WP_REST_Request $request)
	{
		try {
			$params = json_decode($request->get_body(), true, 512, JSON_THROW_ON_ERROR); // phpcs:ignore
		} catch (\Throwable $t) {
			return \rest_ensure_response([
				'status' => 'error',
				'code' => 400,
				'message' => $this->labels->getLabel('captchaBadRequest'),
			]);
		}

		$token = $params['token'] ?? '';
		$formId = $params['formId'] ?? '';

		if (!$token || !$formId) {
			return \rest_ensure_response([
				'status' => 'error',
				'code' => 400,
				'message' => $this->labels->getLabel('captchaBadRequest', $formId),
			]);
		}

		$secretKey = !empty(Variables::getGoogleReCaptchaSecretKey()) ? Variables::getGoogleReCaptchaSecretKey() : $this->getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_SECRET_KEY);

		$response = \wp_remote_post(
			"https://www.google.com/recaptcha/api/siteverify",
			[
				'body' => [
					'secret' => $secretKey,
					'response' => $token,
				],
			]
		);

		// Generic error msg from WP.
		if (is_wp_error($response)) {
			return [
				'status' => 'error',
				'code' => 400,
				'message' => $this->labels->getLabel('submitWpError', $formId),
			];
		}

		// Get body from the response.
		try {
			$responseBody = json_decode(\wp_remote_retrieve_body($response), true);
		} catch (\Throwable $t) {
			return \rest_ensure_response([
				'status' => 'error',
				'code' => 400,
				'message' => $this->labels->getLabel('captchaBadRequest'),
			]);
		}

		// Check the status.
		$success = $responseBody['success'] ?? false;

		// If error status returns error.
		if (!$success) {
			// Find error codes to use in msg response.
			$errorCode = $responseBody['error-codes'][0] ?? '';

			// Bailout on error.
			return \rest_ensure_response([
				'status' => 'error',
				'code' => 400,
				'message' => $this->labels->getLabel("captcha" . ucfirst(Components::kebabToCamelCase($errorCode)), $formId),
				'validation' => $responseBody,
			]);
		}

		// Check the action.
		$action = $responseBody['action'] ?? '';

		// Bailout if action is not correct.
		if ($action !== 'submit') {
			return \rest_ensure_response([
				'status' => 'error',
				'code' => 400,
				'message' => $this->labels->getLabel('captchaWrongAction', $formId),
				'validation' => $responseBody,
			]);
		}

		$score = $responseBody['score'] ?? 0.0;
		$setScore = $this->getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_SCORE_KEY) ?: SettingsCaptcha::SETTINGS_CAPTCHA_SCORE_DEFAULT_KEY; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Bailout on spam.
		if (floatval($score) < floatval($setScore)) {
			return \rest_ensure_response([
				'status' => 'error',
				'code' => 400,
				'message' => $this->labels->getLabel('captchaScoreSpam', $formId),
				'validation' => $responseBody,
			]);
		}

		return \rest_ensure_response([
			'status' => 'success',
			'code' => 200,
			'message' => '',
			'validation' => $responseBody,
		]);
	}
}
