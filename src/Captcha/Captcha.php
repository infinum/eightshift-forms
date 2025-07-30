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
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use WP_Error;

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
		if (!\apply_filters(SettingsCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			return [
				AbstractBaseRoute::R_MSG => $this->labels->getLabel('captchaSuccess'),
				AbstractBaseRoute::R_DEBUG => [
					AbstractBaseRoute::R_DEBUG_KEY => 'captchaFeatureDisabled',
				],
			];
		}

		$debug = [
			'token' => $token,
			'action' => $action,
			'isEnterprise' => $isEnterprise,
		];

		if (!$token) {
			throw new BadRequestException(
				$this->labels->getLabel('captchaBadRequest'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => 'captchaRequestMissingToken',
					AbstractBaseRoute::R_DEBUG => $debug,
				]
			);
		}

		if ($isEnterprise) {
			$response = $this->onEnterprise($token, $action);
		} else {
			$response = $this->onFree($token);
		}

		// Generic error msg from WP.
		if (\is_wp_error($response)) {
			throw new BadRequestException(
				$this->labels->getLabel('submitWpError'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => 'captchaRequestWpError',
					AbstractBaseRoute::R_DEBUG => $debug,
				]
			);
		}

		// Get body from the response.
		$responseBody = \json_decode(\wp_remote_retrieve_body($response), true);

		if ($isEnterprise) {
			return $this->getEnterpriseOutput($responseBody, $action, $debug);
		}

		return $this->getFreeOutput($responseBody, $action, $debug);
	}

	/**
	 * Get Enterprise response from api.
	 *
	 * @param string $token Token for captcha.
	 * @param string $action Action name.
	 *
	 * @return array<mixed>|WP_Error
	 */
	private function onEnterprise(string $token, string $action): array|WP_Error
	{
		$siteKey = SettingsHelpers::getOptionWithConstant(Variables::getGoogleReCaptchaSiteKey(), SettingsCaptcha::SETTINGS_CAPTCHA_SITE_KEY);
		$apiKey = SettingsHelpers::getOptionWithConstant(Variables::getGoogleReCaptchaApiKey(), SettingsCaptcha::SETTINGS_CAPTCHA_API_KEY);
		$projectIdKey = SettingsHelpers::getOptionWithConstant(Variables::getGoogleReCaptchaProjectIdKey(), SettingsCaptcha::SETTINGS_CAPTCHA_PROJECT_ID_KEY);

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
	 * @return array<mixed>|WP_Error
	 */
	private function onFree(string $token): array|WP_Error
	{
		$secretKey = SettingsHelpers::getOptionWithConstant(Variables::getGoogleReCaptchaSecretKey(), SettingsCaptcha::SETTINGS_CAPTCHA_SECRET_KEY);

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
	 * @param array<mixed> $responseBody Response body from API.
	 * @param string $action Action name.
	 * @param array<mixed> $debug Debug data.
	 *
	 * @return mixed
	 */
	private function getEnterpriseOutput(array $responseBody, string $action, array $debug)
	{
		$debug = \array_merge($debug, [
			'responseBody' => $responseBody,
			'action' => $action,
		]);

		// Check the status.
		$error = $responseBody['error'] ?? [];

		// If error status returns error.
		if ($error) {
			throw new BadRequestException(
				$this->labels->getLabel('captchaError'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => 'captchaEnterpriseOutputError',
					AbstractBaseRoute::R_DEBUG => $debug,
				]
			);
		}

		return $this->validate(
			$responseBody,
			$action,
			$responseBody['tokenProperties']['action'] ?? '',
			$responseBody['riskAnalysis']['score'] ?? 0.0,
			$debug
		);
	}

	/**
	 * Get free output.
	 *
	 * @param array<mixed> $responseBody Response body from API.
	 * @param string $action Action name.
	 * @param array<mixed> $debug Debug data.
	 *
	 * @return mixed
	 */
	private function getFreeOutput(array $responseBody, string $action, array $debug)
	{
		$debug = \array_merge($debug, [
			'responseBody' => $responseBody,
			'action' => $action,
		]);

		// Check the status.
		$success = $responseBody['success'] ?? false;

		// If error status returns error.
		if (!$success) {
			throw new BadRequestException(
				$this->labels->getLabel('captchaError'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => 'captchaFreeOutputError',
					AbstractBaseRoute::R_DEBUG => $debug,
				]
			);
		}

		return $this->validate(
			$responseBody,
			$action,
			$responseBody['action'] ?? '',
			$responseBody['score'] ?? 0.0,
			$debug
		);
	}

	/**
	 * Validate and return if issue.
	 *
	 * @param mixed $responseBody Response body from API.
	 * @param string $action Action name.
	 * @param string $actionResponse Action response from API.
	 * @param float $score Score value Score value from API.
	 * @param array<mixed> $debug Debug data.
	 *
	 * @return mixed
	 */
	private function validate(
		$responseBody,
		string $action,
		string $actionResponse,
		float $score,
		array $debug
	) {
		$debug = \array_merge($debug, [
			'responseBody' => $responseBody,
			'action' => $action,
			'actionResponse' => $actionResponse,
			'score' => $score,
		]);

		// Bailout if action is not correct.
		if ($actionResponse !== $action) {
			throw new BadRequestException(
				$this->labels->getLabel('captchaWrongAction'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => 'captchaWrongAction',
					AbstractBaseRoute::R_DEBUG => $debug,
				]
			);
		}

		$setScore = SettingsHelpers::getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_SCORE_KEY) ?: SettingsCaptcha::SETTINGS_CAPTCHA_SCORE_DEFAULT_KEY; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Bailout on spam.
		if (\floatval($score) < \floatval($setScore)) {
			throw new BadRequestException(
				$this->labels->getLabel('captchaScoreSpam'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => 'captchaScoreSpam',
					AbstractBaseRoute::R_DEBUG => $debug,
				],
				[
					UtilsHelper::getStateResponseOutputKey('captchaIsSpam') => true,
					UtilsHelper::getStateResponseOutputKey('captchaResponse') => $responseBody,
				]
			);
		}

		return [
			AbstractBaseRoute::R_MSG => $this->labels->getLabel('captchaSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG_KEY => 'captchaSuccess',
				AbstractBaseRoute::R_DEBUG => $debug,
			],
			AbstractBaseRoute::R_DATA => [
				UtilsHelper::getStateResponseOutputKey('captchaIsSpam') => false,
				UtilsHelper::getStateResponseOutputKey('captchaResponse') => $responseBody,
			],
		];
	}
}
