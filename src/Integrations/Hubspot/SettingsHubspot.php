<?php

/**
 * Hubspot Settings Options class.
 *
 * @package EightshiftForms\Integrations\Hubspot
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Hubspot;

use EightshiftForms\Helpers\TraitHelper;
use EightshiftForms\Settings\Settings\SettingsTypeInterface;
use EightshiftFormsPluginVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Hubspot integration class.
 */
class SettingsHubspot implements SettingsTypeInterface, ServiceInterface
{
	/**
	 * Use General helper trait.
	 */
	use TraitHelper;

	// Filter name key.
	public const FILTER_NAME = 'esforms_settings_hubspot';

	// Settings key.
	public const TYPE_KEY = 'hubspot';

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
	 * @return array
	 */
	public function getSettingsTypeData(): array
	{
		return [
			'sidebar' => [
				'label' => __('Hubspot', 'eightshift-forms'),
				'value' => self::TYPE_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			],
			'form' => [
				[
					'component' => 'intro',
					'introTitle' => \__('Hubspot settings', 'eightshift-forms'),
					'introSubtitle' => \__('Configure your hubspot settings in one place.', 'eightshift-forms'),
				],
			]
		];
	}
}
