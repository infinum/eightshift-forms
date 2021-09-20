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
	public const MAILER_USE_KEY = 'mailer-use';
	public const MAILER_SENDER_NAME_KEY = 'mailer-sender-name';
	public const MAILER_SENDER_EMAIL_KEY = 'mailer-sender-email';
	public const MAILER_TO_KEY = 'mailer-to';
	public const MAILER_SUBJECT_KEY = 'mailer-subject';
	public const MAILER_TEMPLATE_KEY = 'mailer-template';

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
				'label' => \__('Use Mailer system?', 'eightshift-forms'),
				'help' => \__('If checked your form will send you an email once it is submitted.', 'eightshift-forms'),
				'type' => 'checkbox',
				'items' => [
					[
						'name' => self::MAILER_USE_KEY,
						'label' => \__('Use', 'eightshift-forms'),
						'isRequired' => true,
					],
				]
			],
			[
				'component' => 'input',
				'name' => self::MAILER_SENDER_NAME_KEY,
				'label' => \__('Sender Name', 'eightshift-forms'),
				'help' => \__('', 'eightshift-forms'),
				'type' => 'text',
				'isRequired' => true,
			],
			[
				'component' => 'input',
				'name' => self::MAILER_SENDER_EMAIL_KEY,
				'label' => \__('Sender Email', 'eightshift-forms'),
				'help' => \__('Send email to what user once sent.', 'eightshift-forms'),
				'type' => 'email',
				'isRequired' => true,
			],
			[
				'component' => 'input',
				'name' => self::MAILER_TO_KEY,
				'label' => \__('Email to', 'eightshift-forms'),
				'help' => \__('Define email to', 'eightshift-forms'),
				'type' => 'text',
				'isRequired' => true,
			],
			[
				'component' => 'input',
				'name' => self::MAILER_SUBJECT_KEY,
				'label' => \__('Email subject', 'eightshift-forms'),
				'help' => \__('Define email subject', 'eightshift-forms'),
				'type' => 'text',
				'isRequired' => true,
			],
			[
				'component' => 'textarea',
				'name' => self::MAILER_TEMPLATE_KEY,
				'label' => \__('Email template', 'eightshift-forms'),
				'help' => \__('Define email template', 'eightshift-forms'),
				'isRequired' => true,
			],
		];
	}
}
