<?php

/**
 * Greenhouse Settings class.
 *
 * @package EightshiftForms\Integrations\Greenhouse
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Greenhouse;

use EightshiftForms\Helpers\TraitHelper;
use EightshiftForms\Settings\Settings\SettingsDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsGreenhouse class.
 */
class SettingsGreenhouse implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use TraitHelper;

	/**
	 * Filter name key.
	 */
	public const FILTER_NAME = 'es_forms_settings_greenhouse';

	/**
	 * Settings key.
	 */
	public const TYPE_KEY = 'greenhouse';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_NAME, [$this, 'getSettingsData']);
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
		return [
			'sidebar' => [
				'label' => __('Greenhouse', 'eightshift-forms'),
				'value' => self::TYPE_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			],
			'form' => [
				[
					'component' => 'intro',
					'introTitle' => \__('Greenhouse settings', 'eightshift-forms'),
					'introSubtitle' => \__('Configure your greenhouse settings in one place.', 'eightshift-forms'),
				],
			]
		];
	}
}
