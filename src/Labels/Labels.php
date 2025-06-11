<?php

/**
 * Class that holds all labels.
 *
 * @package EightshiftForms\Labels
 */

declare(strict_types=1);

namespace EightshiftForms\Labels;

use EightshiftForms\Helpers\SettingsHelpers;

/**
 * Labels class.
 */
class Labels implements LabelsInterface
{
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
		'momentsSuccess',
		'workableSuccess',
		'talentlyftSuccess',
		'jiraSuccess',
		'corvusSuccess',
		'paycekSuccess',
		'pipedriveSuccess',
		'calculatorSuccess',
		'nationbuilderSuccess',
	];

	/**
	 * Get all labels
	 *
	 * @return array<string, array<string, string>>
	 */
	public function getLabels(): array
	{
		$output = [
			'validationField' => $this->getValidationFieldLabels(),
			'validationForm' => $this->getValidationFormLabels(),
			'validationSteps' => $this->getValidationStepsLabels(),
			'generic' => $this->getGenericLabels(),
			'validationGeolocation' => $this->getValidationGeolocationLabels(),
			'mailer' => $this->getMailerLabels(),
			'custom' => $this->getCustomLabels(),
			'captcha' => $this->getCaptchaLabels(),
			'greenhouse' => $this->getGreenhouseLabels(),
			'mailchimp' => $this->getMailchimpLabels(),
			'hubspot' => $this->getHubspotLabels(),
			'mailerlite' => $this->getMailerliteLabels(),
			'goodbits' => $this->getGoodbitsLabels(),
			'activeCampaign' => $this->getActiveCampaignLabels(),
			'airtable' => $this->getAirtableLabels(),
			'moments' => $this->getMomentsLabels(),
			'workable' => $this->getWorkableLabels(),
			'talentlyft' => $this->getTalentlyftLabels(),
			'jira' => $this->getJiraLabels(),
			'corvus' => $this->getCorvusLabels(),
			'paycek' => $this->getPaycekLabels(),
			'pipedrive' => $this->getPipedriveLabels(),
			'calculator' => $this->getCalculatorLabels(),
			'nationbuilder' => $this->getNationbuilderLabels(),
		];

		return $output;
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
				$dbLabel = SettingsHelpers::getSettingValue($key, $formId);
			} else {
				$dbLabel = SettingsHelpers::getOptionValue($key);
			}

			// If there is an override in the DB use that.
			if (!empty($dbLabel)) {
				return $dbLabel;
			}
		}

		static $labels = [];

		if (!$labels) {
			$labels = \array_merge(...\array_values($this->getLabels()));
		}

		return $labels[$key] ?? '';
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
	 * Return labels - Validation field.
	 *
	 * @return array<string, string>
	 */
	private function getValidationFieldLabels(): array
	{
		return [
			'validationRequired' => \__('This field is required.', 'eightshift-forms'),
			// translators: %s used for displaying required number.
			'validationRequiredCount' => \__('This field is required, with at least %s items selected.', 'eightshift-forms'),
			'validationInvalid' => \__('This field is not valid.', 'eightshift-forms'),
			'validationEmail' => \__('This e-mail is not valid.', 'eightshift-forms'),
			'validationEmailExists' => \__('This e-mail already exists in our system.', 'eightshift-forms'),
			'validationEmailTld' => \__('This e-mails top level domain is not valid.', 'eightshift-forms'),
			'validationUrl' => \__('This URL is not valid.', 'eightshift-forms'),
			// translators: %s used for displaying min number to the user.
			'validationMin' => \__('This field value is less than expected. Minimal number should be %s.', 'eightshift-forms'),
			// translators: %s used for displaying max number to the user.
			'validationMax' => \__('This field value is more than expected. Maximal number should be %s.', 'eightshift-forms'),
			// translators: %s used for displaying length min number to the user.
			'validationMinLength' => \__('This field value has less characters than expected. We expect minimum %s characters.', 'eightshift-forms'),
			// translators: %s used for displaying length max number to the user.
			'validationMaxLength' => \__('This field value has more characters than expected. We expect maximum %s characters.', 'eightshift-forms'),
			// translators: %s used for displaying length min number to the user.
			'validationMinCount' => \__('This field value has less items than expected. We expect minimum %s items.', 'eightshift-forms'),
			// translators: %s used for displaying length max number to the user.
			'validationMaxCount' => \__('This field value has more items than expected. We expect maximum %s items.', 'eightshift-forms'),
			'validationNumber' => \__('This field should only contain numbers.', 'eightshift-forms'),
			// translators: %s used for displaying validation pattern to the user.
			'validationPattern' => \__('This field value should be in this format: %s.', 'eightshift-forms'),
			// translators: %s used for displaying file type value.
			'validationAccept' => \__('The file type is not supported. Only %s files are allowed.', 'eightshift-forms'),
			// translators: %s used for displaying file type value.
			'validationAcceptMime' => \__('The file seems to be corrupted or invalid format. Only %s are allowed.', 'eightshift-forms'),
			// translators: %s used for displaying file type value.
			'validationAcceptMimeMultiple' => \__('One or more files seem to be corrupt or have invalid format. Only %s are allowed.', 'eightshift-forms'),
			'validationFileWrongUploadPath' => \__('One or more files seem to be uploaded using an unauthorized method.', 'eightshift-forms'),
			'validationFileNotLocated' => \__('It seems that one or more files were not uploaded to the server. Please remove the files and try again.', 'eightshift-forms'),
			'validationFileUpload' => \__('There seems to be an error with the file upload. Please try again.', 'eightshift-forms'),
			'validationFileMaxAmount' => \__('You can only upload a single file in this field. If you have multiple files, please remove them and try again.', 'eightshift-forms'),
			// translators: %s used for displaying number value.
			'validationMinSize' => \__('The file is smaller than allowed. Minimum file size is %s MB.', 'eightshift-forms'),
			// translators: %s used for displaying number value.
			'validationMaxSize' => \__('The file is larger than allowed. Maximum file size is %s MB.', 'eightshift-forms'),
			'validationPhone' => \__('This phone number is not valid. It must contain a valid country/network prefix with only numbers.', 'eightshift-forms'),
			'validationDate' => \__('This date format is not valid.', 'eightshift-forms'),
			'validationDateTime' => \__('This date/time format is not valid.', 'eightshift-forms'),
			'validationDateNoFuture' => \__('This fields only allows dates in the past.', 'eightshift-forms'),
			'validationMailchimpInvalidZip' => \__('This field value has more characters than expected. We expect maximum 5 numbers.', 'eightshift-forms'),
			'validationGreenhouseAcceptMime' => \__('The file seems to be corrupted or invalid format. Only pdf,doc,docx,txt,rtf are allowed.', 'eightshift-forms'),
			'validationMomentsInvalidPhoneLength' => \__('This field has invalid length for phone number.', 'eightshift-forms'),
			'validationMomentsInvalidSpecialCharacters' => \__('This field contains forbidden special characters.', 'eightshift-forms'),
			'validationWorkableMaxLength127' => \__('This field is too long. Max length is 127 characters.', 'eightshift-forms'),
			'validationWorkableMaxLength255' => \__('This field is too long. Max length is 255 characters.', 'eightshift-forms'),
			'validationSecurity' => \__('You have made too many requests in a short time. Please slow down and try again.', 'eightshift-forms'),
			'validationMissingMandatoryParams' => \__('This form is malformed or not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
			'validationSubmitOnce' => \__('This form can be submitted only once.', 'eightshift-forms'),
			'validationSubmitLoggedIn' => \__('This form can be submitted only by logged in users.', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Validation Form global
	 *
	 * @return array<string, string>
	 */
	private function getValidationFormLabels(): array
	{
		return [
			'validationGlobalMissingRequiredParams' => \__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
			'validationFileUploadSuccess' => \__('File uploaded successfully.', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Validation Geolocation
	 *
	 * @return array<string, string>
	 */
	private function getValidationGeolocationLabels(): array
	{
		return [
			'geolocationSkipCheck' => \__('Form geolocation skipped. Feature inactive.', 'eightshift-forms'),
			'geolocationMalformedOrNotValid' => \__('The geolocation data is malformed or not valid.', 'eightshift-forms'),
			'geolocationSuccess' => \__('Success geolocation', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Validation Steps
	 *
	 * @return array<string, string>
	 */
	private function getValidationStepsLabels(): array
	{
		return [
			'validationStepsCurrentStepProblem' => \__('It looks like there is some problem with current step, please try again.', 'eightshift-forms'),
			'validationStepsNextStepProblem' => \__('It looks like there is some problem with next step, please try again.', 'eightshift-forms'),
			'validationStepsParametersProblem' => \__('It looks like there is some problem with parameters sent, please try again.', 'eightshift-forms'),
			'validationStepsSuccess' => \__('Step validation is successful, you may continue.', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Custom action
	 *
	 * @return array<string, string>
	 */
	private function getCustomLabels(): array
	{
		return [
			'customNoAction' => \__('There was an issue with form action. Check the form settings.', 'eightshift-forms'),
			'customError' => \__('There was an error with your form submission.', 'eightshift-forms'),
			'customSuccess' => \__('Form was successfully submitted.', 'eightshift-forms'),
			'customSuccessRedirect' => \__('Form was successfully submitted. Redirecting you now.', 'eightshift-forms'),
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
			'mailerErrorSettingsMissing' => \__('Form is not configured correctly. Please try again.', 'eightshift-forms'),
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
			'hubspotInvalidNumberError' => \__('Some of number fields are not a valid number value.', 'eightshift-forms'),
			'hubspotInputTooLargeError' => \__('The value in the field is too large for the type of field.', 'eightshift-forms'),
			'hubspotFieldNotInFormDefinitionError' => \__('The field was included in the form submission but is not in the form definition.', 'eightshift-forms'),
			'hubspotNumberOutOfRangeError' => \__('The value of a number field outside the range specified in the field settings.', 'eightshift-forms'),
			'hubspotValueNotInFieldDefinitionError' => \__('The value provided for an enumeration field (e.g. checkbox, dropdown, radio) is not one of the possible options.', 'eightshift-forms'),
			'hubspotInvalidMetadataError' => \__('The context object contains an unexpected attribute. Please contact website administrator.', 'eightshift-forms'),
			'hubspotInvalidGotoWebinarKeyError' => \__('The value in goToWebinarWebinarKey in the context object is invalid. Please contact website administrator.', 'eightshift-forms'),
			'hubspotInvalidHutkError' => \__('The hutk field in the context object is invalid. Please contact website administrator.', 'eightshift-forms'),
			'hubspotInvalidIpAddressError' => \__('The ipAddress field in the context object is invalid. Please contact website administrator.', 'eightshift-forms'),
			'hubspotInvalidPageUriError' => \__('The pageUri field in the context object is invalid. Please contact website administrator.', 'eightshift-forms'),
			'hubspotInvalidLegalOptionFormatError' => \__('LegalConsentOptions was empty or it contains both the consent and legitimateInterest fields. Please contact website administrator.', 'eightshift-forms'),
			'hubspotMissingProcessingConsentError' => \__('The consentToProcess field in consent or value field in legitimateInterest was false. Please contact website administrator.', 'eightshift-forms'),
			'hubspotMissingProcessingConsentTextError' => \__('The text field for processing consent was missing. Please contact website administrator.', 'eightshift-forms'),
			'hubspotMissingCommunicationConsentTextError' => \__('The communication consent text was missing for a subscription. Please contact website administrator.', 'eightshift-forms'),
			'hubspotMissingLegitimateInterestTextError' => \__('The legitimate interest consent text was missing. Please contact website administrator.', 'eightshift-forms'),
			'hubspotDuplicateSubscriptionTypeIdError' => \__('The communications list contains two or more items with the same subscriptionTypeId. Please contact website administrator.', 'eightshift-forms'),
			'hubspotHasRecaptchaEnabledError' => \__('Your Hubspot form has reCaptcha enabled and we are not able to process the request. Please disable reCaptcha and try again. Please contact website administrator.', 'eightshift-forms'),
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
			'activeCampaignForbiddenError' => \__('It looks like this API key is not authorized to make this request. Please check your API key and try again.', 'eightshift-forms'),
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
			'captchaSkipCheck' => \__('Form captcha skipped due to troubleshooting config set in settings.', 'eightshift-forms'),
			'captchaBadRequest' => \__('Spam prevention system encountered an error. Captcha "request" is invalid or malformed.', 'eightshift-forms'),
			'captchaWrongAction' => \__('Spam prevention system encountered an error. Captcha response "action" is not valid.', 'eightshift-forms'),
			'captchaScoreSpam' => \__('The request was marked as a potential spam request. Please try again.', 'eightshift-forms'),
			'captchaError' => \__('Spam prevention system encountered an error. Please try again.', 'eightshift-forms'),
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
	 * Return labels - Moments
	 *
	 * @return array<string, string>
	 */
	private function getMomentsLabels(): array
	{
		return [
			'momentsErrorSettingsMissing' => \__('Moments integration is not configured correctly. Please try again.', 'eightshift-forms'),
			'momentsBadRequestError' => \__('Something is not right with the submission. Please check all the fields and try again.', 'eightshift-forms'),
			'momentsSuccess' => \__('The form was submitted successfully. Thank you!', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Workable
	 *
	 * @return array<string, string>
	 */
	private function getWorkableLabels(): array
	{
		return [
			'workableBadRequestError' => \__('Something is not right with the job application. Please check all the fields and try again.', 'eightshift-forms'),
			'workableArchivedJobError' => \__('We apologize, but this job is no longer available. Please try again later, or contact us if you believe this is a mistake.', 'eightshift-forms'),
			'workableTooLongFileNameError' => \__('One of your uploaded files has a filename that is too long. Please reduce the filename and try again.', 'eightshift-forms'),
			'workableSuccess' => \__('Application submitted successfully. Thank you!', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Talentlyft
	 *
	 * @return array<string, string>
	 */
	private function getTalentlyftLabels(): array
	{
		return [
			'talentlyftBadRequestError' => \__('Something is not right with the job application. Please check all the fields and try again.', 'eightshift-forms'),
			'talentlyftValidationError' => \__('It looks like there are some issues with your form fields. Please check all the fields and try again.', 'eightshift-forms'),
			'talentlyftSuccess' => \__('Application submitted successfully. Thank you!', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Jira
	 *
	 * @return array<string, string>
	 */
	private function getJiraLabels(): array
	{
		return [
			'jiraMissingProject' => \__('Your form is missing project key. Please try again.', 'eightshift-forms'),
			'jiraMissingIssueType' => \__('Your form is missing issue type. Please try again.', 'eightshift-forms'),
			'jiraMissingSummary' => \__('Your form is missing issue summary. Please try again.', 'eightshift-forms'),
			'jiraBadRequestError' => \__('Something is not right with the job application. Please check all the fields and try again.', 'eightshift-forms'),
			'jiraSuccess' => \__('Application submitted successfully. Thank you!', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Corvus
	 *
	 * @return array<string, string>
	 */
	private function getCorvusLabels(): array
	{
		return [
			'corvusMissingReqParams' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
			'corvusMissingConfig' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
			'corvusSuccess' => \__('Application submitted successfully. Thank you!', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Paycek
	 *
	 * @return array<string, string>
	 */
	private function getPaycekLabels(): array
	{
		return [
			'paycekMissingReqParams' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
			'paycekMissingConfig' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
			'paycekSuccess' => \__('Payment submitted successfully. Thank you!', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Pipedrive
	 *
	 * @return array<string, string>
	 */
	private function getPipedriveLabels(): array
	{
		return [
			'pipedriveMissingName' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
			'pipedriveMissingOrganization' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
			'pipedriveWrongOrganizationId' => \__('Organization ID is invalid.', 'eightshift-forms'),
			'pipedriveWrongDataset' => \__('Integration dataset is invalid.', 'eightshift-forms'),
			'pipedriveBadRequestError' => \__('Something is not right with the job application. Please check all the fields and try again.', 'eightshift-forms'),
			'pipedriveSuccess' => \__('Application submitted successfully. Thank you!', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Calculator
	 *
	 * @return array<string, string>
	 */
	private function getCalculatorLabels(): array
	{
		return [
			'calculatorErrorSettingsMissing' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
			'calculatorBadRequestError' => \__('Something is not right with the subscription. Please check all the fields and try again.', 'eightshift-forms'),
			'calculatorSuccess' => \__('Application submitted successfully. Thank you!', 'eightshift-forms'),
		];
	}

	/**
	 * Return labels - Nationbuilder
	 *
	 * @return array<string, string>
	 */
	private function getNationbuilderLabels(): array
	{
		return [
			'nationbuilderErrorSettingsMissing' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
			'nationbuilderServerError' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
			'nationbuilderBadRequestError' => \__('Something is not right with the subscription. Please check all the fields and try again.', 'eightshift-forms'),
			'nationbuilderSuccess' => \__('Application submitted successfully. Thank you!', 'eightshift-forms'),
		];
	}
}
