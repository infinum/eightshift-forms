<?php

/**
 * Greenhouse Settings Options class.
 *
 * @package EightshiftForms\Integrations\Greenhouse
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Greenhouse;

use EightshiftForms\Helpers\TraitHelper;
use EightshiftForms\Settings\Settings\SettingsTypeInterface;
use EightshiftFormsPluginVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Greenhouse integration class.
 */
class SettingsGreenhouse implements SettingsTypeInterface, ServiceInterface
{
	/**
	 * Use General helper trait.
	 */
	use TraitHelper;

	// Filter name key.
	public const FILTER_NAME = 'esforms_settings_greenhouse';

	// Settings key.
	public const TYPE_KEY = 'greenhouse';

	// Use keys.
	public const GREENHOUSE_USE_KEY = 'greenhouseUse';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_NAME, [$this, 'getSettingsTypeData']);
	}

	/**
	 * Get Form options array
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array
	 */
	public function getSettingsTypeData(string $formId): array
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
