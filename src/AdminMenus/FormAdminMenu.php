<?php

/**
 * Class that holds class for admin sub menu - Form Listing.
 *
 * @package EightshiftForms\AdminMenus
 */

declare(strict_types=1);

namespace EightshiftForms\AdminMenus;

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\Listing\FormListingInterface;
use EightshiftForms\Settings\Settings\Settings;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftFormsVendor\EightshiftLibs\AdminMenus\AbstractAdminMenu;

/**
 * FormAdminMenu class.
 */
class FormAdminMenu extends AbstractAdminMenu
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Instance variable for listing data.
	 *
	 * @var FormListingInterface
	 */
	protected $formsListing;

	/**
	 * Create a new instance.
	 *
	 * @param FormListingInterface $formsListing Inject form listing data.
	 */
	public function __construct(FormListingInterface $formsListing)
	{
		$this->formsListing = $formsListing;
	}

	/**
	 * Capability for this admin sub menu
	 *
	 * @var string
	 */
	public const ADMIN_MENU_CAPABILITY = 'eightshift_forms_adminu_menu';

	/**
	 * Menu slug for this admin sub menu
	 *
	 * @var string
	 */
	public const ADMIN_MENU_SLUG = 'es-forms';

	/**
	 * Menu icon for this admin menu.
	 *
	 * @var string
	 */
	public const ADMIN_MENU_ICON = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity=".6" fill-rule="evenodd" clip-rule="evenodd" d="M6.75 11.75A.75.75 0 0 1 7.5 11h5a.75.75 0 0 1 0 1.5h-5a.75.75 0 0 1-.75-.75zm0 3A.75.75 0 0 1 7.5 14h5a.75.75 0 0 1 0 1.5h-5a.75.75 0 0 1-.75-.75z" fill="black"/><path fill-rule="evenodd" clip-rule="evenodd" d="M3.75 8.75A.75.75 0 0 1 4.5 8h5a.75.75 0 0 1 0 1.5h-5a.75.75 0 0 1-.75-.75z" fill="black"/><path d="M6 11.75a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm0 3a1 1 0 1 1-2 0 1 1 0 0 1 2 0z" fill="black"/><path opacity=".3" d="M1 2a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v4H1V2z" fill="black"/><path fill-rule="evenodd" clip-rule="evenodd" d="M.25 2.5A2.25 2.25 0 0 1 2.5.25h15a2.25 2.25 0 0 1 2.25 2.25v15a2.25 2.25 0 0 1-2.25 2.25h-15A2.25 2.25 0 0 1 .25 17.5v-15zm2.25-.75a.75.75 0 0 0-.75.75v15c0 .414.336.75.75.75h15a.75.75 0 0 0 .75-.75v-15a.75.75 0 0 0-.75-.75h-15z" fill="black"/><path fill-rule="evenodd" clip-rule="evenodd" d="M3.75 3.75A.75.75 0 0 1 4.5 3h8a.75.75 0 0 1 0 1.5h-8a.75.75 0 0 1-.75-.75z" fill="black"/></svg>';

	/**
	 * Menu position for this admin menu.
	 *
	 * @var int
	 */
	public const ADMIN_MENU_POSITION = 4;

	/**
	 * Menu position filter not configured key.
	 *
	 * @var string
	 */
	public const ADMIN_MENU_FILTER_NOT_CONFIGURED = 'not-configured';

	/**
	 * Get the title to use for the admin page.
	 *
	 * @return string The text to be displayed in the title tags of the page when the menu is selected.
	 */
	protected function getTitle(): string
	{
		return \esc_html__('Eightshift Forms', 'eightshift-forms');
	}

	/**
	 * Get the menu title to use for the admin menu.
	 *
	 * @return string The text to be used for the menu.
	 */
	protected function getMenuTitle(): string
	{
		return \esc_html__('Eightshift Forms', 'eightshift-forms');
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
	 * Get the URL to the icon to be used for this menu
	 *
	 * @return string The URL to the icon to be used for this menu.
	 *                * Pass a base64-encoded SVG using a data URI, which will be colored to match
	 *                  the color scheme. This should begin with 'data:image/svg+xml;base64,'.
	 *                * Pass the name of a Dashicons helper class to use a font icon,
	 *                  e.g. 'dashicons-chart-pie'.
	 *                * Pass 'none' to leave div.wp-menu-image empty so an icon can be added via CSS.
	 */
	protected function getIcon(): string
	{
		return 'data:image/svg+xml;base64,' . \base64_encode(self::ADMIN_MENU_ICON); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Get the position of the menu.
	 *
	 * @return int Number that indicates the position of the menu.
	 * 5   - below Posts
	 * 10  - below Media
	 * 15  - below Links
	 * 20  - below Pages
	 * 25  - below comments
	 * 60  - below first separator
	 * 65  - below Plugins
	 * 70  - below Users
	 * 75  - below Tools
	 * 80  - below Settings
	 * 100 - below second separator
	 */
	protected function getPosition(): int
	{
		return (int) self::ADMIN_MENU_POSITION;
	}

	/**
	 * Get the view component that will render correct view.
	 *
	 * @return string View uri.
	 */
	protected function getViewComponent(): string
	{
		return 'admin-listing';
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
		$status = isset($_GET['type']) ? \sanitize_text_field(\wp_unslash($_GET['type'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$title = \esc_html__('All forms', 'eightshift-forms');
		$trashLink = Helper::getFormsTrashPageUrl();
		$listingLink = '';

		if ($status === 'trash') {
			$title = \esc_html__('Deleted forms', 'eightshift-forms');
			$trashLink = '';
			$listingLink = Helper::getListingPageUrl();
		}

		$filterOptions = Components::render(
			'select-option',
			[
				'selectOptionLabel' => \__('All', 'eightshift-forms'),
				'selectOptionValue' => 'all',
			]
		);
		$filterOptions .= Components::render(
			'select-option',
			[
				'selectOptionLabel' => \__('Not Configured', 'eightshift-forms'),
				'selectOptionValue' => self::ADMIN_MENU_FILTER_NOT_CONFIGURED,
			]
		);

		foreach (Filters::ALL as $key => $value) {
			if ($value['type'] !== Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION) {
				continue;
			}

			$filterOptions .= Components::render(
				'select-option',
				[
					'selectOptionLabel' => Filters::getSettingsLabels($key),
					'selectOptionValue' => $key,
				]
			);
		}

		$filter = Components::render(
			'select',
			[
				'fieldSkip' => true,
				'selectContent' => $filterOptions,
			]
		);

		return [
			'adminListingPageTitle' => $title,
			'adminListingNewFormLink' => Helper::getNewFormPageUrl(),
			'adminListingTrashLink' => $trashLink,
			'adminListingForms' => $this->formsListing->getFormsList($status),
			'adminListingType' => $status,
			'adminListingListingLink' => $listingLink,
			'adminListingIntegrations' => $filter,
			'adminListingIsDeveloperMode' => $this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_DEVELOPER_MODE_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY),
		];
	}
}
