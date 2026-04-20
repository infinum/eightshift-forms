<?php

/**
 * Class that holds Friendly Captcha check.
 *
 * @package EightshiftForms\Captcha
 */

declare(strict_types=1);

namespace EightshiftForms\Captcha;

use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Troubleshooting\SettingsFallback;
use WP_Error;

/**
 * FriendlyCaptcha class.
 */
class FriendlyCaptcha implements CaptchaInterface
{
	/**
	 * Labels service.
	 *
	 * @var LabelsInterface
	 */
	private $labels;

	/**
	 * Constructor.
	 *
	 * @param LabelsInterface $labels Labels service.
	 */
	public function __construct(LabelsInterface $labels)
	{
		$this->labels = $labels;
	}

	/**
	 * Check captcha request.
	 *
	 * Friendly Captcha does not use `$action` or `$isEnterprise` — they are
	 * accepted only for interface compatibility with Google reCAPTCHA.
	 *
	 * @param string $token Token from frontend.
	 * @param string $action Action to check (unused).
	 * @param boolean $isEnterprise Type of captcha (unused).
	 * @param array<string, mixed> $formDetails Form details.
	 *
	 * @return array<mixed>
	 */
	public function check(string $token, string $action, bool $isEnterprise, array $formDetails = []): array
	{
		if (!\apply_filters(SettingsFriendlyCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			return $this->buildSuccess(SettingsFallback::SETTINGS_FALLBACK_FLAG_FRIENDLY_CAPTCHA_FEATURE_DISABLED, []);
		}

		$debug = [
			'token' => $token,
			'formDetails' => $formDetails,
		];

		if (!$token) {
			$this->throwError(
				$this->labels->getLabel('friendlyCaptchaBadRequest'),
				SettingsFallback::SETTINGS_FALLBACK_FLAG_FRIENDLY_CAPTCHA_REQUEST_MISSING_TOKEN,
				$debug
			);
		}

		$response = $this->remoteCall($token);

		if (\is_wp_error($response)) {
			$this->throwError(
				$this->labels->getLabel('submitWpError'),
				SettingsFallback::SETTINGS_FALLBACK_FLAG_FRIENDLY_CAPTCHA_REQUEST_WP_ERROR,
				$debug
			);
		}

		$body = \json_decode(\wp_remote_retrieve_body($response), true) ?? [];

		$debug['responseBody'] = $body;

		if (empty($body['success'])) {
			$this->throwError(
				$this->labels->getLabel('friendlyCaptchaError'),
				SettingsFallback::SETTINGS_FALLBACK_FLAG_FRIENDLY_CAPTCHA_OUTPUT_ERROR,
				$debug
			);
		}

		return $this->buildSuccess(SettingsFallback::SETTINGS_FALLBACK_FLAG_FRIENDLY_CAPTCHA_SUCCESS, $debug);
	}

	/**
	 * Make the remote verification call to Friendly Captcha.
	 *
	 * @param string $token Verification token from the frontend widget.
	 *
	 * @return array<mixed>|WP_Error
	 */
	private function remoteCall(string $token): array|WP_Error
	{
		$siteKey = SettingsHelpers::getOptionWithConstant(Variables::getFriendlyCaptchaSiteKey(), SettingsFriendlyCaptcha::SETTINGS_FRIENDLY_CAPTCHA_SITE_KEY);
		$apiKey = SettingsHelpers::getOptionWithConstant(Variables::getFriendlyCaptchaApiKey(), SettingsFriendlyCaptcha::SETTINGS_FRIENDLY_CAPTCHA_API_KEY);

		return \wp_remote_post(
			SettingsFriendlyCaptcha::getEndpointUrl(),
			[
				'headers' => [
					'Content-Type' => 'application/json; charset=utf-8',
					'X-API-Key' => $apiKey,
				],
				'data_format' => 'body',
				'body' => \wp_json_encode([
					'response' => $token,
					'sitekey' => $siteKey,
				]),
			]
		);
	}

	/**
	 * Build the success response envelope.
	 *
	 * @param string $flag Fallback flag constant.
	 * @param array<mixed> $debug Debug payload.
	 * @param array<string, mixed> $data Extra R_DATA payload.
	 *
	 * @return array<mixed>
	 */
	private function buildSuccess(string $flag, array $debug, array $data = []): array
	{
		$output = [
			AbstractBaseRoute::R_MSG => $this->labels->getLabel('friendlyCaptchaSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG_KEY => $flag,
				AbstractBaseRoute::R_DEBUG => $debug,
			],
		];

		if ($data) {
			$output[AbstractBaseRoute::R_DATA] = $data;
		}

		return $output;
	}

	/**
	 * Throw a BadRequestException with the project's debug envelope.
	 *
	 * @param string $message Localized error message.
	 * @param string $flag Fallback flag constant.
	 * @param array<mixed> $debug Debug payload.
	 * @param array<string, mixed> $extraData Optional extra data.
	 *
	 * @throws BadRequestException Always.
	 *
	 * @return void
	 */
	private function throwError(string $message, string $flag, array $debug, array $extraData = []): void
	{
		// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
		throw new BadRequestException(
			$message,
			[
				AbstractBaseRoute::R_DEBUG_KEY => $flag,
				AbstractBaseRoute::R_DEBUG => $debug,
			],
			$extraData
		);
		// phpcs:enable
	}
}
