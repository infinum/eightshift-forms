<?php

/**
 * The file that defines the project entry point class.
 *
 * A class definition that includes attributes and functions used across both the
 * public side of the site and the admin area.
 *
 * @package EightshiftForms\Config
 */

declare(strict_types=1);

namespace EightshiftForms\Config;

use EightshiftForms\AdminMenus\FormMainListingAdminMenu;
use EightshiftForms\AdminMenus\FormOptionAdminSubMenu;
use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftFormsPluginVendor\EightshiftLibs\Config\AbstractConfigData;

/**
 * The project config class.
 */
class Config extends AbstractConfigData
{

	/**
	 * Method that returns project name.
	 *
	 * Generally used for naming assets handlers, languages, etc.
	 */
	public static function getProjectName(): string
	{
		return "eightshift-forms";
	}

	/**
	 * Method that returns project version.
	 *
	 * Generally used for versioning asset handlers while enqueueing them.
	 */
	public static function getProjectVersion(): string
	{
		return '1.0.0';
	}

	/**
	 * Method that returns project REST-API namespace.
	 *
	 * Used for namespacing projects REST-API routes and fields.
	 *
	 * @return string Project name.
	 */
	public static function getProjectRoutesNamespace(): string
	{
		return static::getProjectName();
	}

	/**
	 * Method that returns project REST-API version.
	 *
	 * Used for versioning projects REST-API routes and fields.
	 *
	 * @return string Project route version.
	 */
	public static function getProjectRoutesVersion(): string
	{
		return 'v1';
	}

	/**
	 * Method that returns listing page url.
	 *
	 * @return string
	 */
	public static function getListingPageUrl(): string
	{
		$page = FormMainListingAdminMenu::ADMIN_MENU_SLUG;

		return "/wp-admin/admin.php?page={$page}";
	}

	/**
	 * Method that returns form options page url.
	 *
	 * @param string $formId Form ID.
	 * @param string $type type key.
	 *
	 * @return string
	 */
	public static function getOptionsPageUrl(string $formId, string $type = SettingsGeneral::TYPE_KEY): string
	{
		$postType = Forms::POST_TYPE_SLUG;
		$page = FormOptionAdminSubMenu::ADMIN_MENU_SLUG;
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
