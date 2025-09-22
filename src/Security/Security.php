<?php

/**
 * Security class.
 *
 * @package EightshiftForms\Security
 */

declare(strict_types=1);

namespace EightshiftForms\Security;

use EightshiftForms\Integrations\Calculator\SettingsCalculator;
use EightshiftForms\Misc\SettingsCloudflare;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Misc\SettingsCloudFront;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

/**
 * Security class.
 */
class Security implements SecurityInterface
{
	/**
	 * Requests allowed per minute
	 *
	 * @var int
	 */
	public const int RATE_LIMIT = 20;

	/**
	 * Time window in seconds
	 *
	 * @var int
	 */
	public const int RATE_LIMIT_WINDOW = 60;

	/**
	 * A settings key for granular rate limiting on different forms.
	 */
	public const string RATE_LIMIT_SETTING_NAME = 'granular-rate-limit';

	/**
	 * Detect if the request is valid using rate limiting.
	 *
	 * @param string $formType Form type.
	 * @param int $formId Form ID.
	 *
	 * @return boolean
	 */
	public function isRequestValid(string $formType, int $formId): bool
	{
		// Bailout if this feature is not enabled.
		if (!\apply_filters(SettingsSecurity::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			return true;
		}

		$keyName = SettingsHelpers::getOptionName(SettingsSecurity::SETTINGS_SECURITY_DATA_KEY);
		$data = SettingsHelpers::getOptionValueGroup(SettingsSecurity::SETTINGS_SECURITY_DATA_KEY);
		$time = \time();

		// Bailout if the IP is in the ignore list.
		$ignoreIps = Helpers::flattenArray(SettingsHelpers::getOptionValueGroup(SettingsSecurity::SETTINGS_SECURITY_IP_IGNORE_KEY));

		if (isset(\array_flip($ignoreIps)[$this->getIpAddress()])) {
			return true;
		}

		$userToken = $this->getIpAddress('hash');
		$activityType = "submit-$formType";


		$rateLimitingEntry = new RateLimitingLogEntry(
			userToken: $userToken,
			activityType: $activityType,
			formId: $formId,
			createdAt: $time,
		);

		$rateLimitingEntry->write();

		$window = \intval(SettingsHelpers::getOptionValueWithFallback(SettingsSecurity::SETTINGS_SECURITY_RATE_LIMIT_WINDOW_KEY, (string) self::RATE_LIMIT_WINDOW));


		// Check if the request count exceeds the rate limit.
		$rateLimit = SettingsHelpers::getOptionValueWithFallback(SettingsSecurity::SETTINGS_SECURITY_RATE_LIMIT_KEY, (string) self::RATE_LIMIT);
		$rateLimitCalculator = SettingsHelpers::getOptionValue(SettingsSecurity::SETTINGS_SECURITY_RATE_LIMIT_CALCULATOR_KEY);

		$calculatorTypeKey = SettingsCalculator::SETTINGS_TYPE_KEY;

		$rateLimitForActivityType = match ($activityType) {
			"submit-{$calculatorTypeKey}" => $rateLimitCalculator,
			default => $rateLimit,
		};

		$aggregatedActivityByType = RateLimitingLogEntry::findAggregatedByActivityType($userToken, $window);

		$sum = 0;
		foreach ($aggregatedActivityByType as $aggregate) {
			$sum += $aggregate['count'];

			if ($aggregate['activity_type'] === $activityType && $aggregate['count'] > $rateLimitForActivityType) {
					return false;
			}
		}

		if ($sum > $rateLimit) {
			return false;
		}

		$granularRateLimit = \intval(SettingsHelpers::getSettingValue(Security::RATE_LIMIT_SETTING_NAME, (string)$formId));

		if ($granularRateLimit <= 0) {
			return true;
		}

		$activityCountByFormId = RateLimitingLogEntry::countByFormId($userToken, $formId, $window);

		if ($activityCountByFormId > $granularRateLimit) {
			return false;
		}
		return true;
	}

	/**
	 * Get users Ip address.
	 *
	 * @param string $secureType Determine if the function will return normal, hashed or anonymized IP.
	 *
	 * @return string
	 */
	public function getIpAddress(string $secureType = 'none'): string
	{
		$ip = isset($_SERVER['REMOTE_ADDR']) ? \sanitize_text_field(\wp_unslash($_SERVER['REMOTE_ADDR'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if (SettingsHelpers::isOptionCheckboxChecked(SettingsCloudflare::SETTINGS_CLOUDFLARE_USE_KEY, SettingsCloudflare::SETTINGS_CLOUDFLARE_USE_KEY)) {
			$ipCloudflare = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? \sanitize_text_field(\wp_unslash($_SERVER['HTTP_CF_CONNECTING_IP'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ($ipCloudflare) {
				$ip = $ipCloudflare;
			}
		}

		if (SettingsHelpers::isOptionCheckboxChecked(SettingsCloudFront::SETTINGS_CLOUDFRONT_USE_KEY, SettingsCloudFront::SETTINGS_CLOUDFRONT_USE_KEY)) {
			$ipCloudFront = isset($_SERVER['HTTP_CLOUDFRONT_VIEWER_ADDRESS']) ? \sanitize_text_field(\wp_unslash($_SERVER['HTTP_CLOUDFRONT_VIEWER_ADDRESS'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ($ipCloudFront) {
				$ip = \explode(':', $ipCloudFront)[0] ?? '';
			}
		}

		if (!$ip) {
			return '';
		}

		switch ($secureType) {
			case 'hash':
				return \md5($ip);
			case 'anonymize':
				if (\filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
					$output = \explode('.', $ip);
					if ($output) {
						$output[\array_key_last($output)] = 'xxx';
						return \implode('.', $output);
					}
				}

				if (\filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)) {
					$output = \explode(':', $ip);
					if ($output) {
						$output[\end($output)] = 'xxx';
						return \implode(':', $output);
					}
				}

				return 'xxx.xxx.xxx.xxx';
			default:
				return $ip;
		}
	}
}
