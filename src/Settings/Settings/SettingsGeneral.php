<?php

/**
 * General Settings Options class.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Helpers\TraitHelper;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
use EightshiftForms\Integrations\Mailchimp\SettingsMailchimp;
use EightshiftForms\Mailer\SettingsMailer;
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
	 * @param string $formId Form Id.
	 *
	 * @return array
	 */
	public function getSettingsTypeData(string $formId): array
	{
		return [
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
				[
					'component' => 'checkboxes',
					'checkboxesFieldLabel' => \__('Check options to use', 'eightshift-forms'),
					'checkboxesFieldHelp' => \__('Select integrations you want to use in your form.', 'eightshift-forms'),
					'checkboxesContent' => [
						[
							'component' => 'checkbox',
							'checkboxName' => SettingsMailer::MAILER_USE_KEY,
							'checkboxId' => SettingsMailer::MAILER_USE_KEY,
							'checkboxLabel' => \__('Use Mailer', 'eightshift-forms'),
							'checkboxValue' => 'true',
						],
						[
							'component' => 'checkbox',
							'checkboxName' => SettingsGreenhouse::GREENHOUSE_USE_KEY,
							'checkboxId' => SettingsGreenhouse::GREENHOUSE_USE_KEY,
							'checkboxLabel' => \__('Use Greenhouse', 'eightshift-forms'),
							'checkboxValue' => 'true',
						],
						[
							'component' => 'checkbox',
							'checkboxName' => SettingsMailchimp::MAILCHIMP_USE_KEY,
							'checkboxId' => SettingsMailchimp::MAILCHIMP_USE_KEY,
							'checkboxLabel' => \__('Use Mailchimp', 'eightshift-forms'),
							'checkboxValue' => 'true',
						],
						[
							'component' => 'checkbox',
							'checkboxName' => SettingsHubspot::HUBSPOT_USE_KEY,
							'checkboxId' => SettingsHubspot::HUBSPOT_USE_KEY,
							'checkboxLabel' => \__('Use Hubspot', 'eightshift-forms'),
							'checkboxValue' => 'true',
						],
					]
				],
			],
		];
	}
}
