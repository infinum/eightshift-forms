<?php

/**
 * Class that holds all settings for form.
 *
 * @package EightshiftLibs\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings;

use EightshiftForms\Labels\InterfaceLabels;

/**
 * FormOption class.
 */
class FormOption extends AbstractFormBuilder
{
	// Settings keys.
	public const SETTINGS_GENERAL_KEY = 'general';
	public const SETTINGS_MAILER_KEY = 'mailer';
	public const SETTINGS_VALIDATION_KEY = 'validation';
	public const SETTINGS_MAILCHIMP_KEY = 'mailchimp';
	public const SETTINGS_GREENHOUSE_KEY = 'greenhouse';
	public const SETTINGS_HUBSPOT_KEY = 'hubspot';

	// Mailer keys.
	public const MAILER_USE_KEY = 'mailerUse';
	public const MAILER_SENDER_NAME_KEY = 'mailerSenderName';
	public const MAILER_SENDER_EMAIL_KEY = 'mailerSenderEmail';
	public const MAILER_TO_KEY = 'mailerTo';
	public const MAILER_SUBJECT_KEY = 'mailerSubject';
	public const MAILER_TEMPLATE_KEY = 'mailerTemplate';

	// Greenhouse keys.
	public const GREENHOUSE_USE_KEY = 'greenhouseUse';

	// Mailchimp keys.
	public const MAILCHIMP_USE_KEY = 'mailchimpUse';

	// Hubspot keys.
	public const HUBSPOT_USE_KEY = 'hubspotUse';

	/**
	 * Instance variable of form labels data.
	 *
	 * @var InterfaceLabels
	 */
	protected $labels;

	/**
	 * Create a new instance.
	 *
	 * @param InterfaceLabels $labels Inject documentsData which holds form labels data.
	 */
	public function __construct(InterfaceLabels $labels)
	{
		$this->labels = $labels;
	}

	/**
	 * Set all settings page field keys.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return array
	 */
	public function getFormFields(string $formId): array
	{

		$output = [
			'sidebar' => [
				[
					'label' => __('General', 'eightshift-forms'),
					'value' => self::SETTINGS_GENERAL_KEY,
					'icon' => 'dashicons-admin-site-alt3',
				],
				[
					'label' => __('Validation', 'eightshift-forms'),
					'value' => self::SETTINGS_VALIDATION_KEY,
					'icon' => 'dashicons-admin-site-alt3',
				],
			],
			'forms' => [
				self::SETTINGS_GENERAL_KEY => $this->getGeneralFields(),
				self::SETTINGS_VALIDATION_KEY => $this->getValidationFields(),
			]
		];

		// Mailer options.
		$mailerUse = get_post_meta($formId, $this->getSettingsName(self::MAILER_USE_KEY), true);

		if ($mailerUse) {
			$output['sidebar'][] = [
				'label' => __('Mailer', 'eightshift-forms'),
				'value' => self::SETTINGS_MAILER_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			];

			$output['forms'][self::SETTINGS_MAILER_KEY] = $this->getMailerFields();
		}

		// Greenhouse options.
		$greenhouseUse = get_post_meta($formId, $this->getSettingsName(self::GREENHOUSE_USE_KEY), true);

		if ($greenhouseUse) {
			$output['sidebar'][] = [
				'label' => __('Greenhouse', 'eightshift-forms'),
				'value' => self::SETTINGS_GREENHOUSE_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			];

			$output['forms'][self::SETTINGS_GREENHOUSE_KEY] = $this->getGreenhouseFields();
		}

		// Mailchimp options.
		$mailchimpUse = get_post_meta($formId, $this->getSettingsName(self::MAILCHIMP_USE_KEY), true);

		if ($mailchimpUse) {
			$output['sidebar'][] = [
				'label' => __('Mailchimp', 'eightshift-forms'),
				'value' => self::SETTINGS_MAILCHIMP_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			];

			$output['forms'][self::SETTINGS_MAILCHIMP_KEY] = $this->getMailchimpFields();
		}

		// Hubspot options.
		$hubspotUse = get_post_meta($formId, $this->getSettingsName(self::HUBSPOT_USE_KEY), true);

		if ($hubspotUse) {
			$output['sidebar'][] = [
				'label' => __('Hubspot', 'eightshift-forms'),
				'value' => self::SETTINGS_HUBSPOT_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			];

			$output['forms'][self::SETTINGS_HUBSPOT_KEY] = $this->getHubspotFields();
		}

		return $output;
	}

