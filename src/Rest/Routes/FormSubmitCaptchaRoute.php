<?php

/**
 * The class register route for public form submiting endpoint - Captcha
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftForms\Validation\SettingsCaptcha;
use Throwable;
use WP_REST_Request;

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
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/' . AbstractBaseRoute::ROUTE_PREFIX_FORM_SUBMIT . '-captcha/';

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
		return self::ROUTE_SLUG;
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
	 * @param WP_REST_Request $request Data got from endpoint url.
	 *
	 * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function routeCallback(WP_REST_Request $request)
	{
		// Bailout if troubleshooting skip captcha is on.
		if ($this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_SKIP_CAPTCHA_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY)) {
			return \rest_ensure_response(
				$this->getApiSuccessOutput(
					\esc_html__('Form captcha skipped due to troubleshooting config set in settings.', 'eightshift-forms')
				)
			);
		}

		try {
			$params = \json_decode($request->get_body(), true, 512, JSON_THROW_ON_ERROR); // phpcs:ignore
		} catch (Throwable $t) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					$this->labels->getLabel('captchaBadRequest'),
				)
			);
		}

		$token = $params['token'] ?? '';
		$action = $params['action'] ?? '';

		if (!$token) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					$this->labels->getLabel('captchaBadRequest'),
				)
			);
		}

		switch ($params['payed'] ?? '') {
			case 'enterprise':
				$response = $this->onEnterprise($token, $action);
				break;
			case 'free':
				$response = $this->onFree($token);
				break;
			default:
				$response = [];
				break;
		}

		// Generic error msg from WP.
		if (\is_wp_error($response)) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					$this->labels->getLabel('submitWpError')
				)
			);
		}

		// Get body from the response.
		try {
			$responseBody = \json_decode(\wp_remote_retrieve_body($response), true);
		} catch (Throwable $t) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					$this->labels->getLabel('captchaBadRequest')
				)
			);
		}

		switch ($params['payed'] ?? '') {
			case 'enterprise':
				return $this->getEnterpriseOutput($responseBody, $action);
			case 'free':
				return $this->getFreeOutput($responseBody, $action);
		}
	}

	/**
	 * Get Enterprise response from api.
	 *
	 * @param string $token Token for captcha.
	 * @param string $action Action name.
	 *
	 * @return mixed
	 */
	private function onEnterprise(string $token, string $action)
	{
		$siteKey = !empty(Variables::getGoogleReCaptchaSiteKey()) ? Variables::getGoogleReCaptchaSiteKey() : $this->getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_SITE_KEY);
		$apiKey = !empty(Variables::getGoogleReCaptchaApiKey()) ? Variables::getGoogleReCaptchaApiKey() : $this->getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_API_KEY);
		$projectIdKey = !empty(Variables::getGoogleReCaptchaProjectIdKey()) ? Variables::getGoogleReCaptchaProjectIdKey() : $this->getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_PROJECT_ID_KEY);

		return \wp_remote_post(
			"https://recaptchaenterprise.googleapis.com/v1/projects/{$projectIdKey}/assessments?key={$apiKey}",
			[
				'headers' => [
					'Content-Type' => 'application/json; charset=utf-8'
				],
				'data_format' => 'body',
				'body' => \wp_json_encode([
					'event' => [
						'siteKey' => $siteKey,
						"token" => $token,
						"expectedAction" => $action
					]
				]),
			]
		);
	}

	/**
	 * Get Enterprise response from api.
	 *
	 * @param string $token Token for captcha.
	 *
	 * @return mixed
	 */
	private function onFree(string $token)
	{
		$secretKey = !empty(Variables::getGoogleReCaptchaSecretKey()) ? Variables::getGoogleReCaptchaSecretKey() : $this->getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_SECRET_KEY);

		return \wp_remote_post(
			"https://www.google.com/recaptcha/api/siteverify",
			[
				'body' => [
					'secret' => $secretKey,
					'response' => $token,
				],
			]
		);
	}

	/**
	 * Get enterprise output.
	 *
	 * @param mixed $responseBody Response body from API.
	 * @param string $action Action name.
	 *
	 * @return mixed
	 */
	private function getEnterpriseOutput($responseBody, string $action)
	{
		// Check the status.
		$error = $responseBody['error'] ?? [];

		// If error status returns error.
		if ($error) {
			// Bailout on error.
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					$error['message'] ?? '',
					[
						'response' => $responseBody,
					]
				)
			);
		}

		return $this->validate($responseBody, $action, $responseBody['tokenProperties']['action'] ?? '', $responseBody['riskAnalysis']['score'] ?? 0.0);
	}

	/**
	 * Get free output.
	 *
	 * @param mixed $responseBody Response body from API.
	 * @param string $action Action name.
	 *
	 * @return mixed
	 */
	private function getFreeOutput($responseBody, string $action)
	{
		// Check the status.
		$success = $responseBody['success'] ?? false;

		// If error status returns error.
		if (!$success) {
			// Find error codes to use in msg response.
			$errorCode = $responseBody['error-codes'][0] ?? '';

			// Bailout on error.
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					$this->labels->getLabel("captcha" . \ucfirst(Components::kebabToCamelCase($errorCode))),
					[
						'response' => $responseBody,
					]
				)
			);
		}

		return $this->validate($responseBody, $action, $responseBody['action'] ?? '', $responseBody['score'] ?? 0.0);
	}

	/**
	 * Validate and return if issue.
	 *
	 * @param mixed $responseBody Response body from API.
	 * @param string $action Action name.
	 * @param string $actionResponse Action response from API.
	 * @param float $score Score value Score value from API.
	 *
	 * @return mixed
	 */
	private function validate($responseBody, string $action, string $actionResponse, float $score)
	{
		// Bailout if action is not correct.
		if ($actionResponse !== $action) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					$this->labels->getLabel('captchaWrongAction'),
					[
						'response' => $responseBody,
					]
				)
			);
		}

		$setScore = $this->getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_SCORE_KEY) ?: SettingsCaptcha::SETTINGS_CAPTCHA_SCORE_DEFAULT_KEY; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Bailout on spam.
		if (\floatval($score) < \floatval($setScore)) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					$this->labels->getLabel('captchaScoreSpam'),
					[
						'response' => $responseBody,
					]
				)
			);
		}

		return \rest_ensure_response(
			$this->getApiSuccessOutput(
				'',
				[
					'response' => $responseBody,
				]
			)
		);
	}
}
