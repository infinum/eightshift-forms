<?php

/**
 * Trait that holds all generic helpers.
 *
 * @package EightshiftLibs\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

use EightshiftForms\AdminMenus\FormGlobalSettingsAdminSubMenu;
use EightshiftForms\AdminMenus\FormSettingsAdminSubMenu;
use EightshiftForms\AdminMenus\FormListingAdminSubMenu;
use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\Settings\SettingsGeneral;

/**
 * Helper class.
 */
class Helper
{
	/**
	 * Encript/Decrypt method.
	 *
	 * @param string $action Action used.
	 * @param string $string String used.
	 *
	 * @return string|bool
	 */
	public static function encryptor(string $action, string $string)
	{
		$encryptMethod = "AES-256-CBC";
		$secretKey = wp_salt(); // user define private key.
		$secretIv = wp_salt('SECURE_AUTH_KEY'); // user define secret key.
		$key = hash('sha256', $secretKey);
		$iv = substr(hash('sha256', $secretIv), 0, 16); // sha256 is hash_hmac_algo.

		if ($action === 'encrypt') {
			$output = openssl_encrypt($string, $encryptMethod, $key, 0, $iv);

			return base64_encode((string) $output); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		}

		return openssl_decrypt(base64_decode($string), $encryptMethod, $key, 0, $iv); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	}

	/**
	 * Method that returns listing page url.
	 *
	 * @return string
	 */
	public static function getListingPageUrl(): string
	{
		$page = FormListingAdminSubMenu::ADMIN_MENU_SLUG;

		return "/wp-admin/admin.php?page={$page}";
	}

	/**
	 * Method that returns form settings page url.
	 *
	 * @param string $formId Form ID.
	 * @param string $type Type key.
	 *
	 * @return string
	 */
	public static function getSettingsPageUrl(string $formId, string $type = SettingsGeneral::SETTINGS_TYPE_KEY): string
	{
		$page = FormSettingsAdminSubMenu::ADMIN_MENU_SLUG;
		$typeKey = '';

		if (!empty($type)) {
			$typeKey = "&type={$type}";
		}

		return "/wp-admin/admin.php?page={$page}&formId={$formId}{$typeKey}";
	}

	/**
	 * Method that returns form settings global page url.
	 *
	 * @param string $type Type key.
	 *
	 * @return string
	 */
	public static function getSettingsGlobalPageUrl(string $type = SettingsGeneral::SETTINGS_TYPE_KEY): string
	{
		$page = FormGlobalSettingsAdminSubMenu::ADMIN_MENU_SLUG;
		$typeKey = '';

		if (!empty($type)) {
			$typeKey = "&type={$type}";
		}

		return "/wp-admin/admin.php?page={$page}{$typeKey}";
	}

	/**
	 * Method that returns new form page url.
	 *
	 * @return string
	 */
	public static function getNewFormPageUrl(): string
	{
		$postType = Forms::POST_TYPE_SLUG;

		return "/wp-admin/post-new.php?post_type={$postType}";
	}

	/**
	 * Method that returns trash page url.
	 *
	 * @return string
	 */
	public static function getFormsTrashPageUrl(): string
	{
		return self::getListingPageUrl() . '&type=trash';
	}

	/**
	 * Method that returns form edit page url.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	public static function getFormEditPageUrl(string $formId): string
	{
		return get_edit_post_link((int) $formId) ?? '';
	}

	/**
	 * Method that returns form trash action url.
	 *
	 * @param string $formId Form ID.
	 * @param bool $permanent Permanently delete.
	 *
	 * @return string
	 */
	public static function getFormTrashActionUrl(string $formId, bool $permanent = false): string
	{
		return get_delete_post_link((int) $formId, '', $permanent);
	}

	/**
	 * Method that returns form trash restore action url.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	public static function getFormTrashRestoreActionUrl(string $formId): string
	{
		return wp_nonce_url("/wp-admin/post.php?post={$formId}&action=untrash", 'untrash-post_' . $formId);
	}

	/**
	 * Get all field names from the form.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	public static function getFormNames(string $formId): string
	{
		$content = get_the_content(null, false, (int) $formId);

		// Find all name values.
		preg_match_all('/Name":"(.*?)"/m', $content, $matches, PREG_SET_ORDER);

		// Find custom predefined names.
		preg_match_all('/\/(sender-email) {(.*?)} \/-->/m', $content, $matchesCustom, PREG_SET_ORDER);

		$items = array_merge($matches, $matchesCustom);

		$output = [];

		// Populate output.
		foreach ($items as $item) {
			if (isset($item[1]) && !empty($item[1])) {
				$output[] = "<code>{" . $item[1] . "}</code>";
			}
		}

		return implode(', ', $output);
	}

	/**
	 * Provide error log output to a custom log file.
	 *
	 * @param array $message Any type of message.
	 *
	 * @return void
	 */
	public static function logger(array $message): void
	{
		if (Variables::isLogMode()) {
			$wpContentDir = defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : '';

			if (!empty($wpContentDir)) {
				$message['time'] = gmdate("Y-m-d H:i:s");
				error_log((string) wp_json_encode($message) . "\n -------------------------------------", 3, WP_CONTENT_DIR . '/eightshift-forms-debug.log'); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}
	}

	/**
	 * Check if current page is part of the settings page
	 *
	 * @return boolean
	 */
	public static function isSettingsPage(): bool
	{
		global $plugin_page; // phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps

		return !empty($plugin_page); // phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps
	}

	/**
	 * Minify string
	 *
	 * @param string $string String to check.
	 *
	 * @return string
	 */
	public static function minifyString(string $string): string
	{
		$string = str_replace(PHP_EOL, ' ', $string);
		$string = preg_replace('/[\r\n]+/', "\n", $string);
		return (string) preg_replace('/[ \t]+/', ' ', (string) $string);
	}


	/**
	 * Convert inner blocks to array
	 *
	 * @param string $string String to convert.
	 * @param string $type Type of content.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function convertInnerBlocksToArray(string $string, string $type): array
	{
		$output = [];

		switch ($type) {
			case 'select':
				$re = '/<option[^>]*value="(.*?)"[^>]*>([^<]*)<\s*\/\s*option\s*>/m';
				break;
			default:
				$re = '';
				break;
		}

		if (!$re) {
			return $output;
		}

		$string = Helper::minifyString($string);

		preg_match_all($re, $string, $matches, PREG_SET_ORDER, 0);

		if (!$matches) {
			return $output;
		}

		foreach ($matches as $match) {
			$output[] = [
				'label' => $match[2] ?? '',
				'value' => $match[1] ?? '',
				'original' => $match[0] ?? '',
			];
		}

		return $output;
	}

	/**
	 * Convert camel to snake case
	 *
	 * @param string $input Name to change.
	 *
	 * @return string
	 */
	public static function camelToSnakeCase($input): string
	{
		return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
	}
}
