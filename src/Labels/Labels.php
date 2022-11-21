<?php

/**
 * Class that holds all labels.
 *
 * @package EightshiftLibs\Labels
 */

declare(strict_types=1);

namespace EightshiftForms\Labels;

use EightshiftForms\Integrations\ActiveCampaign\SettingsActiveCampaign;
use EightshiftForms\Integrations\Airtable\SettingsAirtable;
use EightshiftForms\Integrations\Goodbits\SettingsGoodbits;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
use EightshiftForms\Integrations\Mailchimp\SettingsMailchimp;
use EightshiftForms\Integrations\Mailerlite\SettingsMailerlite;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Validation\SettingsCaptcha;

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
		'customSuccess',
		'activeCampaignSuccess',
		'airtableSuccess',
	];

	/**
	 * Get all labels
	 *
	 * @return array<string, string>
	 */
	public function getLabels(): array
	{
		$output = \array_merge(
			$this->getGenericLabels(),
			$this->getValidationLabels(),
			$this->getMailerLabels(),
			$this->getCutomLabels()
		);

		// Google reCaptcha.
		if ($this->isCheckboxOptionChecked(SettingsCaptcha::SETTINGS_CAPTCHA_USE_KEY, SettingsCaptcha::SETTINGS_CAPTCHA_USE_KEY)) {
			$output = \array_merge($output, $this->getCaptchaLabels());
		}

		// Greenhouse.
		if ($this->isCheckboxOptionChecked(SettingsGreenhouse::SETTINGS_GREENHOUSE_USE_KEY, SettingsGreenhouse::SETTINGS_GREENHOUSE_USE_KEY)) {
			$output = \array_merge($output, $this->getGreenhouseLabels());
		}

		// Mailchimp.
		if ($this->isCheckboxOptionChecked(SettingsMailchimp::SETTINGS_MAILCHIMP_USE_KEY, SettingsMailchimp::SETTINGS_MAILCHIMP_USE_KEY)) {
			$output = \array_merge($output, $this->getMailchimpLabels());
		}

		// Hubspot.
		if ($this->isCheckboxOptionChecked(SettingsHubspot::SETTINGS_HUBSPOT_USE_KEY, SettingsHubspot::SETTINGS_HUBSPOT_USE_KEY)) {
			$output = \array_merge($output, $this->getHubspotLabels());
		}

		// Mailerlite.
		if ($this->isCheckboxOptionChecked(SettingsMailerlite::SETTINGS_MAILERLITE_USE_KEY, SettingsMailerlite::SETTINGS_MAILERLITE_USE_KEY)) {
			$output = \array_merge($output, $this->getMailerliteLabels());
		}

		// Goodbits.
		if ($this->isCheckboxOptionChecked(SettingsGoodbits::SETTINGS_GOODBITS_USE_KEY, SettingsGoodbits::SETTINGS_GOODBITS_USE_KEY)) {
			$output = \array_merge($output, $this->getGoodbitsLabels());
		}

		// ActiveCampaign.
		if ($this->isCheckboxOptionChecked(SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_USE_KEY, SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_USE_KEY)) {
			$output = \array_merge($output, $this->getActiveCampaignLabels());
		}

		// Airtable.
		if ($this->isCheckboxOptionChecked(SettingsAirtable::SETTINGS_AIRTABLE_USE_KEY, SettingsAirtable::SETTINGS_AIRTABLE_USE_KEY)) {
			$output = \array_merge($output, $this->getAirtableLabels());
		}

		return $output;
	}

	/**
	 * Return labels - Generic
	 *
	 * @return array<string, string>
	 */
	private function getGenericLabels(): array
	{
		return [
			'submitWpError' => \__('Something went wrong while submitting your form. Please try again.', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Validation
	 *
	 * @return array<string, string>
	 */
	private function getValidationLabels(): array
	{
		return [
			'validationRequired' => \__('This field is required.', 'eightshift-forms'),
			// translators: %s used for displaying required number.
			'validationRequiredCount' => \__('This field is required, with at least %s items selected.', 'eightshift-forms'),
			'validationEmail' => \__('This e-mail is not valid.', 'eightshift-forms'),
			'validationUrl' => \__('This URL is not valid.', 'eightshift-forms'),
			// translators: %s used for displaying length min number to the user.
			'validationMinLength' => \__('This field value has less characters than expected. We expect minimum %s characters.', 'eightshift-forms'),
			// translators: %s used for displaying length max number to the user.
			'validationMaxLength' => \__('This field value has more characters than expected. We expect maximum %s characters.', 'eightshift-forms'),
			'validationNumber' => \__('This field should only contain numbers.', 'eightshift-forms'),
			// translators: %s used for displaying validation pattern to the user.
			'validationPattern' => \__('This field doesn\'t satisfy the validation pattern: %s.', 'eightshift-forms'),
			// translators: %s used for displaying file type value.
			'validationAccept' => \__('The file type is not supported. Only %s are allowed.', 'eightshift-forms'),
			// translators: %s used for displaying file type value.
			'validationAcceptMime' => \__('The file seems to be corrupted. Only %s are allowed.', 'eightshift-forms'),
			// translators: %s used for displaying number value.
			'validationMinSize' => \__('The file is smaller than allowed. Minimum file size is %s MB.', 'eightshift-forms'),
			// translators: %s used for displaying number value.
			'validationMaxSize' => \__('The file is larger than allowed. Maximum file size is %s MB.', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Custom action
	 *
	 * @return array<string, string>
	 */
	private function getCutomLabels(): array
	{
		return [
			'customNoAction' => \__('There was an issue with form action. Check the form settings.', 'eightshift-forms'),
			'customError' => \__('There was an error with your form submission.', 'eightshift-forms'),
			'customSuccess' => \__('Form was successfuly submitted.', 'eightshift-forms'),
			'customSuccessRedirect' => \__('Form was successfuly submitted. Redirecting you now.', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Mailer
	 *
	 * @return array<string, string>
	 */
	private function getMailerLabels(): array
	{
		return [
			'mailerSuccessNoSend' => \__('E-mail was sent successfully.', 'eightshift-forms'),
			'mailerErrorSettingsMissing' => \__('Form settings are not configured correctly. Please try again.', 'eightshift-forms'),
			'mailerErrorEmailSend' => \__('E-mail was not sent due to an unknown issue. Please try again.', 'eightshift-forms'),
			'mailerErrorEmailConfirmationSend' => \__('Confirmation e-mail was not sent due to unknown issue. Please try again.', 'eightshift-forms'),
			'mailerSuccess' => \__('E-mail was sent successfully.', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Greenhouse
	 *
	 * @return array<string, string>
	 */
	private function getGreenhouseLabels(): array
	{
		return [
			'greenhouseErrorSettingsMissing' => \__('Greenhouse integration is not configured correctly. Please try again.', 'eightshift-forms'),
			'greenhouseBadRequestError' => \__('Something is not right with the job application. Please check all the fields and try again.', 'eightshift-forms'),
			'greenhouseUnsupportedFileTypeError' => \__('An unsupported file type was uploaded. Please try again.', 'eightshift-forms'),
			'greenhouseInvalidFirstNameError' => \__('"First name" is in an incorrect format.', 'eightshift-forms'),
			'greenhouseInvalidLastNameError' => \__('"Last name" is in an incorrect format.', 'eightshift-forms'),
			'greenhouseInvalidEmailError' => \__('Enter a valid email address.', 'eightshift-forms'),
			'greenhouseInvalidFirstNameLastNameEmailError' => \__('"First name", "Last name", and "E-mail" are in an incorrect format.', 'eightshift-forms'),
			'greenhouseInvalidFirstNameLastNameError' => \__('"First name" and "Last name" are in an incorrect format.', 'eightshift-forms'),
			'greenhouseInvalidFirstNameEmailError' => \__('"First name" and "E-mail" are in an incorrect format.', 'eightshift-forms'),
			'greenhouseInvalidLastNameEmailError' => \__('"Last name" and "E-mail" are in an incorrect format.', 'eightshift-forms'),
			'greenhouseInvalidFirstNamePhoneError' => \__('"First name" and "Phone" are an incorrect format.', 'eightshift-forms'),
			'greenhouseInvalidLastNamePhoneError' => \__('"First name" and "Phone" are in an incorrect format.', 'eightshift-forms'),
			'greenhouseInvalidEmailPhoneError' => \__('"E-mail" and "Phone" are in an incorrect format.', 'eightshift-forms'),
			'greenhouseInvalidFirstNameLastNameEmailPhoneError' => \__('"First name", "Last name", "E-mail", and "Phone" are in an incorrect format.', 'eightshift-forms'),
			'greenhouseInvalidFirstNameLastNamePhoneError' => \__('"First name", "Last name", and "Phone" are in an incorrect format.', 'eightshift-forms'),
			'greenhouseInvalidFirstNameEmailPhoneError' => \__('"First name", "E-mail", and "Phone" are in an incorrect format.', 'eightshift-forms'),
			'greenhouseInvalidLastNameEmailPhoneError' => \__('"Last name", "E-mail", and "Phone" are in an incorrect format.', 'eightshift-forms'),
			'greenhouseSuccess' => \__('Application submitted successfully. Thank you!', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Mailchimp
	 *
	 * @return array<string, string>
	 */
	private function getMailchimpLabels(): array
	{
		return [
			'mailchimpErrorSettingsMissing' => \__('Mailchimp integration is not configured correctly. Please try again.', 'eightshift-forms'),
			'mailchimpBadRequestError' => \__('Something is not right with the subscription. Please check all the fields and try again.', 'eightshift-forms'),
			'mailchimpInvalidResourceError' => \__('Something is not right with the resource. Please check all the fields and try again.', 'eightshift-forms'),
			'mailchimpInvalidEmailError' => \__('Enter a valid email address.', 'eightshift-forms'),
			'mailchimpMissingFieldsError' => \__('It looks like some required fields are missing. Please check all the fields and try again.', 'eightshift-forms'),
			'mailchimpSuccess' => \__('The newsletter subscription was successful. Thank you!', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - HubSpot
	 *
	 * @return array<string, string>
	 */
	private function getHubspotLabels(): array
	{
		return [
			// Internal.
			'hubspotErrorSettingsMissing' => \__('Hubspot integration is not configured correctly. Please try again.', 'eightshift-forms'),
			'hubspotBadRequestError' => \__('Something is not with the application. Please check all the fields and try again.', 'eightshift-forms'),
			'hubspotInvalidRequestError' => \__('Something is not right with the application. Please check all the fields and try again.', 'eightshift-forms'),
			'hubspotSuccess' => \__('The form was submitted successfully. Thank you!', 'eightshift-forms'),

			// Hubspot.
			'hubspotMaxNumberOfSubmittedValuesExceededError' => \__('More than 1000 fields were included in the response. Please contact website administrator.', 'eightshift-forms'),
			'hubspotInvalidEmailError' => \__('Enter a valid email address.', 'eightshift-forms'),
			'hubspotBlockedEmailError' => \__('We are sorry but you email was blocked in our blacklist.', 'eightshift-forms'),
			'hubspotRequiredFieldError' => \__('Some required fields are not filled in, please check them and try again.', 'eightshift-forms'),
			'hubspotInvalidNumberError' => \__('Some of number fields are not a valid number value.', 'eightshift-forms'),
			'hubspotInputTooLargeError' => \__('The value in the field is too large for the type of field.', 'eightshift-forms'),
			'hubspotFieldNotInFormDefinitionError' => \__('The field was included in the form submission but is not in the form definition.', 'eightshift-forms'),
			'hubspotNumberOutOfRangeError' => \__('The value of a number field outside the range specified in the field settings.', 'eightshift-forms'),
			'hubspotValueNotInFieldDefinitionError' => \__('The value provided for an enumeration field (e.g. checkbox, dropdown, radio) is not one of the possible options.', 'eightshift-forms'),
			'hubspotInvalidMetadataError' => \__('The context object contains an unexpected attribute. Please contact website administrator.', 'eightshift-forms'),
			'hubspotInvalidGotowebinarWebinarKeyError' => \__('The value in goToWebinarWebinarKey in the context object is invalid. Please contact website administrator.', 'eightshift-forms'),
			'hubspotInvalidHutkError' => \__('The hutk field in the context object is invalid. Please contact website administrator.', 'eightshift-forms'),
			'hubspotInvalidIpAddressError' => \__('The ipAddress field in the context object is invalid. Please contact website administrator.', 'eightshift-forms'),
			'hubspotInvalidPageUriError' => \__('The pageUri field in the context object is invalid. Please contact website administrator.', 'eightshift-forms'),
			'hubspotInvalidLegalOptionFormatError' => \__('LegalConsentOptions was empty or it contains both the consent and legitimateInterest fields. Please contact website administrator.', 'eightshift-forms'),
			'hubspotMissingProcessingConsentError' => \__('The consentToProcess field in consent or value field in legitimateInterest was false. Please contact website administrator.', 'eightshift-forms'),
			'hubspotMissingProcessingConsentTextError' => \__('The text field for processing consent was missing. Please contact website administrator.', 'eightshift-forms'),
			'hubspotMissingCommunicationConsentTextError' => \__('The communication consent text was missing for a subscription. Please contact website administrator.', 'eightshift-forms'),
			'hubspotMissingLegitimateInterestTextError' => \__('The legitimate interest consent text was missing. Please contact website administrator.', 'eightshift-forms'),
			'hubspotDuplicateSubscriptionTypeIdError' => \__('The communications list contains two or more items with the same subscriptionTypeId. Please contact website administrator.', 'eightshift-forms'),
			'hubspotHasRecaptchaEnabledError' => \__('Your Hubspot form has reCaptch enabled and we are not able to process the request. Please disable reCaptcha and try again. Please contact website administrator.', 'eightshift-forms'),
			'hubspotError429Error' => \__('The HubSpot account has reached the rate limit. Please contact website administrator.', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Mailerlite
	 *
	 * @return array<string, string>
	 */
	private function getMailerliteLabels(): array
	{
		return [
			'mailerliteErrorSettingsMissing' => \__('MailerLite integration is not configured correctly. Please try again.', 'eightshift-forms'),
			'mailerliteBadRequestError' => \__('Something is not right with the subscription. Please check all the fields and try again.', 'eightshift-forms'),
			'mailerliteInvalidEmailError' => \__('Enter a valid email address.', 'eightshift-forms'),
			'mailerliteEmailTemporarilyBlockedError' => \__('The e-mail is temporarily blocked by our e-mail client. Please try again later or use try a different e-mail.', 'eightshift-forms'),
			'mailerliteSuccess' => \__('The newsletter subscription was successful. Thank you!', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Goodbits
	 *
	 * @return array<string, string>
	 */
	private function getGoodbitsLabels(): array
	{
		return [
			'goodbitsErrorSettingsMissing' => \__('Goodbits integration is not configured correctly. Please try again.', 'eightshift-forms'),
			'goodbitsBadRequestError' => \__('Something is not right with the subscription. Please check all the fields and try again.', 'eightshift-forms'),
			'goodbitsInvalidEmailError' => \__('Enter a valid email address.', 'eightshift-forms'),
			'goodbitsUnauthorizedError' => \__('There was an authorization error (incorrect API key). Contact support.', 'eightshift-forms'),
			'goodbitsSuccess' => \__('The newsletter subscription was successful. Thank you!', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Active Campaign
	 *
	 * @return array<string, string>
	 */
	private function getActiveCampaignLabels(): array
	{
		return [
			'activeCampaignInvalidEmailError' => \__('Enter a valid email address.', 'eightshift-forms'),
			'activeCampaignDuplicateError' => \__('Email address already exists in the system.', 'eightshift-forms'),
			'activeCampaign500Error' => \__('There was an error with the service. Please try again.', 'eightshift-forms'),
			'activeCampaignForbidenError' => \__('It looks like this API key is not authorized to make this request. Please check your API key and try again.', 'eightshift-forms'),
			'activeCampaignSuccess' => \__('The form was submitted successfully. Thank you!', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Google reCaptcha
	 *
	 * @return array<string, string>
	 */
	private function getCaptchaLabels(): array
	{
		return [
			'captchaMissingInputSecret' => \__('The Captcha "secret" parameter is missing.', 'eightshift-forms'),
			'captchaInvalidInputSecret' => \__('The Captcha "secret" parameter is invalid or malformed.', 'eightshift-forms'),
			'captchaInvalidInputResponse' => \__('The Captcha "response" parameter is invalid or malformed.', 'eightshift-forms'),
			'captchaMissingInputResponse' => \__('The Captcha "response" parameter is missing.', 'eightshift-forms'),
			'captchaBadRequest' => \__('The Captcha "request" is invalid or malformed.', 'eightshift-forms'),
			'captchaTimeoutOrDuplicate' => \__('The Captcha response is no longer valid: either is too old or has been used previously.', 'eightshift-forms'),
			'captchaWrongAction' => \__('The Captcha response "action" is not valid.', 'eightshift-forms'),
			'captchaIncorrectCaptchaSol' => \__('The Captcha keys are not valid. Please check your site and secret key configuration.', 'eightshift-forms'),
			'captchaScoreSpam' => \__('The automated system detected this request as a potential spam request. Please try again.', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Airtable
	 *
	 * @return array<string, string>
	 */
	private function getAirtableLabels(): array
	{
		return [
			'airtableNotFoundError' => \__('Airtable integration is not configured correctly. Please try again.', 'eightshift-forms'),
			'airtableInvalidPermissionsOrModelNotFoundError' => \__('Invalid permissions, or the requested model was not found. Check that your token has the required permissions and that the model names and/or ids are correct.', 'eightshift-forms'),
			'airtableInvalidPermissionsError' => \__('You are not permitted to perform this operation.', 'eightshift-forms'),
			'airtableInvalidRequestUnknownError' => \__('Invalid request: parameter validation failed. Check your request data.', 'eightshift-forms'),
			'airtableInvalidValueForColumnError' => \__('One or more fields are invalid. Please try again.', 'eightshift-forms'),
			'airtableSuccess' => \__('The form was submitted successfully. Thank you!', 'eightshift-forms'),
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
			$local = \array_flip(self::ALL_LOCAL_LABELS);

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
