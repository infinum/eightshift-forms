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
	public const MAILER_USE_KEY = 'mailerUse';
	public const MAILER_SENDER_NAME_KEY = 'mailerSenderName';
	public const MAILER_SENDER_EMAIL_KEY = 'mailerSenderEmail';
	public const MAILER_TO_KEY = 'mailerTo';
	public const MAILER_SUBJECT_KEY = 'mailerSubject';
	public const MAILER_TEMPLATE_KEY = 'mailerTemplate';

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
	 * @return array
	 */
	public function getFormFields(): array
	{
		return array_merge(
			$this->getMailerFields(),
			$this->getValidationFields(),
		);
	}

	/**
	 * Get mailer fields
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
				'component' => 'checkboxes',
				'checkboxesFieldHelp' => \__('If checked your form will send an email once it is submitted.', 'eightshift-forms'),
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxName' => self::MAILER_USE_KEY,
						'checkboxLabel' => \__('Use', 'eightshift-forms'),
						'checkboxIsRequired' => true,
						'checkboxValue' => 'true',
					],
				]
			],
			[
				'component' => 'input',
				'inputName' => self::MAILER_SENDER_NAME_KEY,
				'inputFieldLabel' => \__('Sender Name', 'eightshift-forms'),
				'inputFieldHelp' => \__('Define sender name showed in the email client.', 'eightshift-forms'),
				'inputType' => 'text',
				'inputIsRequired' => true,
			],
			[
				'component' => 'input',
				'inputName' => self::MAILER_SENDER_EMAIL_KEY,
				'inputFieldLabel' => \__('Sender Email', 'eightshift-forms'),
				'inputFieldHelp' => \__('Define sender email showed in the email client.', 'eightshift-forms'),
				'inputType' => 'email',
				'inputIsRequired' => true,
			],
			[
				'component' => 'input',
				'inputName' => self::MAILER_TO_KEY,
				'inputFieldLabel' => \__('Email to', 'eightshift-forms'),
				'inputFieldHelp' => \__('Define to what address the email will be sent', 'eightshift-forms'),
				'inputType' => 'email',
				'inputIsRequired' => true,
			],
			[
				'component' => 'input',
				'inputName' => self::MAILER_SUBJECT_KEY,
				'inputFieldLabel' => \__('Email subject', 'eightshift-forms'),
				'inputFieldHelp' => \__('Define email subject', 'eightshift-forms'),
				'inputType' => 'text',
				'inputIsRequired' => true,
			],
			[
				'component' => 'textarea',
				'textareaName' => self::MAILER_TEMPLATE_KEY,
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
	 * Get validation fields
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
				'inputFieldLabel' => $key,
				'inputPlaceholder' => $label,
			];
		}

		return $output;
	}
}
