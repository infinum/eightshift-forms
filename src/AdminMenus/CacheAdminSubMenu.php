<?php

/**
 * File that holds class for admin sub menu example.
 *
 * @package EightshiftForms\AdminMenus
 */

declare(strict_types=1);

namespace EightshiftForms\AdminMenus;

use EightshiftForms\Cache\TransientCacheAjax;
use EightshiftForms\Helpers\Components;
use EightshiftForms\Integrations\Greenhouse\Greenhouse;
use EightshiftForms\Integrations\Mailchimp\Mailchimp;
use EightshiftLibs\AdminMenus\AbstractAdminSubMenu;

/**
 * CacheAdminSubMenu class.
 */
class CacheAdminSubMenu extends AbstractAdminSubMenu
{
	/**
	 * Capability for this admin sub menu
	 *
	 * @var string
	 */
	public const ADMIN_MENU_CAPABILITY = 'eightshift-form-cache';

	/**
	 * Menu slug for this admin sub menu
	 *
	 * @var string
	 */
	public const ADMIN_MENU_SLUG = 'cache';

	/**
	 * Parent menu slug for this admin sub menu
	 *
	 * @var string
	 */
	public const PARENT_MENU_SLUG = 'edit.php?post_type=eightshift-forms';

	/**
	 * Get the title to use for the admin page.
	 *
	 * @return string The text to be displayed in the title tags of the page when the menu is selected.
	 */
	protected function getTitle(): string
	{
		return \esc_html__('Cache', 'eightshift-forms');
	}

	/**
	 * Get the menu title to use for the admin menu.
	 *
	 * @return string The text to be used for the menu.
	 */
	protected function getMenuTitle(): string
	{
		return \esc_html__('Cache', 'eightshift-forms');
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
		return  'cache-settings';
	}

	/**
	 * Render the current view.
	 *
	 * @param array<string, mixed>  $attributes Array of attributes passed to the view.
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
			'cacheSettingsPageTitle' => \esc_html__('Cache', 'eightshift-forms'),
			'cacheSettingsSubTitle' => \esc_html__('This subpage is used for all integrations if they are using transient caching of data.', 'eightshift-forms'),
			'cacheSettingsAjaxAction' => TransientCacheAjax::TRANSIENT_CACHE_AJAX_DELETE_ACTION,
			'cacheSettingsTypes' => [
				[
					'name' => Greenhouse::CACHE_JOBS,
					'label' => __('Greenhouse', 'eightshift-form'),
					/* translators: %s will be replaced with cache lifespan (string). */
					'desc' => sprintf(__('Cache stores Greenhouse jobs and questions data used to list forms, jobs, and questions both on frontend and in editor. Life span of this cache is %s hours.', 'eightshift-form'), \esc_html(gmdate("H:i:s", Greenhouse::CACHE_JOBS_LIFESPAN))),
				],
				[
					'name' => Mailchimp::CACHE_LISTS,
					'label' => __('Mailchimp', 'eightshift-form'),
					/* translators: %s will be replaced with cache lifespan (string). */
					'desc' => sprintf(__('Cache stores mailchimp lists both on frontend and in editor. Life span of this cache is %s min.', 'eightshift-form'), \esc_html(gmdate("i:s", Mailchimp::CACHE_LIST_TIMEOUT))),
				],
			]
		];
	}
}
