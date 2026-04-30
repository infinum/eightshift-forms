<?php

/**
 * Class that holds Friendly Captcha check.
 *
 * @package EightshiftForms\Captcha
 */

declare(strict_types=1);

namespace EightshiftForms\Captcha;

use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Troubleshooting\SettingsFallback;

/**
 * FriendlyCaptcha class.
 */
class FriendlyCaptcha implements CaptchaInterface
{
	/**
	 * Friendly Captcha API endpoint URLs.
	 */
	public const FRIENDLY_CAPTCHA_ENDPOINT_GLOBAL_URL = 'https://global.frcapi.com/api/v2/captcha/siteverify';
	public const FRIENDLY_CAPTCHA_ENDPOINT_EU_URL = 'https://eu.frcapi.com/api/v2/captcha/siteverify';

	/**
	 * Friendly Captcha siteverify error codes that indicate a missing or invalid API key.
	 */
	private const ERROR_CODES_AUTH = ['secret_missing', 'secret_invalid', 'auth_invalid', 'auth_required'];

	/**
	 * Friendly Captcha siteverify error codes that indicate a malformed request.
	 */
	private const ERROR_CODES_BAD_REQUEST = ['bad_request', 'solution_missing', 'sitekey_invalid', 'sitekey_missing'];

	/**
	 * Friendly Captcha siteverify error codes recoverable by a fresh widget solution.
	 */
	private const ERROR_CODES_TIMEOUT_OR_DUPLICATE = ['solution_timeout_or_duplicate', 'solution_expired', 'solution_already_used'];

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
	 * Friendly Captcha does not use `$action` or `$isEnterprise` — they are
	 * accepted only for interface compatibility with Google reCAPTCHA.
	 *
	 * @param string $token Token from frontend.
	 * @param string $action Action to check.
	 * @param boolean $isEnterprise Type of captcha.
	 * @param array<string, mixed> $formDetails Form details.
	 *
	 * @throws BadRequestException If captcha is not valid.
	 *
	 * @return array<mixed>
	 */
	public function check(string $token, string $action, bool $isEnterprise, array $formDetails = []): array
	{
		if (!\apply_filters(SettingsFriendlyCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			return [
				AbstractBaseRoute::R_MSG => $this->labels->getLabel('friendlyCaptchaSuccess'),
				AbstractBaseRoute::R_DEBUG => [
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_CAPTCHA_FEATURE_DISABLED,
				],
			];
		}

		$debug = [
			'token' => $token,
			'formDetails' => $formDetails,
		];

		if (!$token) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->labels->getLabel('friendlyCaptchaBadRequest'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_CAPTCHA_REQUEST_MISSING_TOKEN,
					AbstractBaseRoute::R_DEBUG => $debug,
				]
			);
			// phpcs:enable
		}

		$siteKey = SettingsHelpers::getOptionWithConstant(Variables::getFriendlyCaptchaSiteKey(), SettingsFriendlyCaptcha::SETTINGS_FRIENDLY_CAPTCHA_SITE_KEY);
		$apiKey = SettingsHelpers::getOptionWithConstant(Variables::getFriendlyCaptchaApiKey(), SettingsFriendlyCaptcha::SETTINGS_FRIENDLY_CAPTCHA_API_KEY);

		$response = \wp_remote_post(
			self::getEndpointUrl(),
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

		// Generic error msg from WP.
		if (\is_wp_error($response)) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->labels->getLabel('submitWpError'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_CAPTCHA_REQUEST_WP_ERROR,
					AbstractBaseRoute::R_DEBUG => $debug,
				]
			);
			// phpcs:enable
		}

		$responseCode = (int) \wp_remote_retrieve_response_code($response);
		$responseBody = \json_decode(\wp_remote_retrieve_body($response), true) ?? [];
		$errorCode = (string) ($responseBody['errors'][0]['error_code'] ?? '');

		$debug['responseCode'] = $responseCode;
		$debug['responseBody'] = $responseBody;
		$debug['errorCode'] = $errorCode;

		if (ApiHelpers::isSuccessResponse($responseCode)) {
			return [
				AbstractBaseRoute::R_MSG => $this->labels->getLabel('friendlyCaptchaSuccess'),
				AbstractBaseRoute::R_DEBUG => [
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_CAPTCHA_SUCCESS,
					AbstractBaseRoute::R_DEBUG => $debug,
				],
			];
		}

		// Auth issues — bad/missing API key.
		if (\in_array($errorCode, self::ERROR_CODES_AUTH, true)) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->labels->getLabel('friendlyCaptchaAuthError'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_FRIENDLY_CAPTCHA_AUTH_ERROR,
					AbstractBaseRoute::R_DEBUG => $debug,
				]
			);
			// phpcs:enable
		}

		// Malformed request rejected by Friendly Captcha (sitekey/payload issue).
		if (\in_array($errorCode, self::ERROR_CODES_BAD_REQUEST, true)) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->labels->getLabel('friendlyCaptchaBadRequest'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_FRIENDLY_CAPTCHA_BAD_REQUEST,
					AbstractBaseRoute::R_DEBUG => $debug,
				]
			);
			// phpcs:enable
		}

		// Solution expired or replayed — recoverable by requesting a fresh widget solution.
		if (\in_array($errorCode, self::ERROR_CODES_TIMEOUT_OR_DUPLICATE, true)) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->labels->getLabel('friendlyCaptchaTimeoutOrDuplicate'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_FRIENDLY_CAPTCHA_TIMEOUT_OR_DUPLICATE,
					AbstractBaseRoute::R_DEBUG => $debug,
				]
			);
			// phpcs:enable
		}

		// Solution failed validation — likely a bot or tampered token.
		if ($errorCode === 'solution_invalid') {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->labels->getLabel('friendlyCaptchaInvalidSolution'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_FRIENDLY_CAPTCHA_INVALID_SOLUTION,
					AbstractBaseRoute::R_DEBUG => $debug,
				]
			);
			// phpcs:enable
		}

		// Non-success HTTP status with no recognised error code (e.g. 5xx).
		if (ApiHelpers::isErrorResponse($responseCode)) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->labels->getLabel('friendlyCaptchaHttpError'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_FRIENDLY_CAPTCHA_HTTP_ERROR,
					AbstractBaseRoute::R_DEBUG => $debug,
				]
			);
			// phpcs:enable
		}

		// Generic catch-all for any other unsuccessful response.
		// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
		throw new BadRequestException(
			$this->labels->getLabel('friendlyCaptchaError'),
			[
				AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_FRIENDLY_CAPTCHA_OUTPUT_ERROR,
				AbstractBaseRoute::R_DEBUG => $debug,
			]
		);
		// phpcs:enable
	}

	/**
	 * Get the selected endpoint value.
	 *
	 * @return string
	 */
	public static function getEndpoint(): string
	{
		return SettingsHelpers::isOptionCheckboxChecked(SettingsFriendlyCaptcha::SETTINGS_FRIENDLY_CAPTCHA_USE_EU_ENDPOINT_KEY, SettingsFriendlyCaptcha::SETTINGS_FRIENDLY_CAPTCHA_USE_EU_ENDPOINT_KEY) ? 'eu' : 'global';
	}

	/**
	 * Get the siteverify URL for the selected endpoint.
	 *
	 * @return string
	 */
	public static function getEndpointUrl(): string
	{
		return self::getEndpoint() === 'eu' ? self::FRIENDLY_CAPTCHA_ENDPOINT_EU_URL : self::FRIENDLY_CAPTCHA_ENDPOINT_GLOBAL_URL;
	}
}
