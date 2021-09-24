<?php

/**
 * Mailchimp Settings Options class.
 *
 * @package EightshiftForms\Integrations\Mailchimp
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailchimp;

use EightshiftForms\Helpers\TraitHelper;
use EightshiftForms\Hooks\Variables;
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
	public const MAILCHIMP_API_KEY_KEY = 'mailchimpApiKey';
	public const MAILCHIMP_FORM_URL_KEY = 'mailchimpFormUrl';

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
		$apiKey = Variables::getApiKeyMailchimp();

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
				[
					'component' => 'input',
					'inputName' => self::MAILCHIMP_API_KEY_KEY,
					'inputId' => self::MAILCHIMP_API_KEY_KEY,
					'inputFieldLabel' => \__('API Key', 'eightshift-forms'),
					'inputFieldHelp' => \__('Open your Mailchimp account and provide API key. You can provide API key using global variable also.', 'eightshift-forms'),
					'inputType' => 'text',
					'inputIsRequired' => true,
					'inputValue' => $apiKey,
					'inputIsReadOnly' => !empty($apiKey),
				],
				[
					'component' => 'input',
					'inputName' => self::MAILCHIMP_FORM_URL_KEY,
					'inputId' => self::MAILCHIMP_FORM_URL_KEY,
					'inputFieldLabel' => \__('Form Url', 'eightshift-forms'),
					'inputFieldHelp' => \__('Provide Signup form URL from your Mailchimp form builder.', 'eightshift-forms'),
					'inputType' => 'text',
					'inputIsRequired' => true
				],
			]
		];
	}
}
