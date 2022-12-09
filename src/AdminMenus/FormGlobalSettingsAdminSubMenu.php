<?php

/**
 * Class that holds class for admin sub menu - Global Settings.
 *
 * @package EightshiftForms\AdminMenus
 */

declare(strict_types=1);

namespace EightshiftForms\AdminMenus;

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\Settings\SettingsDashboard;
use EightshiftForms\Settings\Settings\SettingsInterface;
use EightshiftFormsVendor\EightshiftLibs\AdminMenus\AbstractAdminSubMenu;

/**
 * FormGlobalSettingsAdminSubMenu class.
 */
class FormGlobalSettingsAdminSubMenu extends AbstractAdminSubMenu
{
	/**
	 * Instance variable for global settings.
	 *
	 * @var SettingsInterface
	 */
	protected $settings;

	/**
	 * Create a new instance.
	 *
	 * @param SettingsInterface $settings Inject form global settings data.
	 */
	public function __construct(SettingsInterface $settings)
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
			40
		);
	}

	/**
	 * Capability for this admin sub menu.
	 *
	 * @var string
	 */
	public const ADMIN_MENU_CAPABILITY = 'eightshift_forms_global_settings';

	/**
	 * Menu slug for this admin sub menu.
	 *
	 * @var string
	 */
	public const ADMIN_MENU_SLUG = 'es-settings-global';

	/**
	 * Parent menu slug for this admin sub menu.
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
		return \esc_html__('Global settings', 'eightshift-forms');
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
	 * @return string View URI.
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
		$type = isset($_GET['type']) ? \sanitize_text_field(\wp_unslash($_GET['type'])) : SettingsDashboard::SETTINGS_TYPE_KEY; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return [
			// translators: %s replaces form title name.
			'adminSettingsPageTitle' => \esc_html__('Global settings', 'eightshift-forms'),
			'adminSettingsBackLink' => Helper::getListingPageUrl(),
			'adminSettingsSidebar' => $this->settings->getSettingsSidebar(),
			'adminSettingsForm' => $this->settings->getSettingsForm($type),
			'adminSettingsType' => $type,
			'adminSettingsIsGlobal' => true,
		];
	}
}
