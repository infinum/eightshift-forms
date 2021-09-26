<?php

/**
 * Trait that holds all generic helpers.
 *
 * @package EightshiftLibs\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

use EightshiftForms\AdminMenus\FormDetailsAdminSubMenu;
use EightshiftForms\AdminMenus\FormListingAdminMenu;
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
		$page = FormListingAdminMenu::ADMIN_MENU_SLUG;

		return "/wp-admin/admin.php?page={$page}";
	}

	/**
	 * Method that returns form options page url.
	 *
	 * @param string $formId Form ID.
	 * @param string $type Type key.
	 *
	 * @return string
	 */
	public static function getOptionsPageUrl(string $formId, string $type = SettingsGeneral::TYPE_KEY): string
	{
		$postType = Forms::POST_TYPE_SLUG;
		$page = FormDetailsAdminSubMenu::ADMIN_MENU_SLUG;
		$typeKey = '';

		if (!empty($type)) {
			$typeKey = "&type={$type}";
		}

		return "/wp-admin/edit.php?post_type={$postType}&page={$page}&formId={$formId}{$typeKey}";
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
}
