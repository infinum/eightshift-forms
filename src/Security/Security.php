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
	public const RATE_LIMIT = 20;

	/**
	 * Time window in seconds
	 *
	 * @var int
	 */
	public const RATE_LIMIT_WINDOW = 60;

	/**
	 * Detect if the request is valid using rate limiting.
	 *
	 * @param string $formType Form type.
	 *
	 * @return boolean
	 */
	public function isRequestValid(string $formType): bool
	{
		// Bailout if this feature is not enabled.
		if (!\apply_filters(SettingsSecurity::FILTER_SETTINGS_IS_VALID_NAME, [])) {
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

		$ip = $this->getIpAddress('hash');

		// If this is the first iteration of this user just add it to the list.
		if (!isset($data[$ip])) {
			$data[$ip] = [
				'count' => 1,
				'time' => $time,
			];

			\update_option($keyName, $data); // No need for unserialize because we are storing array.
			return true;
		}

		// Extract user's data.
		$user = $data[$ip];
		$timestamp = $user['time'] ?? '';
		$count = $user['count'] ?? 0;

		// Reset the count if the time window has passed.
		if (($time - $timestamp) > \intval(SettingsHelpers::getOptionValueWithFallback(SettingsSecurity::SETTINGS_SECURITY_RATE_LIMIT_WINDOW_KEY, (string) self::RATE_LIMIT_WINDOW))) {
			unset($data[$ip]);
			\update_option($keyName, $data);
			return true;
		}

		// Check if the request count exceeds the rate limit.
		$rateLimitGeneral = SettingsHelpers::getOptionValueWithFallback(SettingsSecurity::SETTINGS_SECURITY_RATE_LIMIT_KEY, (string) self::RATE_LIMIT);
		$rateLimitCalculator = SettingsHelpers::getOptionValue(SettingsSecurity::SETTINGS_SECURITY_RATE_LIMIT_CALCULATOR_KEY);

		// Different rate limit for calculator.
		if ($rateLimitCalculator && $formType === SettingsCalculator::SETTINGS_TYPE_KEY) {
			$rateLimitGeneral = $rateLimitCalculator;
		}

		if ($count >= \intval($rateLimitGeneral)) {
			return false;
		}

		// Finally update the count and time.
		$data[$ip] = [
			'count' => $count + 1,
			'time' => $time,
		];

		\update_option($keyName, $data);
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
			$ip = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? \sanitize_text_field(\wp_unslash($_SERVER['HTTP_CF_CONNECTING_IP'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if (SettingsHelpers::isOptionCheckboxChecked(SettingsCloudFront::SETTINGS_CLOUDFRONT_USE_KEY, SettingsCloudFront::SETTINGS_CLOUDFRONT_USE_KEY)) {
			$ip = isset($_SERVER['CloudFront-Viewer-Address']) ? \sanitize_text_field(\wp_unslash($_SERVER['CloudFront-Viewer-Address'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ($ip) {
				$ip = \explode(':', $ip)[0] ?? '';
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
