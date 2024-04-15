<?php

/**
 * Class that holds Captcha check.
 *
 * @package EightshiftForms\Captcha
 */

declare(strict_types=1);

namespace EightshiftForms\Captcha;

use EightshiftForms\Hooks\Variables;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use Throwable;

/**
 * Captcha class.
 */
class Captcha implements CaptchaInterface
{
	/**
	 * Instance variable of LabelsInterface data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param LabelsInterface $labels Inject labels methods.
	 */
	public function __construct(LabelsInterface $labels)
	{
		$this->labels = $labels;
	}

	/**
	 * Check captcha request.
	 *
	 * @param string $token Token from frontend.
	 * @param string $action Action to check.
	 * @param boolean $isEnterprise Type of captcha.
	 *
	 * @return array<mixed>
	 */
	public function check(string $token, string $action, bool $isEnterprise): array
	{
		$debug = [
			'token' => $token,
			'action' => $action,
			'isEnterprise' => $isEnterprise,
		];

		if (!$token) {
			return UtilsApiHelper::getApiErrorPublicOutput(
				$this->labels->getLabel('captchaBadRequest'),
				[],
				$debug
			);
		}

		if ($isEnterprise) {
			$response = $this->onEnterprise($token, $action);
		} else {
			$response = $this->onFree($token);
		}

		// Generic error msg from WP.
		if (\is_wp_error($response)) {
			return UtilsApiHelper::getApiErrorPublicOutput(
				$this->labels->getLabel('submitWpError'),
				[],
				$debug
			);
		}

		// Get body from the response.
		try {
			$responseBody = \json_decode(\wp_remote_retrieve_body($response), true);
		} catch (Throwable $t) {
			return UtilsApiHelper::getApiErrorPublicOutput(
				$this->labels->getLabel('captchaBadRequest'),
				[],
				$debug
			);
		}

		if ($isEnterprise) {
			return $this->getEnterpriseOutput($responseBody, $action);
		} else {
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
		$siteKey = UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getGoogleReCaptchaSiteKey(), SettingsCaptcha::SETTINGS_CAPTCHA_SITE_KEY)['value'];
		$apiKey = UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getGoogleReCaptchaApiKey(), SettingsCaptcha::SETTINGS_CAPTCHA_API_KEY)['value'];
		$projectIdKey = UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getGoogleReCaptchaProjectIdKey(), SettingsCaptcha::SETTINGS_CAPTCHA_PROJECT_ID_KEY)['value'];

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
		$secretKey = UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getGoogleReCaptchaSecretKey(), SettingsCaptcha::SETTINGS_CAPTCHA_SECRET_KEY)['value'];

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
		$debug = [
			'responseBody' => $responseBody,
			'action' => $action,
		];

		// Check the status.
		$error = $responseBody['error'] ?? [];

		// If error status returns error.
		if ($error) {
			// Bailout on error.
			return UtilsApiHelper::getApiErrorPublicOutput(
				$error['message'] ?? '',
				[
					'response' => $responseBody,
				],
				$debug
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
		$debug = [
			'responseBody' => $responseBody,
			'action' => $action,
		];

		// Check the status.
		$success = $responseBody['success'] ?? false;

		// If error status returns error.
		if (!$success) {
			// Bailout on error.
			return UtilsApiHelper::getApiErrorPublicOutput(
				$this->labels->getLabel('captchaError'),
				[
					'response' => $responseBody,
				],
				$debug
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
		$debug = [
			'responseBody' => $responseBody,
			'action' => $action,
			'actionResponse' => $actionResponse,
			'score' => $score,
		];

		// Bailout if action is not correct.
		if ($actionResponse !== $action) {
			return UtilsApiHelper::getApiErrorPublicOutput(
				$this->labels->getLabel('captchaWrongAction'),
				[
					'response' => $responseBody,
				],
				$debug
			);
		}

		$setScore = UtilsSettingsHelper::getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_SCORE_KEY) ?: SettingsCaptcha::SETTINGS_CAPTCHA_SCORE_DEFAULT_KEY; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Bailout on spam.
		if (\floatval($score) < \floatval($setScore)) {
			return UtilsApiHelper::getApiErrorPublicOutput(
				$this->labels->getLabel('captchaScoreSpam'),
				[
					'response' => $responseBody,
				],
				$debug
			);
		}

		return UtilsApiHelper::getApiSuccessPublicOutput(
			'',
			[
				'response' => $responseBody,
			],
			$debug
		);
	}
}
