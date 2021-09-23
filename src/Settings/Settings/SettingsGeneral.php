<?php

/**
 * General Settings Options class.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Helpers\TraitHelper;
use EightshiftFormsPluginVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Mailer integration class.
 */
class SettingsGeneral implements SettingsTypeInterface, ServiceInterface
{
	/**
	 * Use General helper trait.
	 */
	use TraitHelper;

	// Filter name key.
	public const FILTER_NAME = 'esforms_settings_general';

	// Settings key.
	public const TYPE_KEY = 'general';

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
		$output = [
			'isRequired' => true,
			'sidebar' => [
				'label' => __('General', 'eightshift-forms'),
				'value' => self::TYPE_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			],
			'form' => [
				[
					'component' => 'intro',
					'introTitle' => \__('Integrations setting', 'eightshift-forms'),
					'introSubtitle' => \__('Configure settings for all your integrations in one place.', 'eightshift-forms'),
				],
			]
		];

		$items = [
			'component' => 'checkboxes',
			'checkboxesFieldLabel' => \__('Check options to use', 'eightshift-forms'),
			'checkboxesFieldHelp' => \__('Select integrations you want to use in your form.', 'eightshift-forms'),
			'checkboxesContent' => [
			]
		];

		foreach (SettingsAll::SETTINGS as $key => $value) {
			if ($key === self::TYPE_KEY) {
				continue;
			}

			$name = "{$key}Use";

			$items['checkboxesContent'][] = [
				'component' => 'checkbox',
				'checkboxName' => $name,
				'checkboxId' => $name,
				'checkboxLabel' => sprintf(__('Use %s', 'eightshift-forms'), ucfirst($key)),
				'checkboxValue' => 'true',
			];
		}

		$output['form'] = array_merge($output['form'], [$items]);

		return $output;
	}
}
