<?php

/**
 * Mailchimp Settings Options class.
 *
 * @package EightshiftForms\Integrations\Mailchimp
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailchimp;

use EightshiftForms\Helpers\TraitHelper;
use EightshiftForms\Settings\Settings\SettingsTypeInterface;
use EightshiftFormsPluginVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Mailchimp integration class.
 */
class SettingsMailchimp implements SettingsTypeInterface, ServiceInterface
{
	/**
	 * Use General helper trait.
	 */
	use TraitHelper;

	// Filter name key.
	public const FILTER_NAME = 'esforms_settings_mailchimp';

	// Settings key.
	public const TYPE_KEY = 'mailchimp';

	// Use keys.
	public const MAILCHIMP_USE_KEY = 'mailchimpUse';

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
				'label' => __('Mailchimp', 'eightshift-forms'),
				'value' => self::TYPE_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			],
			'form' => [
				[
					'component' => 'intro',
					'introTitle' => \__('Mailchimp settings', 'eightshift-forms'),
					'introSubtitle' => \__('Configure your mailchimp settings in one place.', 'eightshift-forms'),
				],
			]
		];
	}
}
