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

			return base64_encode($output); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
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
	 * Method that returns form edit page url.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	public static function getFormEditPageUrl(string $formId): string
	{
		return "/wp-admin/post.php?post={$formId}&action=edit";
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
		$content = get_the_content(null, null, $formId);

		// Find all name values.
		preg_match_all('/Name":"(.*?)"/m', $content, $matches, PREG_SET_ORDER);

		// Find custom predefined names.
		preg_match_all('/\/(sender-email|sender-name) {(.*?)} \/-->/m', $content, $matchesCustom, PREG_SET_ORDER);

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
}
