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
use EightshiftForms\Misc\SettingsWpml;
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
	public const ADMIN_MENU_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M1.5 0A1.5 1.5 0 0 0 0 1.5v17A1.5 1.5 0 0 0 1.5 20h17a1.5 1.5 0 0 0 1.5-1.5v-17A1.5 1.5 0 0 0 18.5 0h-17ZM3 2a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1h9a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H3ZM2 7.5a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1Zm9 7.5a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-1a1 1 0 0 0-1-1h-6Z" fill="#000"/><rect x="12" y="16.25" width="4" height="0.5" rx="0.25" fill="#000"/></svg>';

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
		$type = isset($_GET['type']) ? \sanitize_text_field(\wp_unslash($_GET['type'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$title = \esc_html__('Forms', 'eightshift-forms');

		$useWpml = \apply_filters(SettingsWpml::FILTER_SETTINGS_IS_VALID_NAME, []);
		if ($useWpml) {
			$lang = \apply_filters('wpml_current_language', '');
			if ($lang) {
				$title = $title . ' - ' . \strtoupper($lang);
			}
		}

		if ($type === 'trash') {
			$title = \esc_html__('Deleted forms', 'eightshift-forms');
		}

		$items = $this->formsListing->getFormsList($type);

		return [
			'adminListingPageTitle' => $title,
			'adminListingItemsCount' => count($items),
			'adminListingItems' => $this->getListingItems($items, $type),
			'adminListingTopItems' => $this->getTopBarItems($type),
			'adminListingNoItems' => $this->getNoItemsMessage($type),
		];
	}

	private function getNoItemsMessage(string $type): array
	{
		$isTrashPage = $type === 'trash';
		$newUrl = Helper::getNewFormPageUrl();
		$listingUrl = Helper::getListingPageUrl();

		if (!$isTrashPage) {
			$output = [
				Components::render('highlighted-content', [
					'highlightedContentTitle' => __('You have no forms', 'eightshift-forms'),
					'highlightedContentSubtitle' => '<br /><a class="es-submit es-submit--outline" href="' . $newUrl . '">' . __('Add your first form', 'eightshift-forms') . '</a>',
					'highlightedContentIcon' => 'emptyStateFormList',
				]),
			];
		} else {
			$output = [
				Components::render('highlighted-content', [
					'highlightedContentTitle' => __('Trash is empty', 'eightshift-forms'),
					'highlightedContentSubtitle' => '<br /><a class="es-submit es-submit--outline" href="' . $listingUrl . '">' . __('Go to your forms', 'eightshift-forms') . '</a>',
					'highlightedContentIcon' => 'emptyStateTrash',
				]),
			];
		}

		return $output;
	}

	private function getTopBarItems(string $type): array
	{
		$manifest = Components::getComponent('admin-listing');
		$manifestCustomFormAttrs = Components::getSettings()['customFormAttrs'];

		$componentJsBulkClass = $manifest['componentJsBulkClass'] ?? '';
		$componentJsSelectAllClass = $manifest['componentJsSelectAllClass'] ?? '';
		$componentJsFilterClass = $manifest['componentJsFilterClass'] ?? '';

		$isTrashPage = $type === 'trash';

		$left = [
			Components::render('checkbox', [
				'checkboxValue' => 'all',
				'checkboxName' => 'all',
				'additionalClass' => $componentJsSelectAllClass,
			]),
		];

		if (!$isTrashPage) {
			$left = [
				...$left,
				Components::render('select',[
					'fieldSkip' => true,
					'selectName' => 'filter',
					'selectContent' => $this->getFilterOptions(),
					'selectPlaceholder' => \__('Show all', 'eightshift-forms'),
					'additionalClass' => $componentJsFilterClass,
				]),
				Components::render('submit', [
					'submitVariant' => 'ghost',
					'submitValue' => \__('Delete', 'eightshift-forms'),
					'additionalClass' => $componentJsBulkClass,
					'submitAttrs' => [
						$manifestCustomFormAttrs['bulkType'] => 'delete',
					],
				]),
				Components::render('submit', [
					'submitVariant' => 'ghost',
					'submitValue' => \__('Sync', 'eightshift-forms'),
					'additionalClass' => $componentJsBulkClass,
					'submitAttrs' => [
						$manifestCustomFormAttrs['bulkType'] => 'sync',
					],
				]),
				Components::render('submit', [
					'submitVariant' => 'ghost',
					'submitValue' => \__('Duplicate', 'eightshift-forms'),
					'additionalClass' => $componentJsBulkClass,
					'submitAttrs' => [
						$manifestCustomFormAttrs['bulkType'] => 'duplicate',
					],
				]),
			];
		} else {
			$left = [
				...$left,
				Components::render('submit', [
					'submitVariant' => 'ghost',
					'submitButtonAsLink' => true,
					'submitButtonAsLinkUrl' => Helper::getListingPageUrl(),
					'submitValue' => \__('Back', 'eightshift-forms'),
					'submitIcon' => Helper::getProjectIcons('arrowLeft')
				]),
			];
		}

		if (!$isTrashPage) {
			$right = [
				Components::render('submit', [
					'submitVariant' => 'outline',
					'submitButtonAsLink' => true,
					'submitButtonAsLinkUrl' => Helper::getFormsTrashPageUrl(),
					'submitValue' => \__('Trashed', 'eightshift-forms'),
				]),
				Components::render('submit', [
					'submitButtonAsLink' => true,
					'submitButtonAsLinkUrl' => Helper::getNewFormPageUrl(),
					'submitValue' => \__('Create', 'eightshift-forms'),
					'submitIcon' => Helper::getProjectIcons('addHighContrast')
				]),
			];
		} else {
			$right = [
				Components::render('submit', [
					'submitVariant' => 'ghost',
					'submitValue' => \__('Restore', 'eightshift-forms'),
					'additionalClass' => $componentJsBulkClass,
					'submitAttrs' => [
						$manifestCustomFormAttrs['bulkType'] => 'restore',
					],
				]),
				Components::render('submit', [
					'submitVariant' => 'ghost',
					'submitValue' => \__('Delete permanently', 'eightshift-forms'),
					'additionalClass' => $componentJsBulkClass,
					'submitAttrs' => [
						$manifestCustomFormAttrs['bulkType'] => 'delete-perminentely',
					],
				]),
			];
		}

		return [
			'left' => Components::ensureString($left),
			'right' => Components::ensureString($right),
		];
	}

	private function getListingItems(array $items, string $type) : array
	{
		$output = [];
		$isTrashPage = $type === 'trash';

		$isDevMode = \apply_filters(SettingsDebug::FILTER_SETTINGS_IS_DEBUG_ACTIVE, SettingsDebug::SETTINGS_DEBUG_DEVELOPER_MODE_KEY);

		$manifest = Components::getComponent('admin-listing');
		$manifestCustomFormAttrs = Components::getSettings()['customFormAttrs'];
		$componentJsItemClass = $manifest['componentJsItemClass'] ?? '';

		foreach ($items as $item) {
			$id = $item['id'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$title = $item['title'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$editLink = $item['editLink'] ?? '#';
			$postType = $item['postType'] ?? '';
			$activeIntegration = $item['activeIntegration'] ?? [];

			$cardIcon = $activeIntegration['icon'] ?: Helper::getProjectIcons('listingGeneric'); // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

			if ($postType === 'post') {
				$cardIcon = Helper::getProjectIcons('post');
			} elseif ($postType === 'page') {
				$cardIcon = Helper::getProjectIcons('page');
			}

			if (!$title) {
				// Translators: %s is the form ID.
				$title = sprintf(__('Form %s', 'eightshift-forms'), $id);
			}

			$isValid = $this->isIntegrationValid($item);

			$output[] = Components::render('card-inline', [
				'cardInlineTitle' => $title . ($isDevMode ? " ({$id})" : ''),
				'cardInlineTitleLink' => $editLink,
				'cardInlineSubTitle' => !$isTrashPage ? implode(', ', $this->getSubtitle($item)) : '',
				'cardInlineIcon' => $cardIcon,
				'cardInlineLeftContent' => Components::ensureString($this->getLeftContent($item)),
				'cardInlineRightContent' => Components::ensureString($this->getRightContent($item, $type)),
				'cardInlineInvalid' => !$isValid,
				'additionalAttributes' => [
					$manifestCustomFormAttrs['adminIntegrationType'] => $isValid ? $activeIntegration['value'] : FormAdminMenu::ADMIN_MENU_FILTER_NOT_CONFIGURED,
					$manifestCustomFormAttrs['bulkId'] => $id,
				],
				'additionalClass' => Components::classnames([
					$componentJsItemClass,
				]),
			]);
		}

		return $output;
	}

	private function isIntegrationValid(array $item): bool
	{
		$isActive = $item['activeIntegration']['isActive'] ?? false;
		$isValid = $item['activeIntegration']['isValid'] ?? false;
		$isApiValid = $item['activeIntegration']['isApiValid'] ?? false;

		return $isActive && $isValid && $isApiValid;
	}

	private function getSubtitle(array $item): array
	{
		$output = [];

		$status = $item['status'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$postType = $item['postType'] ?? '';
		$isActive = $item['activeIntegration']['isActive'] ?? false;
		$isValid = $item['activeIntegration']['isValid'] ?? false;
		$isApiValid = $item['activeIntegration']['isApiValid'] ?? false;

		if ($status !== 'publish') {
			$output[] = ucfirst($status);

			if ($postType) {
				$output[] = ucfirst($postType);
			}
		}

		if (!$isActive) {
			$output[] = '<span class="error-text">' . esc_html__('Integration not enabled', 'eightshift-forms') . '</span>';
		}

		if (!$isValid) {
			$output[] = '<span class="error-text">' . esc_html__('Form configuration not valid', 'eightshift-forms') . '</span>';
		}

		if (!$isApiValid) {
			$output[] = '<span class="error-text">' . esc_html__('Missing form fields', 'eightshift-forms') . '</span>';
		}

		return $output;
	}

	private function getLeftContent(array $item): array
	{
		$id = $item['id'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		return [
			Components::render('checkbox', [
				'checkboxValue' => $id,
				'checkboxName' => $id,
			]),
		];
	}

	private function getRightContent(array $item, string $type): array
	{
		$manifest = Components::getComponent('admin-listing');
		$manifestCustomFormAttrs = Components::getSettings()['customFormAttrs'];

		$componentJsLocationsClass = $manifest['componentJsLocationsClass'] ?? '';

		$id = $item['id'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$settingsLink = $item['settingsLink'] ?? '';

		$isTrashPage = $type === 'trash';

		$output = [
			Components::render('submit', [
				'submitVariant' => 'ghost',
				'submitValue' => \__('Locations', 'eightshift-forms'),
				'submitAttrs' => [
					$manifestCustomFormAttrs['locationsId'] => $id
				],
				'additionalClass' => $componentJsLocationsClass,
			]),
		];

		if (!$isTrashPage) {
			$output = [
				...$output,
				Components::render('submit', [
					'submitVariant' => 'ghost',
					'submitButtonAsLink' => true,
					'submitButtonAsLinkUrl' => $settingsLink,
					'submitValue' => \__('Settings', 'eightshift-forms'),
				]),
			];
		}

		return $output;
	}

	private function getFilterOptions(): string
	{
		$filterOptions = Components::render(
			'select-option',
			[
				'selectOptionLabel' => \__('Not Configured', 'eightshift-forms'),
				'selectOptionValue' => self::ADMIN_MENU_FILTER_NOT_CONFIGURED,
			]
		);

		$activeIntegration = \array_flip($this->getActiveIntegrations());

		foreach (Filters::ALL as $key => $value) {
			if ($value['type'] !== Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION) {
				continue;
			}

			if (!isset($activeIntegration[$key])) {
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

		return $filterOptions;
	}
}
