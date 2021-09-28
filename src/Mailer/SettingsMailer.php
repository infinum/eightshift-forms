<?php

/**
 * Mailer Settings class.
 *
 * @package EightshiftForms\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Mailer;

use EightshiftForms\Helpers\TraitHelper;
use EightshiftForms\Settings\Settings\SettingsDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsMailer class.
 */
class SettingsMailer implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use TraitHelper;

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_mailer';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'mailer';

	/**
	 * Sender Name key.
	 */
	public const SETTINGS_MAILER_SENDER_NAME_KEY = 'mailerSenderName';

	/**
	 * Sender Email key.
	 */
	public const SETTINGS_MAILER_SENDER_EMAIL_KEY = 'mailerSenderEmail';

	/**
	 * Mail To key.
	 */
	public const SETTINGS_MAILER_TO_KEY = 'mailerTo';

	/**
	 * Subject key.
	 */
	public const SETTINGS_MAILER_SUBJECT_KEY = 'mailerSubject';

	/**
	 * Template key.
	 */
	public const SETTINGS_MAILER_TEMPLATE_KEY = 'mailerTemplate';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
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
		return [
			'sidebar' => [
				'label' => __('Mailer', 'eightshift-forms'),
				'value' => self::SETTINGS_TYPE_KEY,
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
					'inputName' => $this->getSettingsName(self::SETTINGS_MAILER_SENDER_NAME_KEY),
					'inputId' => $this->getSettingsName(self::SETTINGS_MAILER_SENDER_NAME_KEY),
					'inputFieldLabel' => \__('Sender Name', 'eightshift-forms'),
					'inputFieldHelp' => \__('Define sender name showed in the email client.', 'eightshift-forms'),
					'inputType' => 'text',
					'inputIsRequired' => true,
					'inputValue' => $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_NAME_KEY, $formId),
				],
				[
					'component' => 'input',
					'inputName' => $this->getSettingsName(self::SETTINGS_MAILER_SENDER_EMAIL_KEY),
					'inputId' => $this->getSettingsName(self::SETTINGS_MAILER_SENDER_EMAIL_KEY),
					'inputFieldLabel' => \__('Sender Email', 'eightshift-forms'),
					'inputFieldHelp' => \__('Define sender email showed in the email client.', 'eightshift-forms'),
					'inputType' => 'email',
					'inputIsRequired' => true,
					'inputValue' => $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_EMAIL_KEY, $formId),
				],
				[
					'component' => 'input',
					'inputName' => $this->getSettingsName(self::SETTINGS_MAILER_TO_KEY),
					'inputId' => $this->getSettingsName(self::SETTINGS_MAILER_TO_KEY),
					'inputFieldLabel' => \__('Email to', 'eightshift-forms'),
					'inputFieldHelp' => \__('Define to what address the email will be sent', 'eightshift-forms'),
					'inputType' => 'email',
					'inputIsRequired' => true,
					'inputValue' => $this->getSettingsValue(self::SETTINGS_MAILER_TO_KEY, $formId),
				],
				[
					'component' => 'input',
					'inputName' => $this->getSettingsName(self::SETTINGS_MAILER_SUBJECT_KEY),
					'inputId' => $this->getSettingsName(self::SETTINGS_MAILER_SUBJECT_KEY),
					'inputFieldLabel' => \__('Email subject', 'eightshift-forms'),
					'inputFieldHelp' => \__('Define email subject', 'eightshift-forms'),
					'inputType' => 'text',
					'inputIsRequired' => true,
					'inputValue' => $this->getSettingsValue(self::SETTINGS_MAILER_SUBJECT_KEY, $formId),
				],
				[
					'component' => 'textarea',
					'textareaName' => $this->getSettingsName(self::SETTINGS_MAILER_TEMPLATE_KEY),
					'textareaId' => $this->getSettingsName(self::SETTINGS_MAILER_TEMPLATE_KEY),
					'textareaFieldLabel' => \__('Email template', 'eightshift-forms'),
					'textareaFieldHelp' => \__('Define email template', 'eightshift-forms'),
					'textareaIsRequired' => true,
					'textareaValue' => $this->getSettingsValue(self::SETTINGS_MAILER_TEMPLATE_KEY, $formId),
				],
			]
		];
	}
}
