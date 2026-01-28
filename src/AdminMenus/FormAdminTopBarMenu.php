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
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Listing\FormListingInterface;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftForms\Helpers\DeveloperHelpers;
use EightshiftForms\Helpers\HooksHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
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
		if (GeneralHelpers::isBlockEditor()) {
			return;
		}

		// Bail early if user has no permission to see the menu.
		if (!\current_user_can(Forms::POST_CAPABILITY_TYPE)) {
			return;
		}

		$prefix = FormAdminMenu::ADMIN_MENU_SLUG;
		$isDevelopMode = DeveloperHelpers::isDeveloperModeActive();

		$mainLabel = \esc_html__('Forms', 'eightshift-forms');

		if (!$adminBar->get_node('eightshift')) {
			$adminBar->add_menu(
				[
					'id' => 'eightshift',
					'title' => \esc_html__('Eightshift', 'eightshift-forms'),
				],
			);
		}

		// Add main menu item.
		$adminBar->add_menu(
			[
				'id' => $prefix,
				'parent' => 'eightshift',
				'group' => '',
				'title' => $isDevelopMode ? $mainLabel . UtilsHelper::getUtilsIcons('warning') : $mainLabel,
				'href' => GeneralHelpers::getListingPageUrl(),
				'meta' => [
					'title' => $isDevelopMode ? \esc_html__('Debug tools are active!', 'eightshift-forms') : $mainLabel,
				]
			],
		);

		$listingPrefix = "{$prefix}-listing";
		$adminBar->add_menu(
			[
				'id' => $listingPrefix,
				'parent' => $prefix,
				'title' => \esc_html__('View all forms', 'eightshift-forms'),
				'href' => GeneralHelpers::getListingPageUrl(),
			],
		);

		$adminBar->add_menu(
			[
				'id' => "{$prefix}-new-form",
				'parent' => $prefix,
				'title' => \esc_html__('Add new form', 'eightshift-forms'),
				'href' => GeneralHelpers::getNewFormPageUrl(Forms::POST_TYPE_SLUG),
			],
		);

		if (\current_user_can(FormGlobalSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY)) {
			$adminBar->add_menu(
				[
					'id' => "{$prefix}-global-settings",
					'parent' => $prefix,
					'title' => \esc_html__('Global settings', 'eightshift-forms'),
					'href' => GeneralHelpers::getSettingsGlobalPageUrl(SettingsDashboard::SETTINGS_TYPE_KEY),
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
					'href' => GeneralHelpers::getSettingsGlobalPageUrl(SettingsCache::SETTINGS_TYPE_KEY),
				],
			);

			$adminBar->add_menu(
				[
					'id' => "{$troubleshootingPrefix}-debug",
					'parent' => $troubleshootingPrefix,
					'title' => \esc_html__('Debug', 'eightshift-forms'),
					'href' => GeneralHelpers::getSettingsGlobalPageUrl(SettingsDebug::SETTINGS_TYPE_KEY),
				],
			);

			$adminBar->add_menu(
				[
					'id' => "{$troubleshootingPrefix}-version",
					'parent' => $troubleshootingPrefix,
					// Translators: %s is the plugin version number.
					'title' => \sprintf(\esc_html__('Version: %s', 'eightshift-forms'), \esc_html(Helpers::getPluginVersion())),
					'href' => null,
				],
			);

			// Merge addon blocks to the list.
			$filterName = HooksHelpers::getFilterName(['admin', 'topBarMenu', 'items']);
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
