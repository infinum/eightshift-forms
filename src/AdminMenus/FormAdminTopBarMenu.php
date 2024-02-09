<?php

/**
 * Admin menu top bar functionality.
 *
 * @package EightshiftForms\AdminMenus
 */

declare(strict_types=1);

namespace EightshiftForms\AdminMenus;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Dashboard\SettingsDashboard;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftForms\Listing\FormListingInterface;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;
use WP_Admin_Bar;

/**
 * FormAdminTopBarMenu class.
 */
class FormAdminTopBarMenu implements ServiceInterface
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
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('admin_bar_menu', [$this, 'getTopBarMenu'], 500);
	}

	/**
	 * Add top bar menu form items.
	 *
	 * @param WP_Admin_Bar $adminBar Admin bar array.
	 *
	 * @return void
	 */
	public function getTopBarMenu(WP_Admin_Bar $adminBar): void
	{
		// Don't use in multisite.
		if (\is_network_admin()) {
			return;
		}

		// Bail early if it is used in block editor.
		if (UtilsGeneralHelper::isBlockEditor()) {
			return;
		}

		// Bail early if user has no permission to see the menu.
		if (!\current_user_can(Forms::POST_CAPABILITY_TYPE)) {
			return;
		}

		$prefix = FormAdminMenu::ADMIN_MENU_SLUG;
		$isDevelopMode = UtilsDeveloperHelper::isDeveloperModeActive();
		$isDevelopModeQmLog = UtilsDeveloperHelper::isDeveloperQMLogActive();

		$version = UtilsGeneralHelper::getProjectVersion();

		$mainLabel = \esc_html__('Eightshift Forms', 'eightshift-forms');

		// Add main menu item.
		$adminBar->add_menu(
			[
				'id' => $prefix,
				'parent' => null,
				'group' => null,
				'title' => ($isDevelopMode || $isDevelopModeQmLog) ? $mainLabel . UtilsHelper::getUtilsIcons('warning') : $mainLabel,
				'href' => UtilsGeneralHelper::getListingPageUrl(),
				'meta' => [
					'title' => ($isDevelopMode || $isDevelopModeQmLog) ? \esc_html__('Debug tools are active!', 'eightshift-forms') : $mainLabel,
				]
			],
		);

		$listingPrefix = "{$prefix}-listing";
		$adminBar->add_menu(
			[
				'id' => $listingPrefix,
				'parent' => $prefix,
				'title' => \esc_html__('View all forms', 'eightshift-forms'),
				'href' => UtilsGeneralHelper::getListingPageUrl(),
			],
		);

		$items = $this->formsListing->getFormsList('');

		if ($items) {
			foreach ($items as $item) {
				$id = $item['id'] ?? '';
				$title = $item['title'] ?? '';
				$url = $item['editLink'] ?? null;

				if (!$title) {
					// Translators: %s is the form ID.
					$title = \sprintf(\__('Form %s', 'eightshift-forms'), $id);
				}

				if ($isDevelopMode) {
					$title = "{$title} ($id)";
				}

				$link = "{$listingPrefix}-{$id}";

				$adminBar->add_menu(
					[
						'id' => "{$listingPrefix}-{$id}",
						'parent' => $listingPrefix,
						'title' => $title,
						'href' => $url,
					],
				);

				$adminBar->add_menu(
					[
						'id' => "{$listingPrefix}-{$id}-edit",
						'parent' => $link,
						'title' => \esc_html__('Edit form', 'eightshift-forms'),
						'href' => $url,
					],
				);
				$adminBar->add_menu(
					[
						'id' => "{$listingPrefix}-{$id}-settings",
						'parent' => $link,
						'title' => \esc_html__('Settings', 'eightshift-forms'),
						'href' => $item['settingsLink'] ?? null,
					],
				);
			}
		}

		$adminBar->add_menu(
			[
				'id' => "{$prefix}-new-form",
				'parent' => $prefix,
				'title' => \esc_html__('Add new form', 'eightshift-forms'),
				'href' => UtilsGeneralHelper::getNewFormPageUrl(),
			],
		);

		if (\current_user_can(FormGlobalSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY)) {
			$adminBar->add_menu(
				[
					'id' => "{$prefix}-global-settings",
					'parent' => $prefix,
					'title' => \esc_html__('Global settings', 'eightshift-forms'),
					'href' => UtilsGeneralHelper::getSettingsGlobalPageUrl(SettingsDashboard::SETTINGS_TYPE_KEY),
				],
			);

			$troubleshootingPrefix = "{$prefix}-troubleshooting";
			$adminBar->add_menu(
				[
					'id' => $troubleshootingPrefix,
					'parent' => $prefix,
					'title' => \esc_html__('Troubleshooting', 'eightshift-forms'),
					'href' => null,
				],
			);

			$adminBar->add_menu(
				[
					'id' => "{$troubleshootingPrefix}-cache",
					'parent' => $troubleshootingPrefix,
					'title' => \esc_html__('Clear cache', 'eightshift-forms'),
					'href' => UtilsGeneralHelper::getSettingsGlobalPageUrl(SettingsCache::SETTINGS_TYPE_KEY),
				],
			);

			$adminBar->add_menu(
				[
					'id' => "{$troubleshootingPrefix}-debug",
					'parent' => $troubleshootingPrefix,
					'title' => \esc_html__('Debug', 'eightshift-forms'),
					'href' => UtilsGeneralHelper::getSettingsGlobalPageUrl(SettingsDebug::SETTINGS_TYPE_KEY),
				],
			);

			$adminBar->add_menu(
				[
					'id' => "{$troubleshootingPrefix}-version",
					'parent' => $troubleshootingPrefix,
					// Translators: %s is the plugin version number.
					'title' => \sprintf(\esc_html__('Version: %s', 'eightshift-forms'), \esc_html($version)),
					'href' => null,
				],
			);

			// Merge addon blocks to the list.
			$filterName = UtilsHooksHelper::getFilterName(['admin', 'topBarMenu', 'items']);
			if (\has_filter($filterName)) {
				$addonsPrefix = "{$prefix}-addons";

				$filterItems = \apply_filters($filterName, [], $addonsPrefix);

				if ($filterItems) {
					$adminBar->add_menu(
						[
							'id' => $addonsPrefix,
							'parent' => $prefix,
							'title' => \esc_html__('Add-ons', 'eightshift-forms'),
							'href' => null,
						]
					);

					foreach ($filterItems as $filterItem) {
						$adminBar->add_menu($filterItem);
					}
				}
			}
		}
	}
}
