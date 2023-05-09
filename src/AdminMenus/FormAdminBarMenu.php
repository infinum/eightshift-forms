<?php

/**
 * Class that holds class for admin bar menu.
 *
 * @package EightshiftForms\AdminMenus
 */

declare(strict_types=1);

namespace EightshiftForms\AdminMenus;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Config\Config;
use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\Listing\FormListingInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;
use WP_Admin_Bar;

/**
 * FormAdminBarMenu class.
 */
class FormAdminBarMenu implements ServiceInterface
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
		if (!\current_user_can(Forms::POST_CAPABILITY_TYPE)) {
			return;
		}

		$prefix = FormAdminMenu::ADMIN_MENU_SLUG;
		$isDevelopMode = $this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_DEVELOPER_MODE_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY);

		$version = Config::getProjectVersion();

		$adminBar->add_menu(
			[
				'id' => $prefix,
				'parent' => null,
				'group'  => null,
				'title' => \esc_html__('Eightshift Forms', 'eightshift-forms'),
				'href'  => Helper::getListingPageUrl(),
			],
		);

		$listingPrefix = "{$prefix}-listing";
		$adminBar->add_menu(
			[
				'id' => $listingPrefix,
				'parent' => $prefix,
				'title' => \esc_html__('View all forms', 'eightshift-forms'),
				'href'  => Helper::getListingPageUrl(),
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
						'href'  => $url,
					],
				);

				$adminBar->add_menu(
					[
						'id' => "{$listingPrefix}-{$id}-edit",
						'parent' => $link,
						'title' => \esc_html__('Edit form', 'eightshift-forms'),
						'href'  => $url,
					],
				);
				$adminBar->add_menu(
					[
						'id' => "{$listingPrefix}-{$id}-settings",
						'parent' => $link,
						'title' => \esc_html__('Settings', 'eightshift-forms'),
						'href'  => $item['settingsLink'] ?? null,
					],
				);
				$adminBar->add_menu(
					[
						'id' => "{$listingPrefix}-{$id}-locations",
						'parent' => $link,
						'title' => \esc_html__('Locations', 'eightshift-forms'),
						'href'  => $item['settingsLocationLink'] ?? null,
					],
				);
			}
		}

		$adminBar->add_menu(
			[
				'id' => "{$prefix}-new-form",
				'parent' => $prefix,
				'title' => \esc_html__('Add new form', 'eightshift-forms'),
				'href'  => Helper::getNewFormPageUrl(),
			],
		);

		if (\current_user_can(FormGlobalSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY)) {
			$adminBar->add_menu(
				[
					'id' => "{$prefix}-global-settings",
					'parent' => $prefix,
					'title' => \esc_html__('Global settings', 'eightshift-forms'),
					'href'  => Helper::getSettingsGlobalPageUrl(),
				],
			);

			$troubleshootingPrefix = "{$prefix}-troubleshooting";
			$adminBar->add_menu(
				[
					'id' => $troubleshootingPrefix,
					'parent' => $prefix,
					'title' => \esc_html__('Troubleshooting', 'eightshift-forms'),
					'href'  => null,
				],
			);

			$adminBar->add_menu(
				[
					'id' => "{$troubleshootingPrefix}-cache",
					'parent' => $troubleshootingPrefix,
					'title' => \esc_html__('Clear cache', 'eightshift-forms'),
					'href'  => Helper::getSettingsGlobalPageUrl(SettingsCache::SETTINGS_TYPE_KEY),
				],
			);

			$adminBar->add_menu(
				[
					'id' => "{$troubleshootingPrefix}-debug",
					'parent' => $troubleshootingPrefix,
					'title' => \esc_html__('Debug', 'eightshift-forms'),
					'href'  => Helper::getSettingsGlobalPageUrl(SettingsDebug::SETTINGS_TYPE_KEY),
				],
			);

			$adminBar->add_menu(
				[
					'id' => "{$troubleshootingPrefix}-version",
					'parent' => $troubleshootingPrefix,
					// Translators: %s is the plugin version number.
					'title' => \sprintf(\esc_html__('Version: %s', 'eightshift-forms'), \esc_html($version)),
					'href'  => null,
				],
			);
		}
	}
}
