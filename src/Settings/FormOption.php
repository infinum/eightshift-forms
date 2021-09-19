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
	/**
	 * Set all settings page field keys.
	 *
	 * @return array
	 */
	public function getFormFields(): array
	{
		return [
			[
				'component' => 'checkbox',
				'checkboxName' => 'es_mailer_use',
				'checkboxLabel' => \__('Use Mailer system?', 'eightshift-forms'),
				'checkboxHelp' => \__('If checked your form will send you an email once it is submitted.', 'eightshift-forms'),
				'checkboxType' => 'checkbox',
				'checkboxIsRequired' => true,
				'checkboxIsChecked' => true,
			],
			[
				'component' => 'input',
				'inputName' => 'es_mailer_sender_name',
				'inputFieldLabel' => \__('Sender Name', 'eightshift-forms'),
				'inputFieldHelp' => \__('', 'eightshift-forms'),
				'inputType' => 'text',
				'inputIsRequired' => true,
			],
			[
				'component' => 'input',
				'inputName' => 'es_mailer_sender_email',
				'inputFieldLabel' => \__('Sender Email', 'eightshift-forms'),
				'inputFieldHelp' => \__('', 'eightshift-forms'),
				'inputType' => 'email',
				'inputIsRequired' => true,
			],
			[
				'component' => 'input',
				'inputName' => 'es_mailer_to',
				'inputFieldLabel' => \__('Send email to', 'eightshift-forms'),
				'inputFieldHelp' => \__('Send email to what user once sent.', 'eightshift-forms'),
				'inputType' => 'email',
				'inputIsRequired' => true,
			],
			[
				'component' => 'input',
				'inputName' => 'es_mailer_subject',
				'inputFieldLabel' => \__('Email subject', 'eightshift-forms'),
				'inputFieldHelp' => \__('Define email subject', 'eightshift-forms'),
				'inputType' => 'text',
				'inputIsRequired' => true,
			],
		];
	}
}