	/**
	 * Get general fields.
	 *
	 * @return array
	 */
	public function getGeneralFields(): array
	{
		return [
			[
				'component' => 'intro',
				'introTitle' => \__('General setting', 'eightshift-forms'),
				'introSubtitle' => \__('Configure your form general settings in one place.', 'eightshift-forms'),
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => \__('Check options to use', 'eightshift-forms'),
				'checkboxesFieldHelp' => \__('Select integrations you want to use in your form.', 'eightshift-forms'),
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxName' => self::MAILER_USE_KEY,
						'checkboxId' => self::MAILER_USE_KEY,
						'checkboxLabel' => \__('Use Mailer', 'eightshift-forms'),
						'checkboxValue' => 'true',
					],
					[
						'component' => 'checkbox',
						'checkboxName' => self::GREENHOUSE_USE_KEY,
						'checkboxId' => self::GREENHOUSE_USE_KEY,
						'checkboxLabel' => \__('Use Greenhouse', 'eightshift-forms'),
						'checkboxValue' => 'true',
					],
					[
						'component' => 'checkbox',
						'checkboxName' => self::MAILCHIMP_USE_KEY,
						'checkboxId' => self::MAILCHIMP_USE_KEY,
						'checkboxLabel' => \__('Use Mailchimp', 'eightshift-forms'),
						'checkboxValue' => 'true',
					],
					[
						'component' => 'checkbox',
						'checkboxName' => self::HUBSPOT_USE_KEY,
						'checkboxId' => self::HUBSPOT_USE_KEY,
						'checkboxLabel' => \__('Use Hubspot', 'eightshift-forms'),
						'checkboxValue' => 'true',
					],
				]
			],
		];
	}

	/**
	 * Get mailer fields.
	 *
	 * @return array
	 */
	public function getMailerFields(): array
	{
		return [
			[
				'component' => 'intro',
				'introTitle' => \__('Mailing setting', 'eightshift-forms'),
				'introSubtitle' => \__('Configure your mailing settings in one place.', 'eightshift-forms'),
			],
			[
				'component' => 'input',
				'inputName' => self::MAILER_SENDER_NAME_KEY,
				'inputId' => self::MAILER_SENDER_NAME_KEY,
				'inputFieldLabel' => \__('Sender Name', 'eightshift-forms'),
				'inputFieldHelp' => \__('Define sender name showed in the email client.', 'eightshift-forms'),
				'inputType' => 'text',
				'inputIsRequired' => true,
			],
			[
				'component' => 'input',
				'inputName' => self::MAILER_SENDER_EMAIL_KEY,
				'inputId' => self::MAILER_SENDER_EMAIL_KEY,
				'inputFieldLabel' => \__('Sender Email', 'eightshift-forms'),
				'inputFieldHelp' => \__('Define sender email showed in the email client.', 'eightshift-forms'),
				'inputType' => 'email',
				'inputIsRequired' => true,
			],
			[
				'component' => 'input',
				'inputName' => self::MAILER_TO_KEY,
				'inputId' => self::MAILER_TO_KEY,
				'inputFieldLabel' => \__('Email to', 'eightshift-forms'),
				'inputFieldHelp' => \__('Define to what address the email will be sent', 'eightshift-forms'),
				'inputType' => 'email',
				'inputIsRequired' => true,
			],
			[
				'component' => 'input',
				'inputName' => self::MAILER_SUBJECT_KEY,
				'inputId' => self::MAILER_SUBJECT_KEY,
				'inputFieldLabel' => \__('Email subject', 'eightshift-forms'),
				'inputFieldHelp' => \__('Define email subject', 'eightshift-forms'),
				'inputType' => 'text',
				'inputIsRequired' => true,
			],
			[
				'component' => 'textarea',
				'textareaName' => self::MAILER_TEMPLATE_KEY,
				'textareaId' => self::MAILER_TEMPLATE_KEY,
				'textareaFieldLabel' => \__('Email template', 'eightshift-forms'),
				'textareaFieldHelp' => \__('Define email template', 'eightshift-forms'),
				'textareaIsRequired' => true,
			],
			[
				'component' => 'divider',
			],
		];
	}

	/**
	 * Get validation fields.
	 *
	 * @return array
	 */
	public function getValidationFields(): array
	{
		$output = [
			[
				'component' => 'intro',
				'introTitle' => \__('Form validation messages', 'eightshift-forms'),
				'introSubtitle' => \__('Configure your form validation messages in one place.', 'eightshift-forms'),
			],
		];

		foreach ($this->labels->getLabels() as $key => $label) {
			$output[] = [
				'component' => 'input',
				'inputName' => $key,
				'inputId' => $key,
				'inputFieldLabel' => $key,
				'inputPlaceholder' => $label,
			];
		}

		return $output;
	}

	/**
	 * Get greenhouse fields.
	 *
	 * @return array
	 */
	public function getGreenhouseFields(): array
	{
		return [
			[
				'component' => 'intro',
				'introTitle' => \__('Greenhouse settings', 'eightshift-forms'),
				'introSubtitle' => \__('Configure your greenhouse settings in one place.', 'eightshift-forms'),
			],
		];
	}

	/**
	 * Get mailchimp fields.
	 *
	 * @return array
	 */
	public function getMailchimpFields(): array
	{
		return [
			[
				'component' => 'intro',
				'introTitle' => \__('Mailchimp settings', 'eightshift-forms'),
				'introSubtitle' => \__('Configure your mailchimp settings in one place.', 'eightshift-forms'),
			],
		];
	}

	/**
	 * Get hubspot fields.
	 *
	 * @return array
	 */
	public function getHubspotFields(): array
	{
		return [
			[
				'component' => 'intro',
				'introTitle' => \__('Hubspot settings', 'eightshift-forms'),
				'introSubtitle' => \__('Configure your hubspot settings in one place.', 'eightshift-forms'),
			],
		];
	}
}
