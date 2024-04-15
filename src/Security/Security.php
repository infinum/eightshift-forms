<?php

/**
 * Security class.
 *
 * @package EightshiftForms\Security
 */

declare(strict_types=1);

namespace EightshiftForms\Security;

use EightshiftForms\Misc\SettingsCloudflare;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

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
	 * @return boolean
	 */
	public function isRequestValid(): bool
	{
		// Bailout if this feature is not enabled.
		if (!\apply_filters(SettingsSecurity::FILTER_SETTINGS_IS_VALID_NAME, [])) {
			return true;
		}

		$key = SettingsSecurity::SETTINGS_SECURITY_DATA_KEY;
		$keyName = UtilsSettingsHelper::getOptionName($key);
		$data = UtilsSettingsHelper::getOptionValueGroup($key);
		$ip = $this->getIpAddress();
		$time = \time();

		// Bailout if the IP is in the ignore list.
		$ignoreIps = Components::flattenArray(UtilsSettingsHelper::getOptionValueGroup(SettingsSecurity::SETTINGS_SECURITY_IP_IGNORE_KEY));

		if (isset(\array_flip($ignoreIps)[$ip])) {
			return true;
		}

		// Hash the IP for anonymization.
		$ip = \md5($ip);

		// If this is the first iteration of this user just add it to the list.
		if (!isset($data[$ip])) {
			$data[$ip] = [
				'count' => 1,
				'time' => $time,
			];

			\update_option($keyName, $data); // No need for unserilize because we are storing array.
			return true;
		}

		// Extract user's data.
		$user = $data[$ip];
		$timestamp = $user['time'] ?? '';
		$count = $user['count'] ?? 0;

		// Reset the count if the time window has passed.
		if (($time - $timestamp) > \intval(UtilsSettingsHelper::getOptionValueWithFallback(SettingsSecurity::SETTINGS_SECURITY_RATE_LIMIT_WINDOW_KEY, (string) self::RATE_LIMIT_WINDOW))) {
			unset($data[$ip]);
			\update_option($keyName, $data);
			return true;
		}

		// Check if the request count exceeds the rate limit.
		if ($count >= \intval(UtilsSettingsHelper::getOptionValueWithFallback(SettingsSecurity::SETTINGS_SECURITY_RATE_LIMIT_KEY, (string) self::RATE_LIMIT))) {
			return false;
		}

		// Finaly update the count and time.
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
	 * @param bool $secure Determine if the function will return normal IP or hashed IP.
	 *
	 * @return string
	 */
	public function getIpAddress(bool $secure = false): string
	{
		$ip = isset($_SERVER['REMOTE_ADDR']) ? \sanitize_text_field(\wp_unslash($_SERVER['REMOTE_ADDR'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if (UtilsSettingsHelper::isOptionCheckboxChecked(SettingsCloudflare::SETTINGS_CLOUDFLARE_USE_KEY, SettingsCloudflare::SETTINGS_CLOUDFLARE_USE_KEY)) {
			$ip = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? \sanitize_text_field(\wp_unslash($_SERVER['HTTP_CF_CONNECTING_IP'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if (!$ip) {
			return '';
		}

		if ($secure) {
			return \md5($ip);
		}

		return $ip;
	}
}
