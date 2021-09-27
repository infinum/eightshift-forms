<?php

/**
 * General Settings class.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Helpers\TraitHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsGeneral class.
 */
class SettingsGeneral implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use TraitHelper;

	/**
	 * Filter name key.
	 */
	public const FILTER_NAME = 'es_forms_settings_general';

	/**
	 * Filter global name key.
	 */
	public const FILTER_GLOBAL_NAME = 'es_forms_settings_global_general';

	/**
	 * Settings key.
	 */
	public const TYPE_KEY = 'general';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_NAME, [$this, 'getSettingsData']);
		\add_filter(self::FILTER_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
	}

	/**
	 * Get Form settings data array
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array
	 */
	public function getSettingsData(string $formId): array
	{
		$output = [
			'sidebar' => [
				'label' => __('General', 'eightshift-forms'),
				'value' => self::TYPE_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			],
			'form' => []
		];

		return $output;
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array
	 */
	public function getSettingsGlobalData(): array
	{
		$output = [
			'sidebar' => [
				'label' => __('General', 'eightshift-forms'),
				'value' => self::TYPE_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			],
			'form' => []
		];

		return $output;
	}
}
