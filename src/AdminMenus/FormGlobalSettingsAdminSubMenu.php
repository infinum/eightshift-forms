<?php

/**
 * Class that holds class for admin sub menu - Global Settings.
 *
 * @package EightshiftForms\AdminMenus
 */

declare(strict_types=1);

namespace EightshiftForms\AdminMenus;

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Dashboard\SettingsDashboard;
use EightshiftForms\Settings\SettingsBuilderInterface;
use EightshiftForms\Config\Config;
use EightshiftFormsVendor\EightshiftLibs\AdminMenus\AbstractAdminSubMenu;

/**
 * FormGlobalSettingsAdminSubMenu class.
 */
class FormGlobalSettingsAdminSubMenu extends AbstractAdminSubMenu
{
	/**
	 * Instance variable for global settings.
	 *
	 * @var SettingsBuilderInterface
	 */
	protected $settings;

	/**
	 * Create a new instance.
	 *
	 * @param SettingsBuilderInterface $settings Inject form global settings data.
	 */
	public function __construct(SettingsBuilderInterface $settings)
	{
		$this->settings = $settings;
	}

	/**
	 * Capability for this admin sub menu.
	 *
	 * @var string
	 */
	public const ADMIN_MENU_CAPABILITY = Config::CAP_SETTINGS_GLOBAL;

	/**
	 * Menu slug for this admin sub menu.
	 *
	 * @var string
	 */
	public const ADMIN_MENU_SLUG = Config::SLUG_ADMIN_SETTINGS_GLOBAL;

	/**
	 * Parent menu slug for this admin sub menu.
	 *
	 * @var string
	 */
	public const PARENT_MENU_SLUG = FormAdminMenu::ADMIN_MENU_SLUG;

	/**
	 * Return hook priority order.
	 *
	 * @return integer
	 */
	public function getPriorityOrder(): int
	{
		return 40;
	}

	/**
	 * Get the title to use for the admin page.
	 *
	 * @return string The text to be displayed in the title tags of the page when the menu is selected.
	 */
	protected function getTitle(): string
	{
		$type = isset($_GET['type']) ? \sanitize_text_field(\wp_unslash($_GET['type'])) : SettingsDashboard::SETTINGS_TYPE_KEY; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// translators: %s replaces form title name.
		return \sprintf(\esc_html__('Global settings - %s', 'eightshift-forms'), \ucfirst(\esc_html($type)));
	}

	/**
	 * Get the menu title to use for the admin menu.
	 *
	 * @return string The text to be used for the menu.
	 */
	protected function getMenuTitle(): string
	{
		return \esc_html__('Global settings', 'eightshift-forms');
	}

	/**
	 * Get the capability required for this menu to be displayed.
	 *
	 * @return string The capability required for this menu to be displayed to the user.
	 */
	protected function getCapability(): string
	{
		return self::ADMIN_MENU_CAPABILITY;
	}

	/**
	 * Get the menu slug.
	 *
	 * @return string The slug name to refer to this menu by.
	 *                Should be unique for this menu page and only include lowercase alphanumeric,
	 *                dashes, and underscores characters to be compatible with sanitize_key().
	 */
	protected function getMenuSlug(): string
	{
		return self::ADMIN_MENU_SLUG;
	}

	/**
	 * Get the slug of the parent menu.
	 *
	 * @return string The slug name for the parent menu (or the file name) of a standard WordPress admin page.
	 */
	protected function getParentMenu(): string
	{
		return self::PARENT_MENU_SLUG;
	}

	/**
	 * Get the view component that will render correct view.
	 *
	 * @param array<string, mixed> $attributes Array of attributes passed to the view.
	 *
	 * @return string View uri.
	 */
	protected function getViewComponent(array $attributes): string
	{
		return Helpers::render('admin-settings', $attributes);
	}

	/**
	 * Process the admin menu attributes.
	 *
	 * Here you can get any kind of metadata, query the database, etc..
	 * This data will be passed to the component view to be rendered out in the
	 * processAdminMenu parent method.
	 *
	 * @param array<string, mixed>|string $attr Raw admin menu attributes passed into the
	 *                           admin menu function.
	 *
	 * @return array<string, mixed> Processed admin menu attributes.
	 */
	protected function processAttributes($attr): array
	{
		$type = isset($_GET['type']) ? \sanitize_text_field(\wp_unslash($_GET['type'])) : SettingsDashboard::SETTINGS_TYPE_KEY; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return [
			'adminSettingsPageTitle' => \esc_html__('Global settings', 'eightshift-forms'),
			'adminSettingsBackLink' => GeneralHelpers::getListingPageUrl(),
			'adminSettingsSidebar' => $this->settings->getSettingsSidebar(),
			'adminSettingsForm' => $this->settings->getSettingsForm($type, '0'),
			'adminSettingsType' => $type,
			'adminSettingsIsGlobal' => true,
		];
	}
}
