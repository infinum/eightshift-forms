<?php

/**
 * General Settings class.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Helpers\TraitHelper;
use EightshiftForms\Settings\GlobalSettings\SettingsGlobal;
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
			'checkboxesContent' => []
		];

		// Check all settings and build checkboxes.
		foreach (SettingsAll::SETTINGS as $key => $value) {
			if ($key === self::TYPE_KEY) {
				continue;
			}

			// Determin the correct setting name.
			$name = $this->getSettingsName("{$key}Use");

			// Check for saved data.
			$value = \get_post_meta($formId, $name, true);

			$items['checkboxesContent'][] = [
				'component' => 'checkbox',
				'checkboxName' => $name,
				'checkboxId' => $name,
				// translators: %s replaced with the key.
				'checkboxLabel' => sprintf(__('Use %s', 'eightshift-forms'), ucfirst($key)),
				'checkboxIsChecked' => !empty($value),
			];
		}

		// Merge checkboxes to the original output array.
		$output['form'] = array_merge($output['form'], [$items]);

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
			'checkboxesContent' => []
		];

		// Check all settings and build checkboxes.
		foreach (SettingsGlobal::SETTINGS as $key => $value) {
			if ($key === self::TYPE_KEY) {
				continue;
			}

			// Determin the correct setting name.
			$name = $this->getSettingsName("{$key}Use");

			// Check for saved data.
			$value = \get_option($name, true);

			$items['checkboxesContent'][] = [
				'component' => 'checkbox',
				'checkboxName' => $name,
				'checkboxId' => $name,
				// translators: %s replaced with the key.
				'checkboxLabel' => sprintf(__('Use %s', 'eightshift-forms'), ucfirst($key)),
				'checkboxIsChecked' => !empty($value),
			];
		}

		// Merge checkboxes to the original output array.
		$output['form'] = array_merge($output['form'], [$items]);

		return $output;
	}
}
