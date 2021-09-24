<?php

/**
 * Mailer Settings Options class.
 *
 * @package EightshiftForms\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Mailer;

use EightshiftForms\Helpers\TraitHelper;
use EightshiftForms\Settings\Settings\SettingsTypeInterface;
use EightshiftFormsPluginVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Mailer integration class.
 */
class SettingsMailer implements SettingsTypeInterface, ServiceInterface
{
	/**
	 * Use General helper trait.
	 */
	use TraitHelper;

	// Filter name key.
	public const FILTER_NAME = 'esforms_settings_mailer';

	// Settings key.
	public const TYPE_KEY = 'mailer';

	// Use keys.
	public const MAILER_SENDER_NAME_KEY = 'mailerSenderName';
	public const MAILER_SENDER_EMAIL_KEY = 'mailerSenderEmail';
	public const MAILER_TO_KEY = 'mailerTo';
	public const MAILER_SUBJECT_KEY = 'mailerSubject';
	public const MAILER_TEMPLATE_KEY = 'mailerTemplate';

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
		return [
			'sidebar' => [
				'label' => __('Mailer', 'eightshift-forms'),
				'value' => self::TYPE_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			],
			'form' => [
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
			]
		];
	}
}
