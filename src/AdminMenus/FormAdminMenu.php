<?php

/**
 * Class that holds class for admin sub menu - Form Listing.
 *
 * @package EightshiftForms\AdminMenus
 */

declare(strict_types=1);

namespace EightshiftForms\AdminMenus;

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\Listing\FormListingInterface;
use EightshiftFormsVendor\EightshiftLibs\AdminMenus\AbstractAdminMenu;

/**
 * FormAdminMenu class.
 */
class FormAdminMenu extends AbstractAdminMenu
{
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
	public const ADMIN_MENU_ICON = 'dashicons-forms';

	/**
	 * Menu position for this admin menu.
	 *
	 * @var string
	 */
	public const ADMIN_MENU_POSITION = 4;

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
		return self::ADMIN_MENU_ICON;
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
		return self::ADMIN_MENU_POSITION;
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
		return [
			'adminListingPageTitle' => \esc_html__('All your forms', 'eightshift-forms'),
			'adminListingSubTitle' => \esc_html__('On listing page you can preview all your forms in one place.', 'eightshift-forms'),
			'adminListingNewFormLink' => Helper::getNewFormPageUrl(),
			'adminListingTrashLink' => Helper::getFormsTrashPageUrl(),
			'adminListingForms' => $this->formsListing->getFormsList(),
		];
	}
}
