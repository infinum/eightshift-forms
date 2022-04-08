<?php

/**
 * Class that holds class for admin sub menu - Form Settings.
 *
 * @package EightshiftForms\AdminMenus
 */

declare(strict_types=1);

namespace EightshiftForms\AdminMenus;

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\Settings\SettingsAllInterface;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftFormsVendor\EightshiftLibs\AdminMenus\AbstractAdminSubMenu;

/**
 * FormSettingsAdminSubMenu class.
 */
class FormSettingsAdminSubMenu extends AbstractAdminSubMenu
{
	/**
	 * Instance variable for all settings.
	 *
	 * @var SettingsAllInterface
	 */
	protected $settingsAll;

	/**
	 * Create a new instance.
	 *
	 * @param SettingsAllInterface $settingsAll Inject form all settings data.
	 */
	public function __construct(SettingsAllInterface $settingsAll)
	{
		$this->settingsAll = $settingsAll;
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
					'',
					$this->getTitle(),
					$this->getMenuTitle(),
					$this->getCapability(),
					$this->getMenuSlug(),
					[$this, 'processAdminSubmenu']
				);
			},
			30
		);

		add_filter('parent_file', [$this, 'changeHighlightParent'], 31);
	}

	/**
	 * Capability for this admin sub menu
	 *
	 * @var string
	 */
	public const ADMIN_MENU_CAPABILITY = 'eightshift_forms_form_settings';

	/**
	 * Menu slug for this admin sub menu
	 *
	 * @var string
	 */
	public const ADMIN_MENU_SLUG = 'es-settings';

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
		return \esc_html__('Form Settings', 'eightshift-forms');
	}

	/**
	 * Get the menu title to use for the admin menu.
	 *
	 * @return string The text to be used for the menu.
	 */
	protected function getMenuTitle(): string
	{
		return \esc_html__('Form Settings', 'eightshift-forms');
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
		return self::PARENT_MENU_SLUG;
	}

	/**
	 * Get the view component that will render correct view.
	 *
	 * @return string View uri.
	 */
	protected function getViewComponent(): string
	{
		return 'admin-settings';
	}

	/**
	 * Render the current view.
	 *
	 * @param array<string, mixed> $attributes Array of attributes passed to the view.
	 * @param string $innerBlockContent Not used here.
	 *
	 * @return string Rendered HTML.
	 * @throws \Exception On missing attributes OR missing template.
	 */
	public function render(array $attributes = [], string $innerBlockContent = ''): string
	{
		return Components::render($this->getViewComponent(), $attributes);
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
		$formId = isset($_GET['formId']) ? \sanitize_text_field(wp_unslash($_GET['formId'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$type = isset($_GET['type']) ? \sanitize_text_field(wp_unslash($_GET['type'])) : SettingsGeneral::SETTINGS_TYPE_KEY; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if (empty($formId)) {
			return [];
		}

		$formTitle = get_the_title((int) $formId);

		if (!$formTitle) {
			$formTitle = esc_html__('No form title', 'eightshift-forms');
		}

		return [
			// translators: %s replaces the form name.
			'adminSettingsPageTitle' => sprintf(\esc_html__('Form settings: %s', 'eightshift-forms'), $formTitle),
			'adminSettingsBackLink' => Helper::getListingPageUrl(),
			'adminSettingsFormEditLink' => Helper::getFormEditPageUrl($formId),
			'adminSettingsLink' => Helper::getSettingsPageUrl($formId, ''),
			'adminSettingsSidebar' => $this->settingsAll->getSettingsSidebar($formId, $type),
			'adminSettingsForm' => $this->settingsAll->getSettingsForm($formId, $type),
			'adminSettingsType' => $type,
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

		if ($plugin_page === 'es-settings') { // phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps
			$plugin_page = 'es-forms'; // phpcs:ignore
		}

		return $parentFile ?? '';
	}
}
