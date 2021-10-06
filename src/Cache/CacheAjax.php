<?php

/**
 * Class that holds transient cache ajax.
 *
 * @package EightshiftForms\Cache
 */

declare(strict_types=1);

namespace EightshiftForms\Cache;

use EightshiftForms\AdminMenus\FormGlobalSettingsAdminSubMenu;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * CacheAjax class.
 */
class CacheAjax implements ServiceInterface
{

	/**
	 * Ajax action name for deleting transient cache.
	 *
	 * @var string
	 */
	public const CACHE_AJAX_DELETE_ACTION = 'es_forms_ajax_delete_cache';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		add_action('wp_ajax_' . static::CACHE_AJAX_DELETE_ACTION, [$this, 'deleteTransient']);
	}

	/**
	 * Delete transient based on the type.
	 *
	 * @return void
	 */
	public function deleteTransient(): void
	{
		if (! current_user_can(FormGlobalSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY)) {
			wp_send_json_error(
				[
					'msg' => esc_html__('Error! You don\'t have enough permissions to perform this action!', 'eightshift-forms')
				],
				400
			);
		}

		$type = isset($_POST['type']) ? sanitize_key($_POST['type']) : '';
		$label = isset($_POST['label']) ? sanitize_key($_POST['label']) : '';

		if (empty($type)) {
			wp_send_json_error(
				[
					'msg' => esc_html__('Type not provided!', 'eightshift-forms')
				],
				400
			);
		}

		$response = delete_transient($type);

		if (!$response) {
			wp_send_json_success(
				[
					/* translators: %s will be replaced with cache label (string). */
					'msg' => sprintf(esc_html__('Error in deleting %s transient cache. Ether cache key is wrong or cache is not built in the database!', 'eightshift-forms'), $label)
				],
				200
			);
		}

		wp_send_json_success(
			[
				/* translators: %s will be replaced with cache label (string). */
				'msg' => sprintf(esc_html__('Successfully deleted %s transient cache.', 'eightshift-forms'), $label)
			],
			200
		);
	}
}
