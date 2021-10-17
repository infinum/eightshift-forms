<?php

/**
 * Mailer Settings class.
 *
 * @package EightshiftForms\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Mailer;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\SettingsHelper;
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
	use SettingsHelper;

	/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_mailer';

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_mailer';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_mailer';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'mailer';

	/**
	 * Mailer Use key.
	 */
	public const SETTINGS_MAILER_USE_KEY = 'mailerUse';

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
	 * Sender Subject key.
	 */
	public const SETTINGS_MAILER_SENDER_SUBJECT_KEY = 'mailerSenderSubject';

	/**
	 * Sender Template key.
	 */
	public const SETTINGS_MAILER_SENDER_TEMPLATE_KEY = 'mailerSenderTemplate';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_SIDEBAR_NAME, [$this, 'getSettingsSidebar']);
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData'], 10, 2);
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsValid']);
	}

	/**
	 * Determin if settings are valid.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return boolean
	 */
	public function isSettingsValid(string $formId): bool
	{
		$senderName = $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_NAME_KEY, $formId);
		$senderEmail = $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_EMAIL_KEY, $formId);
		$to = $this->getSettingsValue(self::SETTINGS_MAILER_TO_KEY, $formId);
		$subject = $this->getSettingsValue(self::SETTINGS_MAILER_SUBJECT_KEY, $formId);

		if (
			empty($senderName) ||
			empty($senderEmail) ||
			empty($to) ||
			empty($subject)
		) {
			return false;
		}

		return true;
	}

	/**
	 * Get Settings sidebar data.
	 *
	 * @return array
	 */
	public function getSettingsSidebar(): array
	{
		return [
			'label' => __('Mailer', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => '<svg width="30" height="30" xmlns="http://www.w3.org/2000/svg"><g fill-rule="nonzero" fill="none"><path d="M29.374 2.344l-10.97 7.93-1.8 1.304a2.7 2.7 0 01-3.208 0l-1.8-1.304L.613 2.357A3.243 3.243 0 013.261 1h23.478c1.043 0 2.022.5 2.635 1.344z" fill="#FFD54F"/><g fill="#FFC107"><path d="M.613 2.357l10.983 7.917L.952 20.917A3.261 3.261 0 010 18.61V4.26a3.222 3.222 0 01.613-1.904zM30 4.26V18.61a3.26 3.26 0 01-.952 2.308L18.404 10.274l10.97-7.93C29.78 2.9 30 3.57 30 4.26z"/></g><path d="M19.565 24.478c-.866 0-1.304-.652-1.304-1.956a3.3 3.3 0 10-.794 2.109 2.326 2.326 0 002.098 1.152c2.609 0 2.609-2.455 2.609-3.261a7.174 7.174 0 10-2.186 5.156.654.654 0 00-.913-.938 5.87 5.87 0 111.795-4.218c0 1.462-.33 1.956-1.305 1.956zm-4.565 0a1.957 1.957 0 110-3.913 1.957 1.957 0 010 3.913z" fill="#455A64"/><path d="M18.404 10.274l-1.8 1.304a2.7 2.7 0 01-3.208 0l-1.8-1.304L.952 20.917a3.26 3.26 0 002.309.953h3.293a8.47 8.47 0 0116.89 0h3.295a3.26 3.26 0 002.309-.953L18.404 10.274z" fill="#FFA000"/></g></svg>',
		];
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
		$isUsed = (bool) $this->getSettingsValue(self::SETTINGS_MAILER_USE_KEY, $formId);

		$output = [
			[
				'component' => 'intro',
				'introTitle' => \__('Mailing setting', 'eightshift-forms'),
				'introSubtitle' => \__('Configure your mailing settings in one place.', 'eightshift-forms'),
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => \__('Check options to use', 'eightshift-forms'),
				'checkboxesFieldHelp' => \__('Select if you want to use send email on form success.', 'eightshift-forms'),
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxName' => $this->getSettingsName(self::SETTINGS_MAILER_USE_KEY),
						'checkboxId' => $this->getSettingsName(self::SETTINGS_MAILER_USE_KEY),
						'checkboxLabel' => __('Use Mailer', 'eightshift-forms'),
						'checkboxIsChecked' => !empty($this->getSettingsValue(self::SETTINGS_MAILER_USE_KEY, $formId)),
						'checkboxValue' => 'true',
					]
				]
			],
		];

		if ($isUsed) {
			$formNames = Helper::getFormNames($formId);

			$output = array_merge(
				$output,
				[
					[
						'component' => 'divider',
					],
					[
						'component' => 'intro',
						'introTitle' => \__('Configure admin email options', 'eightshift-forms'),
						'introTitleSize' => 'medium',
						'introSubtitle' => \__('Configure options that are going to be used for email send to the sender email addres, generally this is the website owner.', 'eightshift-forms'),
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
						'inputFieldHelp' => \__('Define to what address the email will be sent.', 'eightshift-forms'),
						'inputType' => 'email',
						'inputIsRequired' => true,
						'inputValue' => $this->getSettingsValue(self::SETTINGS_MAILER_TO_KEY, $formId),
					],
					[
						'component' => 'input',
						'inputName' => $this->getSettingsName(self::SETTINGS_MAILER_SUBJECT_KEY),
						'inputId' => $this->getSettingsName(self::SETTINGS_MAILER_SUBJECT_KEY),
						'inputFieldLabel' => \__('Email subject', 'eightshift-forms'),
						'inputFieldHelp' => \__('Define email subject.', 'eightshift-forms'),
						'inputType' => 'text',
						'inputIsRequired' => true,
						'inputValue' => $this->getSettingsValue(self::SETTINGS_MAILER_SUBJECT_KEY, $formId),
					],
					[
						'component' => 'textarea',
						'textareaName' => $this->getSettingsName(self::SETTINGS_MAILER_TEMPLATE_KEY),
						'textareaId' => $this->getSettingsName(self::SETTINGS_MAILER_TEMPLATE_KEY),
						'textareaFieldLabel' => \__('Email template', 'eightshift-forms'),
						// translators: %s will be replaced with forms field name.
						'textareaFieldHelp' => \sprintf(__('Define email template. You can use these email template variables: %s. If you don\'t see your field here please check your form blocks and populate <strong>name</strong> input.', 'eightshift-forms'), $formNames),
						'textareaIsRequired' => false,
						'textareaValue' => $this->getSettingsValue(self::SETTINGS_MAILER_TEMPLATE_KEY, $formId),
					],
					[
						'component' => 'divider',
					],
					[
						'component' => 'intro',
						'introTitle' => \__('Configure sender confirmation email options', 'eightshift-forms'),
						'introTitleSize' => 'medium',
						'introSubtitle' => \__('Configure options that are going to be used for sender confirmation email send to the person that filled the form of the frontend.', 'eightshift-forms'),
					],
					[
						'component' => 'input',
						'inputName' => $this->getSettingsName(self::SETTINGS_MAILER_SENDER_SUBJECT_KEY),
						'inputId' => $this->getSettingsName(self::SETTINGS_MAILER_SENDER_SUBJECT_KEY),
						'inputFieldLabel' => \__('Sender email subject', 'eightshift-forms'),
						'inputFieldHelp' => \__('Define sender email subject.', 'eightshift-forms'),
						'inputType' => 'text',
						'inputIsRequired' => true,
						'inputValue' => $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_SUBJECT_KEY, $formId),
					],
					[
						'component' => 'textarea',
						'textareaName' => $this->getSettingsName(self::SETTINGS_MAILER_SENDER_TEMPLATE_KEY),
						'textareaId' => $this->getSettingsName(self::SETTINGS_MAILER_SENDER_TEMPLATE_KEY),
						'textareaFieldLabel' => \__('Sender email template', 'eightshift-forms'),
						// translators: %s will be replaced with forms field name.
						'textareaFieldHelp' => \sprintf(__('Define sender email template. You can use these email template variables: %s. If you don\'t see your field here please check your form blocks and populate <strong>name</strong> input.', 'eightshift-forms'), $formNames),
						'textareaIsRequired' => false,
						'textareaValue' => $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_TEMPLATE_KEY, $formId),
					],
				]
			);
		}

		return $output;
	}
}
