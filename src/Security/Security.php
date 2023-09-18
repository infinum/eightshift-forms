<?php

/**
 * Security class.
 *
 * @package EightshiftForms\Security
 */

declare(strict_types=1);

namespace EightshiftForms\Security;

use EightshiftForms\Misc\SettingsCloudflare;
use EightshiftForms\Settings\SettingsHelper;

/**
 * Security class.
 */
class Security implements SecurityInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

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
		$keyName = $this->getSettingsName($key);
		$data = $this->getOptionValueGroup($key);
		$ip = $this->getIpAddress(true);
		$time = \time();

		// If this is the first iteration of this user just add it to the list.
		if (!isset($data[$ip])) {
			$data[$ip] = [
				'count' => 1,
				'time' => $time,
			];

			\update_option($keyName, $data);
			return true;
		}

		// Extract user's data.
		$user = $data[$ip];
		$timestamp = $user['time'] ?? '';
		$count = $user['count'] ?? 0;

		// Reset the count if the time window has passed.
		if (($time - $timestamp) > \intval($this->getOptionValueWithFallback(SettingsSecurity::SETTINGS_SECURITY_RATE_LIMIT_WINDOW_KEY, (string) self::RATE_LIMIT_WINDOW))) {
			unset($data[$ip]);
			\update_option($keyName, $data);
			return true;
		}

		// Check if the request count exceeds the rate limit.
		if ($count >= \intval($this->getOptionValueWithFallback(SettingsSecurity::SETTINGS_SECURITY_RATE_LIMIT_KEY, (string) self::RATE_LIMIT))) {
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

		if ($this->isCheckboxOptionChecked(SettingsCloudflare::SETTINGS_CLOUDFLARE_USE_KEY, SettingsCloudflare::SETTINGS_CLOUDFLARE_USE_KEY)) {
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
