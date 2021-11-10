<?php

/**
 * Class that holds all labels.
 *
 * @package EightshiftLibs\Labels
 */

declare(strict_types=1);

namespace EightshiftForms\Labels;

use EightshiftForms\Integrations\Goodbits\SettingsGoodbits;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
use EightshiftForms\Integrations\Mailchimp\SettingsMailchimp;
use EightshiftForms\Integrations\Mailerlite\SettingsMailerlite;
use EightshiftForms\Settings\SettingsHelper;

/**
 * Labels class.
 */
class Labels implements LabelsInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * List all label keys that are stored in local form everything else is global settings.
	 */
	public const ALL_LOCAL_LABELS = [
		'mailerSuccess',
		'greenhouseSuccess',
		'mailchimpSuccess',
		'hubspotSuccess',
		'mailerliteSuccess',
		'goodbitsSuccess',
	];

	/**
	 * Get all labels
	 *
	 * @return array<string, string>
	 */
	public function getLabels(): array
	{
		$output = array_merge(
			$this->getGenericLabels(),
			$this->getValidationLabels(),
			$this->getMailerLabels()
		);

		// Greenhouse.
		if ((bool) $this->isCheckboxOptionChecked(SettingsGreenhouse::SETTINGS_GREENHOUSE_USE_KEY, SettingsGreenhouse::SETTINGS_GREENHOUSE_USE_KEY)) {
			$output = array_merge($output, $this->getGreenhouseLabels());
		}

		// Mailchimp.
		if ((bool) $this->isCheckboxOptionChecked(SettingsMailchimp::SETTINGS_MAILCHIMP_USE_KEY, SettingsMailchimp::SETTINGS_MAILCHIMP_USE_KEY)) {
			$output = array_merge($output, $this->getMailchimpLabels());
		}

		// Hubspot.
		if ((bool) $this->isCheckboxOptionChecked(SettingsHubspot::SETTINGS_HUBSPOT_USE_KEY, SettingsHubspot::SETTINGS_HUBSPOT_USE_KEY)) {
			$output = array_merge($output, $this->getHubspotLabels());
		}

		// Mailerlite.
		if ((bool) $this->isCheckboxOptionChecked(SettingsMailerlite::SETTINGS_MAILERLITE_USE_KEY, SettingsMailerlite::SETTINGS_MAILERLITE_USE_KEY)) {
			$output = array_merge($output, $this->getMailerliteLabels());
		}

		// Goodbits.
		if ((bool) $this->isCheckboxOptionChecked(SettingsGoodbits::SETTINGS_GOODBITS_USE_KEY, SettingsGoodbits::SETTINGS_GOODBITS_USE_KEY)) {
			$output = array_merge($output, $this->getGoodbitsLabels());
		}

		return $output;
	}

	/**
	 * Return labels - Generic
	 *
	 * @return array
	 */
	private function getGenericLabels(): array
	{
		return [
			'submitWpError' => __('There was a problem with submitting your form. Please try again.', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Validation
	 *
	 * @return array
	 */
	private function getValidationLabels(): array
	{
		return [
			'validationRequired' => __('This field is required.', 'eightshift-forms'),
			// translators: %s used for displaying required number.
			'validationRequiredCount' => __('This field is required with minimum %s selected items.', 'eightshift-forms'),
			'validationEmail' => __('This field is not a valid email.', 'eightshift-forms'),
			'validationUrl' => __('This field is not a valid url.', 'eightshift-forms'),
			// translators: %s used for displaying validation pattern to the user.
			'validationPattern' => __('Your field doesn\'t satisfy this validation pattern %s.', 'eightshift-forms'),
			// translators: %s used for displaying number value.
			'validationAccept' => __('Your file type is not supported. Please use only %s file type.', 'eightshift-forms'),
			// translators: %s used for displaying number value.
			'validationMinSize' => __('Your file is smaller than allowed. Minimum file size is %s kb.', 'eightshift-forms'),
			// translators: %s used for displaying number value.
			'validationMaxSize' => __('Your file is larget than allowed. Maximum file size is %s kb.', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Maler
	 *
	 * @return array
	 */
	private function getMailerLabels(): array
	{
		return [
			'mailerSuccessNoSend' => __('Email sent successfully.', 'eightshift-forms'),
			'mailerErrorSettingsMissing' => __('Mailer settings are not configured correctly. Please try again.', 'eightshift-forms'),
			'mailerErrorEmailSend' => __('Email not sent due to unknown issue. Please try again.', 'eightshift-forms'),
			'mailerErrorEmailConfirmationSend' => __('Email user confirmation not sent due to unknown issue. Please try again.', 'eightshift-forms'),
			'mailerSuccess' => __('Email sent successfully.', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Greenhouse
	 *
	 * @return array
	 */
	private function getGreenhouseLabels(): array
	{
		return [
			'greenhouseErrorSettingsMissing' => __('Greenhouse is not configured correctly. Please try again.', 'eightshift-forms'),
			'greenhouseBadRequestError' => __('There is something wrong with your application. Please check all the fields and try again.', 'eightshift-forms'),
			'greenhouseUnsupportedFileTypeError' => __('You have submitted an unsupported file type. Please try again.', 'eightshift-forms'),
			'greenhouseInvalidFirstNameError' => __('Your application has some invalid fields: first name.', 'eightshift-forms'),
			'greenhouseInvalidLastNameError' => __('Your application has some invalid fields: last name.', 'eightshift-forms'),
			'greenhouseInvalidEmailError' => __('Your application has some invalid fields: email.', 'eightshift-forms'),
			'greenhouseInvalidFirstNameLastNameEmailError' => __('Your application has some invalid fields: First name, last name and email.', 'eightshift-forms'),
			'greenhouseInvalidFirstNameLastNameError' => __('Your application has some invalid fields: first name and last name.', 'eightshift-forms'),
			'greenhouseInvalidFirstNameEmailError' => __('Your application has some invalid fields: first name and email.', 'eightshift-forms'),
			'greenhouseInvalidLastNameEmailError' => __('Your application has some invalid fields: last name and email.', 'eightshift-forms'),
			'greenhouseInvalidFirstNamePhoneError' => __('Your application has some invalid fields: first name and phone.', 'eightshift-forms'),
			'greenhouseInvalidLastNamePhoneError' => __('Your application has some invalid fields: last name and phone.', 'eightshift-forms'),
			'greenhouseInvalidEmailPhoneError' => __('Your application has some invalid fields: email and phone.', 'eightshift-forms'),
			'greenhouseInvalidFirstNameLastNameEmailPhoneError' => __('Your application has some invalid fields: first name, last name, email and phone.', 'eightshift-forms'),
			'greenhouseInvalidFirstNameLastNamePhoneError' => __('Your application has some invalid fields: first name, last name and phone.', 'eightshift-forms'),
			'greenhouseInvalidFirstNameEmailPhoneError' => __('Your application has some invalid fields: first name, email and phone.', 'eightshift-forms'),
			'greenhouseInvalidLastNameEmailPhoneError' => __('Your application has some invalid fields: last name, email and phone.', 'eightshift-forms'),
			'greenhouseSuccess' => __('Your application is saved successfully. Thank you.', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Mailchimp
	 *
	 * @return array
	 */
	private function getMailchimpLabels(): array
	{
		return [
			'mailchimpErrorSettingsMissing' => __('Mailchimp is not configured correctly. Please try again.', 'eightshift-forms'),
			'mailchimpBadRequestError' => __('There is something wrong with your subscription. Please check all the fields and try again.', 'eightshift-forms'),
			'mailchimpInvalidResourceError' => __('There is something wrong with your application. Please check all the fields and try again.', 'eightshift-forms'),
			'mailchimpInvalidEmailError' => __('It looks like your email is not a valid format. Please try again.', 'eightshift-forms'),
			'mailchimpMissingFieldsError' => __('It looks like we are missing some required fields. Please check all the fields and try again.', 'eightshift-forms'),
			'mailchimpSuccess' => __('You have successfully subscribed to our newsletter. Thank you.', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - HubSpot
	 *
	 * @return array
	 */
	private function getHubspotLabels(): array
	{
		return [
			'hubspotErrorSettingsMissing' => __('Hubspot is not configured correctly. Please try again.', 'eightshift-forms'),
			'hubspotBadRequestError' => __('There is something wrong with your application. Please check all the fields and try again.', 'eightshift-forms'),
			'hubspotInvalidRequestError' => __('There is something wrong with your application. Please check all the fields and try again.', 'eightshift-forms'),
			'hubspotInvalidEmailError' => __('It looks like your email is not a valid format. Please try again.', 'eightshift-forms'),
			'hubspotMissingFieldsError' => __('It looks like we are missing some required fields. Please check all the fields and try again.', 'eightshift-forms'),
			'hubspotSuccess' => __('You have successfully submitted this form.  Thank you.', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Mailerlite
	 *
	 * @return array
	 */
	private function getMailerliteLabels(): array
	{
		return [
			'mailerliteErrorSettingsMissing' => __('Mailerlite is not configured correctly. Please try again.', 'eightshift-forms'),
			'mailerliteBadRequestError' => __('There is something wrong with your subscription. Please check all the fields and try again.', 'eightshift-forms'),
			'mailerliteInvalidEmailError' => __('It looks like your email is not a valid format. Please try again.', 'eightshift-forms'),
			'mailerliteEmailTemporarilyBlockedError' => __('It looks like your email is temporarily blocked by our email client. Please try again later or use a different email.', 'eightshift-forms'),
			'mailerliteSuccess' => __('You have successfully subscribed to our newsletter. Thank you.', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Goodbits
	 *
	 * @return array
	 */
	private function getGoodbitsLabels(): array
	{
		return [
			'goodbitsErrorSettingsMissing' => __('Goodbits is not configured correctly. Please try again.', 'eightshift-forms'),
			'goodbitsBadRequestError' => __('There is something wrong with your subscription. Please check all the fields and try again.', 'eightshift-forms'),
			'goodbitsInvalidEmailError' => __('It looks like your email is not a valid format. Please try again.', 'eightshift-forms'),
			'goodbitsEmailTemporarilyBlockedError' => __('It looks like your email is temporarily blocked by our email client. Please try again later or use a different email.', 'eightshift-forms'),
			'goodbitsSuccess' => __('You have successfully subscribed to our newsletter. Thank you.', 'eightshift-forms'),
		];
	}

	/**
	 * Return one label by key
	 *
	 * @param string $key Label key.
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	public function getLabel(string $key, string $formId = ''): string
	{
		// If form ID is not missing check form settings for the overrides.
		if (!empty($formId)) {
			$local = array_flip(self::ALL_LOCAL_LABELS);

			if (isset($local[$key])) {
				$dbLabel = $this->getSettingsValue($key, $formId);
			} else {
				$dbLabel = $this->getOptionValue($key);
			}

			// If there is an override in the DB use that.
			if (!empty($dbLabel)) {
				return $dbLabel;
			}
		}

		$labels = $this->getLabels();

		return $labels[$key] ?? '';
	}
}
