<?php

/**
 * Class that holds all settings for form.
 *
 * @package EightshiftLibs\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings;

/**
 * FormOption class.
 */
class FormOption extends AbstractFormBuilder
{
	public const MAILER_USE_KEY = 'es-mailer-use';
	public const MAILER_SENDER_NAME_KEY = 'es-mailer-sender-name';
	public const MAILER_SENDER_EMAIL_KEY = 'es-mailer-sender-email';
	public const MAILER_TO_KEY = 'es-mailer-to';
	public const MAILER_SUBJECT_KEY = 'es-mailer-subject';
	public const MAILER_TEMPLATE_KEY = 'es-mailer-template';

	/**
	 * Set all settings page field keys.
	 *
	 * @return array
	 */
	public function getFormFields(): array
	{
		return [
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => \__('Use Mailer system?', 'eightshift-forms'),
				'checkboxesHelp' => \__('If checked your form will send you an email once it is submitted.', 'eightshift-forms'),
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
				'inputHelp' => \__('', 'eightshift-forms'),
				'inputType' => 'text',
				'inputIsRequired' => true,
			],
			[
				'component' => 'input',
				'inputName' => self::MAILER_SENDER_EMAIL_KEY,
				'inputFieldLabel' => \__('Sender Email', 'eightshift-forms'),
				'inputHelp' => \__('Send email to what user once sent.', 'eightshift-forms'),
				'inputType' => 'email',
				'inputIsRequired' => true,
			],
			[
				'component' => 'input',
				'inputName' => self::MAILER_TO_KEY,
				'inputFieldLabel' => \__('Email to', 'eightshift-forms'),
				'inputHelp' => \__('Define email to', 'eightshift-forms'),
				'inputType' => 'email',
				'inputIsRequired' => true,
			],
			[
				'component' => 'input',
				'inputName' => self::MAILER_SUBJECT_KEY,
				'inputFieldLabel' => \__('Email subject', 'eightshift-forms'),
				'inputHelp' => \__('Define email subject', 'eightshift-forms'),
				'inputType' => 'text',
				'inputIsRequired' => true,
			],
			[
				'component' => 'textarea',
				'textareaName' => self::MAILER_TEMPLATE_KEY,
				'textareaFieldLabel' => \__('Email template', 'eightshift-forms'),
				'textareaHelp' => \__('Define email template', 'eightshift-forms'),
				'textareaIsRequired' => true,
			],
		];
	}
}
