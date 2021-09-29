<?php

/**
 * General Settings class.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsGeneral class.
 */
class SettingsGeneral implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_general';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_general';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'general';

	/**
	 * Redirection Success key.
	 */
	public const SETTINGS_GENERAL_REDIRECTION_SUCCESS_KEY = 'generalRedirectionSuccess';

	/**
	 * Tracking event name key.
	 */
	public const SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY = 'generalTrackingEventName';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
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
				'value' => self::SETTINGS_TYPE_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			],
			'form' => [
				[
					'component' => 'input',
					'inputName' => $this->getSettingsName(self::SETTINGS_GENERAL_REDIRECTION_SUCCESS_KEY),
					'inputId' => $this->getSettingsName(self::SETTINGS_GENERAL_REDIRECTION_SUCCESS_KEY),
					'inputFieldLabel' => \__('Success Redirection Url', 'eightshift-forms'),
					'inputFieldHelp' => \__('Define url to redirect after the form is submitted with success.', 'eightshift-forms'),
					'inputType' => 'url',
					'inputIsUrl' => true,
					'inputValue' => $this->getSettingsValue(self::SETTINGS_GENERAL_REDIRECTION_SUCCESS_KEY, $formId),
				],
				[
					'component' => 'input',
					'inputName' => $this->getSettingsName(self::SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY),
					'inputId' => $this->getSettingsName(self::SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY),
					'inputFieldLabel' => \__('Tracking Event Name', 'eightshift-forms'),
					'inputFieldHelp' => \__('Define event name used to push data to GTM.', 'eightshift-forms'),
					'inputType' => 'text',
					'inputValue' => $this->getSettingsValue(self::SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY, $formId),
				],
			]
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
				'value' => self::SETTINGS_TYPE_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			],
			'form' => []
		];

		return $output;
	}
}
