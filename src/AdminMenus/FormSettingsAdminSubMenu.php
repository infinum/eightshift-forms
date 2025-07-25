<?php

/**
 * Class that holds class for admin sub menu - Form Settings.
 *
 * @package EightshiftForms\AdminMenus
 */

declare(strict_types=1);

namespace EightshiftForms\AdminMenus;

use EightshiftForms\CustomPostType\Forms;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftForms\Settings\Settings\SettingsBuilderInterface;
use EightshiftForms\General\SettingsGeneral;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftLibs\AdminMenus\AbstractAdminSubMenu;

/**
 * FormSettingsAdminSubMenu class.
 */
class FormSettingsAdminSubMenu extends AbstractAdminSubMenu
{
	/**
	 * Instance variable for all settings.
	 *
	 * @var SettingsBuilderInterface
	 */
	protected $settings;

	/**
	 * Create a new instance.
	 *
	 * @param SettingsBuilderInterface $settings Settings builder data for injecting the form.
	 */
	public function __construct(SettingsBuilderInterface $settings)
	{
		$this->settings = $settings;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action(
			'admin_menu',
			function () {
				\add_submenu_page(
					$this->getParentMenu(),
					$this->getTitle(),
					$this->getMenuTitle(),
					$this->getCapability(),
					$this->getMenuSlug(),
					[$this, 'processAdminSubmenu']
				);
			},
			30
		);

		\add_filter('parent_file', [$this, 'changeHighlightParent'], 31);
		\add_filter('admin_title', [$this, 'fixPageTitle'], 10, 2);
	}

	/**
	 * Capability for this admin sub menu
	 *
	 * @var string
	 */
	public const ADMIN_MENU_CAPABILITY = UtilsConfig::CAP_SETTINGS;

	/**
	 * Menu slug for this admin sub menu
	 *
	 * @var string
	 */
	public const ADMIN_MENU_SLUG = UtilsConfig::SLUG_ADMIN_SETTINGS;

	/**
	 * Parent menu slug for this admin sub menu
	 *
	 * @var string
	 */
	public const PARENT_MENU_SLUG = FormAdminMenu::ADMIN_MENU_SLUG;

	/**
	 * Get the title to use for the admin page.
	 *
	 * @return string The text to be displayed in the title tags of the page when the menu is selected.
	 */
	protected function getTitle(): string
	{
		$type = isset($_GET['type']) ? \sanitize_text_field(\wp_unslash($_GET['type'])) : SettingsGeneral::SETTINGS_TYPE_KEY; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// translators: %s replaces form title name.
		return \sprintf(\esc_html__('Form settings - %s', 'eightshift-forms'), \ucfirst(\esc_html($type)));
	}

	/**
	 * Get the menu title to use for the admin menu.
	 *
	 * @return string The text to be used for the menu.
	 */
	protected function getMenuTitle(): string
	{
		return \esc_html__('Form settings', 'eightshift-forms');
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
	 * @return string The slug name for the parent menu (or the file name of a standard WordPress admin page.
	 */
	protected function getParentMenu(): string
	{
		return 'null';
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
		$formId = isset($_GET['formId']) ? \sanitize_text_field(\wp_unslash($_GET['formId'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$type = isset($_GET['type']) ? \sanitize_text_field(\wp_unslash($_GET['type'])) : SettingsGeneral::SETTINGS_TYPE_KEY; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if (empty($formId)) {
			return [];
		}

		$formTitle = \get_the_title((int) $formId);

		if (UtilsDeveloperHelper::isDeveloperModeActive()) {
			$formTitle = "{$formId} - {$formTitle}";
		}

		if (!$formTitle) {
			$formTitle = \esc_html__('No form title', 'eightshift-forms');
		}

		$integrationTypeUsed = UtilsGeneralHelper::getFormTypeById($formId);
		$formEditLink = UtilsGeneralHelper::getFormEditPageUrl($formId);

		return [
			// translators: %s replaces the form name.
			'adminSettingsPageTitle' => \sprintf(\esc_html__('Form settings: %s', 'eightshift-forms'), $formTitle),
			'adminSettingsBackLink' => UtilsGeneralHelper::getListingPageUrl(),
			'adminSettingsFormEditLink' => $formEditLink,
			'adminSettingsFormLocationsLink' => UtilsGeneralHelper::getListingPageUrl(UtilsConfig::SLUG_ADMIN_LISTING_LOCATIONS, $formId),
			'adminSettingsSidebar' => $this->settings->getSettingsSidebar($formId, $integrationTypeUsed),
			'adminSettingsForm' => $this->settings->getSettingsForm($type, $formId),
			'adminSettingsType' => $type,
			// translators: %s will be replaced with the form edit link.
			'adminSettingsNotice' => !$integrationTypeUsed ? \sprintf(\__('Please set a block type in the form\'s block editor. Configuration options will appear in the sidebar afterwards.  <a href="%s" target="_blank" rel="noopener noreferrer">Edit form &rarr;</a>', 'eightshift-forms'), $formEditLink) : '',
		];
	}

	/**
	 * Fix Parent Admin Menu Item
	 *
	 * @param string|null $parentFile Parent file to check.
	 *
	 * @return string
	 */
	public function changeHighlightParent($parentFile)
	{
		global $plugin_page; // phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps

		if ($plugin_page === UtilsConfig::SLUG_ADMIN_SETTINGS) { // phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps
			$plugin_page = Forms::POST_TYPE_SLUG; // phpcs:ignore
		}
		return $parentFile ?? '';
	}

	/**
	 * Update the page title.
	 *
	 * @param string $adminTitle The page title, with extra context added.
	 * @param string $title The original page title.

	 * @return string
	 */
	public function fixPageTitle(string $adminTitle, string $title): string
	{
		if (\get_current_screen()->id === "admin_page_" . self::ADMIN_MENU_SLUG && $title === '') {
			$adminTitle = $this->getTitle() . $adminTitle;
		}

		return $adminTitle;
	}
}
