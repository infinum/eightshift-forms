<?php

/**
 * * Admin menu functionality.
 *
 * @package EightshiftForms\AdminMenus
 */

declare(strict_types=1);

namespace EightshiftForms\AdminMenus;

use EightshiftForms\Entries\EntriesHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Misc\SettingsWpml;
use EightshiftForms\Listing\FormListingInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsIntegrationsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
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
	public const ADMIN_MENU_CAPABILITY = UtilsConfig::CAP_LISTING;

	/**
	 * Menu slug for this admin sub menu
	 *
	 * @var string
	 */
	public const ADMIN_MENU_SLUG = UtilsConfig::SLUG_ADMIN;

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
		return 'data:image/svg+xml;base64,' . \base64_encode(UtilsHelper::getUtilsIcons('menuIcon')); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
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
		$formId = isset($_GET['formId']) ? \sanitize_text_field(\wp_unslash($_GET['formId'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$output = [];

		switch ($type) {
			case 'locations':
				$items = UtilsGeneralHelper::getBlockLocations($formId);
				$count = \count($items);
				$formTitle = \get_the_title((int) $formId);

				$output = [
					// Translators: %s is the form title.
					'adminListingPageTitle' => $this->getMultilangTitle(\sprintf(\__('Locations where your "%s" form is used', 'eightshift-forms'), $formTitle)),
					// Translators: %s is the number of locations.
					'adminListingPageSubTitle' => $count === 1 ? \__('Showing 1 form location.', 'eightshift-forms') : \sprintf(\__('Showing %s form locations.', 'eightshift-forms'), $count),
				];
				break;
			case 'entries':
				$items = EntriesHelper::getEntries($formId);
				$count = \count($items);
				$formTitle = \get_the_title((int) $formId);

				$output = [
					// Translators: %s is the form title.
					'adminListingPageTitle' => $this->getMultilangTitle(\sprintf(\__('Entries for %s form', 'eightshift-forms'), $formTitle)),
					// Translators: %s is the number of entries.
					'adminListingPageSubTitle' => $count === 1 ? \__('Showing 1 form entry.', 'eightshift-forms') : \sprintf(\__('Showing %s form entries.', 'eightshift-forms'), $count),
				];
				break;
			case 'trash':
				$items = $this->formsListing->getFormsList($type);
				$count = \count($items);

				$output = [
					// Translators: %s is the form title.
					'adminListingPageTitle' => $this->getMultilangTitle(\__('Deleted forms', 'eightshift-forms')),
					// Translators: %s is the number of trashed forms.
					'adminListingPageSubTitle' => $count === 1 ? \__('Showing 1 trashed form.', 'eightshift-forms') : \sprintf(\__('Showing %s trashed forms.', 'eightshift-forms'), $count),
				];
				break;
			default:
				$items = $this->formsListing->getFormsList($type);
				$count = \count($items);

				$output = [
					'adminListingPageTitle' => $this->getMultilangTitle(\__('All Forms', 'eightshift-forms')),
					// Translators: %s is the number of forms.
					'adminListingPageSubTitle' => $count === 1 ? \__('Showing 1 form.', 'eightshift-forms') : \sprintf(\__('Showing %s forms.', 'eightshift-forms'), $count),
				];
				break;
		}

		return \array_merge(
			$output,
			[
				'adminListingShowNoItems' => $count === 0,
				'adminListingItems' => $this->getListingItems($items, $type),
				'adminListingTopItems' => $this->getTopBarItems($type, $formId),
				'adminListingNoItems' => $this->getNoItemsMessage($type),
			]
		);
	}

	/**
	 * Get multilanguage title depending on the settings flag.
	 *
	 * @param string $title Title to be translated.
	 *
	 * @return string
	 */
	private function getMultilangTitle(string $title): string
	{
		$useWpml = \apply_filters(SettingsWpml::FILTER_SETTINGS_IS_VALID_NAME, []);
		if ($useWpml) {
			$lang = \apply_filters('wpml_current_language', '');
			if ($lang) {
				$title = $title . ' - ' . \strtoupper($lang);
			}
		}

		return $title;
	}

	/**
	 * Get no items message output.
	 *
	 * @param string $type Type of the listing.
	 *
	 * @return array<int, string>
	 */
	private function getNoItemsMessage(string $type): array
	{
		$newUrl = UtilsGeneralHelper::getNewFormPageUrl();
		$listingUrl = UtilsGeneralHelper::getListingPageUrl();

		switch ($type) {
			case 'locations':
				$output = [
					Components::render('highlighted-content', [
						'highlightedContentTitle' => \__('Location list is empty', 'eightshift-forms'),
						// Translators: %s is the link to the forms listing page.
						'highlightedContentSubtitle' => \sprintf(\__('
							Your form is not assigned to any location.<br />
							<br /><a class="es-submit es-submit--outline" href="%s">Go to your forms</a>', 'eightshift-forms'), $listingUrl),
						'highlightedContentIcon' => 'emptyStateLocations',
					]),
				];
				break;
			case 'trash':
				$output = [
					Components::render('highlighted-content', [
						'highlightedContentTitle' => \__('Trash list is empty', 'eightshift-forms'),
						// Translators: %s is the link to the forms listing page.
						'highlightedContentSubtitle' => \sprintf(\__('
							Your don\'t have any form in trash.<br />
							<br /><a class="es-submit es-submit--outline" href="%s">Go to your forms</a>', 'eightshift-forms'), $listingUrl),
						'highlightedContentIcon' => 'emptyStateTrash',
					]),
				];
				break;
			case 'entries':
				$output = [
					Components::render('highlighted-content', [
						'highlightedContentTitle' => \__('Entrie list is empty', 'eightshift-forms'),
						// Translators: %s is the link to the forms listing page.
						'highlightedContentSubtitle' => \sprintf(\__('
							You don\'t have any form entries on this form.<br />
							<br /><a class="es-submit es-submit--outline" href="%s">Go to your forms</a>', 'eightshift-forms'), $listingUrl),
						'highlightedContentIcon' => 'emptyStateEntries',
					]),
				];
				break;
			default:
				$output = [
					Components::render('highlighted-content', [
						'highlightedContentTitle' => \__('You have no forms', 'eightshift-forms'),
						// Translators: %s is the link to the forms listing page.
						'highlightedContentSubtitle' => \sprintf(\__('
							You don\'t have any forms to show.<br />
							<br /><a class="es-submit es-submit--outline" href="%s">Add your first form</a>', 'eightshift-forms'), $newUrl),
						'highlightedContentIcon' => 'emptyStateFormList',
					]),
				];
				break;
		}

		return $output;
	}

	/**
	 * Get top bar items.
	 *
	 * @param string $type Type of the listing.
	 * @param string $formId Form ID.
	 *
	 * @return array<string, mixed>
	 */
	private function getTopBarItems(string $type, string $formId): array
	{
		$bulkSelector = UtilsHelper::getStateSelectorAdmin('listingBulk');
		$filterSelector = UtilsHelper::getStateSelectorAdmin('listingFilter');
		$exportSelector = UtilsHelper::getStateSelectorAdmin('listingExport');
		$selectAllSelector = UtilsHelper::getStateSelectorAdmin('listingSelectAll');

		$left = [];
		$right = [];

		switch ($type) {
			case 'locations':
				$left = [
					Components::render('submit', [
						'submitVariant' => 'ghost',
						'submitButtonAsLink' => true,
						'submitButtonAsLinkUrl' => UtilsGeneralHelper::getListingPageUrl(),
						'submitValue' => \__('Back', 'eightshift-forms'),
						'submitIcon' => UtilsHelper::getUtilsIcons('arrowLeft')
					]),
				];
				break;
			case 'entries':
				$left = [
					Components::render('checkbox', [
						'checkboxValue' => 'all',
						'checkboxName' => 'all',
						'additionalClass' => $selectAllSelector,
					]),
					Components::render('submit', [
						'submitVariant' => 'ghost',
						'submitButtonAsLink' => true,
						'submitButtonAsLinkUrl' => UtilsGeneralHelper::getListingPageUrl(),
						'submitValue' => \__('Back', 'eightshift-forms'),
						'submitIcon' => UtilsHelper::getUtilsIcons('arrowLeft')
					]),
					Components::render('submit', [
						'submitVariant' => 'ghost',
						'submitValue' => \__('Delete', 'eightshift-forms'),
						'submitIsDisabled' => true,
						'additionalClass' => $bulkSelector,
						'submitAttrs' => [
							UtilsHelper::getStateAttribute('bulkType') => 'delete-entry',
						],
					]),
					Components::render('submit', [
						'submitVariant' => 'ghost',
						'submitValue' => \__('Duplicate', 'eightshift-forms'),
						'submitIsDisabled' => true,
						'additionalClass' => $bulkSelector,
						'submitAttrs' => [
							UtilsHelper::getStateAttribute('bulkType') => 'duplicate-entry',
						],
					]),
				];

				$right = [
					Components::render('submit', [
						'submitVariant' => 'ghost',
						'submitValue' => \__('Export to CSV', 'eightshift-forms'),
						'submitIsDisabled' => true,
						'additionalClass' => "{$exportSelector} {$bulkSelector}",
						'submitAttrs' => [
							UtilsHelper::getStateAttribute('bulkType') => 'fake',
							UtilsHelper::getStateAttribute('formId') => $formId,
						],
					]),
				];
				break;
			case 'trash':
				$left = [
					Components::render('checkbox', [
						'checkboxValue' => 'all',
						'checkboxName' => 'all',
						'additionalClass' => $selectAllSelector,
					]),
					Components::render('submit', [
						'submitVariant' => 'ghost',
						'submitButtonAsLink' => true,
						'submitButtonAsLinkUrl' => UtilsGeneralHelper::getListingPageUrl(),
						'submitValue' => \__('Back', 'eightshift-forms'),
						'submitIcon' => UtilsHelper::getUtilsIcons('arrowLeft')
					]),
				];

				$right = [
					Components::render('submit', [
						'submitVariant' => 'ghost',
						'submitValue' => \__('Restore', 'eightshift-forms'),
						'submitIsDisabled' => true,
						'additionalClass' => $bulkSelector,
						'submitAttrs' => [
							UtilsHelper::getStateAttribute('bulkType') => 'restore',
						],
					]),
					Components::render('submit', [
						'submitVariant' => 'ghost',
						'submitValue' => \__('Delete permanently', 'eightshift-forms'),
						'submitIsDisabled' => true,
						'additionalClass' => $bulkSelector,
						'submitAttrs' => [
							UtilsHelper::getStateAttribute('bulkType') => 'delete-perminentely',
						],
					]),
				];
				break;
			default:
				$left = [
					Components::render('checkbox', [
						'checkboxValue' => 'all',
						'checkboxName' => 'all',
						'additionalClass' => $selectAllSelector,
					]),
					Components::render('select', [
						'fieldSkip' => true,
						'selectName' => 'filter',
						'selectContent' => $this->getFilterOptions(),
						'selectPlaceholder' => \__('Show all', 'eightshift-forms'),
						'additionalClass' => $filterSelector,
					]),
					Components::render('submit', [
						'submitVariant' => 'ghost',
						'submitValue' => \__('Delete', 'eightshift-forms'),
						'submitIsDisabled' => true,
						'additionalClass' => $bulkSelector,
						'submitAttrs' => [
							UtilsHelper::getStateAttribute('bulkType') => 'delete',
						],
					]),
					Components::render('submit', [
						'submitVariant' => 'ghost',
						'submitValue' => \__('Sync', 'eightshift-forms'),
						'submitIsDisabled' => true,
						'additionalClass' => $bulkSelector,
						'submitAttrs' => [
							UtilsHelper::getStateAttribute('bulkType') => 'sync',
						],
					]),
					Components::render('submit', [
						'submitVariant' => 'ghost',
						'submitValue' => \__('Duplicate', 'eightshift-forms'),
						'submitIsDisabled' => true,
						'additionalClass' => $bulkSelector,
						'submitAttrs' => [
							UtilsHelper::getStateAttribute('bulkType') => 'duplicate',
						],
					]),
				];

				$right = [
					Components::render('submit', [
						'submitVariant' => 'outline',
						'submitButtonAsLink' => true,
						'submitButtonAsLinkUrl' => UtilsGeneralHelper::getFormsTrashPageUrl(),
						'submitValue' => \__('Trashed', 'eightshift-forms'),
					]),
					Components::render('submit', [
						'submitButtonAsLink' => true,
						'submitButtonAsLinkUrl' => UtilsGeneralHelper::getNewFormPageUrl(),
						'submitValue' => \__('Create', 'eightshift-forms'),
						'submitIcon' => UtilsHelper::getUtilsIcons('addHighContrast')
					]),
				];
				break;
		}

		return [
			'left' => Components::ensureString($left),
			'right' => Components::ensureString($right),
		];
	}

	/**
	 * Get listing items.
	 *
	 * @param array<string, mixed> $items Items to be rendered.
	 * @param string $type Type of the listing.
	 *
	 * @return array<mixed>
	 */
	private function getListingItems(array $items, string $type): array
	{
		$output = [];
		$isDevMode = UtilsDeveloperHelper::isDeveloperModeActive();

		switch ($type) {
			case 'locations':
				foreach ($items as $item) {
					$id = $item['id'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$postType = $item['postType'] ?? '';
					$editLink = $item['editLink'] ?? '';

					$title = \get_the_title($id);

					$output[] = Components::render('card-inline', [
						// Translators: %1$s is the post type, %2$s is the post title.
						'cardInlineTitle' => \sprintf(\__('%1$s - %2$s', 'eightshift-forms'), \ucfirst($postType), $title) . ($isDevMode ? " ({$id})" : ''),
						'cardInlineTitleLink' => $editLink,
						'cardInlineSubTitle' => \implode(', ', $this->getSubtitle($item)),
						'cardInlineUseHover' => true,
						'cardInlineIcon' => UtilsHelper::getUtilsIcons('post'),
						'cardInlineLeftContent' => Components::ensureString($this->getLeftContent($item)),
						'cardInlineRightContent' => Components::ensureString($this->getRightContent($item, $type)),
					]);
				}
				break;
			case 'entries':
				$i = 0;
				$count = \count($items);
				foreach (\array_reverse($items) as $item) {
					$id = $item['id'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$entryValue = $item['entryValue'] ?? [];
					$createdAt = $item['createdAt'] ?? '';

					$content = '<ul class="is-list">';
					foreach ($entryValue as $entryKey => $entryValue) {
						if (\gettype($entryValue) === 'array') {
							if (\array_key_first($entryValue) === 0) {
								$entryValue = \implode(UtilsConfig::DELIMITER, $entryValue);
							} else {
								$entryValue = \array_map(
									function ($value, $key) {
										return "{$key}={$value}";
									},
									$entryValue,
									\array_keys($entryValue)
								);
								$entryValue = \implode(UtilsConfig::DELIMITER, $entryValue);
							}
						}

						$content .= "<li><strong>{$entryKey}</strong>: {$entryValue}</li>";
					}
					$content .= '</ul>';

					$output[] = Components::render('card-inline', [
						// Translators: %s is the entry ID.
						'cardInlineTitle' => \sprintf(\__('Entry %s', 'eightshift-forms'), $id),
						'cardInlineSubTitle' => $createdAt,
						'cardInlineIcon' => UtilsHelper::getUtilsIcons('post'),
						'cardInlineLeftContent' => Components::ensureString($this->getLeftContent($item)),
						'cardInlineContent' => $content,
						'cardInlineUseDivider' => true,
						'cardInlineLastItem' => $i === $count - 1,
						'additionalAttributes' => [
							UtilsHelper::getStateAttribute('bulkId') => $id,
						],
						'additionalClass' => Components::classnames([
							UtilsHelper::getStateSelectorAdmin('listingItem'),
						]),
					]);

					$i++;
				}
				break;
			default:
				foreach ($items as $item) {
					$id = $item['id'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$title = $item['title'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$editLink = $item['editLink'] ?? '#';
					$postType = $item['postType'] ?? '';
					$activeIntegration = $item['activeIntegration'] ?? [];
					$cardIcon = isset($activeIntegration['icon']) ? $activeIntegration['icon'] : UtilsHelper::getUtilsIcons('listingGeneric'); // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

					if (!$title) {
						// Translators: %s is the form ID.
						$title = \sprintf(\__('Form %s', 'eightshift-forms'), $id);
					}

					$isValid = $this->isIntegrationValid($item);

					$output[] = Components::render('card-inline', [
						'cardInlineTitle' => $title . ($isDevMode ? " ({$id})" : ''),
						'cardInlineTitleLink' => $editLink,
						'cardInlineSubTitle' => \implode(', ', $this->getSubtitle($item)),
						'cardInlineIcon' => $cardIcon,
						'cardInlineLeftContent' => Components::ensureString($this->getLeftContent($item)),
						'cardInlineRightContent' => Components::ensureString($this->getRightContent($item, $type)),
						'cardInlineInvalid' => !$isValid,
						'cardInlineUseHover' => true,
						'additionalAttributes' => [
							UtilsHelper::getStateAttribute('adminIntegrationType') => $isValid ? $activeIntegration['value'] : FormAdminMenu::ADMIN_MENU_FILTER_NOT_CONFIGURED,
							UtilsHelper::getStateAttribute('bulkId') => $id,
						],
						'additionalClass' => Components::classnames([
							UtilsHelper::getStateSelectorAdmin('listingItem'),
						]),
					]);
				}
				break;
		}

		return $output;
	}

	/**
	 * Get is integration valid.
	 *
	 * @param array<string, mixed> $item Item to be checked.
	 *
	 * @return boolean
	 */
	private function isIntegrationValid(array $item): bool
	{
		$isActive = $item['activeIntegration']['isActive'] ?? false;
		$isValid = $item['activeIntegration']['isValid'] ?? false;
		$isApiValid = $item['activeIntegration']['isApiValid'] ?? false;

		return $isActive && $isValid && $isApiValid;
	}

	/**
	 * Get subtitle.
	 *
	 * @param array<string, mixed> $item Item to be checked.
	 *
	 * @return array<string>
	 */
	private function getSubtitle(array $item): array
	{
		$output = [];

		$status = $item['status'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$postType = $item['postType'] ?? '';
		$isActive = $item['activeIntegration']['isActive'] ?? false;
		$isValid = $item['activeIntegration']['isValid'] ?? false;
		$isApiValid = $item['activeIntegration']['isApiValid'] ?? false;

		if ($status !== 'publish') {
			$output[] = \ucfirst($status);

			if ($postType) {
				$output[] = \ucfirst($postType);
			}
		}

		if (!$isActive) {
			$output[] = '<span class="error-text">' . \esc_html__('Integration not enabled', 'eightshift-forms') . '</span>';
		}

		if (!$isValid) {
			$output[] = '<span class="error-text">' . \esc_html__('Form configuration not valid', 'eightshift-forms') . '</span>';
		}

		if (!$isApiValid) {
			$output[] = '<span class="error-text">' . \esc_html__('Missing form fields', 'eightshift-forms') . '</span>';
		}

		return $output;
	}

	/**
	 * Get left content.
	 *
	 * @param array<string, mixed> $item Item to be checked.
	 *
	 * @return array<mixed>
	 */
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

	/**
	 * Get right content.
	 *
	 * @param array<string, mixed> $item Item to be checked.
	 * @param string $type Type of the listing.
	 *
	 * @return array<mixed>
	 */
	private function getRightContent(array $item, string $type): array
	{
		$formId = $item['id'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		switch ($type) {
			case 'locations':
				$output = [
					Components::render('submit', [
						'submitVariant' => 'ghost',
						'submitButtonAsLink' => true,
						'submitButtonAsLinkUrl' => $item['viewLink'] ?? '',
						'submitValue' => \__('View', 'eightshift-forms'),
					]),
				];
				break;
			case 'entries':
				$output = [];
				break;
			default:
				$entriesCount = EntriesHelper::getEntriesCount((string) $formId);

				$output = [
					Components::render('submit', [
						'submitVariant' => 'ghost',
						'submitValue' => \__('Locations', 'eightshift-forms'),
						'submitAttrs' => [
							UtilsHelper::getStateAttribute('locationsId') => $formId
						],
						'additionalClass' => UtilsHelper::getStateSelectorAdmin('listingLocations'),
					]),
					($entriesCount > 0) ?
					Components::render('submit', [
						'submitVariant' => 'ghost',
						'submitButtonAsLink' => true,
						'submitButtonAsLinkUrl' => $item['entriesLink'] ?? '',
						// Translators: %s is the number of entries.
						'submitValue' => \sprintf(\__('Entries (%s)', 'eightshift-forms'), $entriesCount),
					]) : null,
					Components::render('submit', [
						'submitVariant' => 'ghost',
						'submitButtonAsLink' => true,
						'submitButtonAsLinkUrl' => $item['settingsLink'] ?? '',
						'submitValue' => \__('Settings', 'eightshift-forms'),
					]),
				];
				break;
		}

		return $output;
	}

	/**
	 * Get filter options.
	 *
	 * @return string
	 */
	private function getFilterOptions(): string
	{
		$filterOptions = Components::render(
			'select-option',
			[
				'selectOptionLabel' => \__('Not Configured', 'eightshift-forms'),
				'selectOptionValue' => self::ADMIN_MENU_FILTER_NOT_CONFIGURED,
			]
		);

		$activeIntegration = \array_flip(UtilsIntegrationsHelper::getActiveIntegrations());

		foreach (\apply_filters(UtilsConfig::FILTER_SETTINGS_DATA, []) as $key => $value) {
			$type = $value['type'] ?? '';

			if ($type !== UtilsConfig::SETTINGS_INTERNAL_TYPE_INTEGRATION) {
				continue;
			}

			if (!isset($activeIntegration[$key])) {
				continue;
			}

			$filterOptions .= Components::render(
				'select-option',
				[
					'selectOptionLabel' => $value['labels']['title'] ?? '',
					'selectOptionValue' => $key,
				]
			);
		}

		return $filterOptions;
	}
}
