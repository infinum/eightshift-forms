<?php

/**
 * Class that holds all labels.
 *
 * @package EightshiftForms\Labels
 */

declare(strict_types=1);

namespace EightshiftForms\Labels;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Captcha\SettingsCaptcha;
use EightshiftForms\CronJobs\SettingsCronJobs;
use EightshiftForms\Geolocation\SettingsGeolocation;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Integrations\ActiveCampaign\SettingsActiveCampaign;
use EightshiftForms\Integrations\Airtable\SettingsAirtable;
use EightshiftForms\Integrations\Calculator\SettingsCalculator;
use EightshiftForms\Integrations\Clearbit\SettingsClearbit;
use EightshiftForms\Integrations\Corvus\SettingsCorvus;
use EightshiftForms\Integrations\Goodbits\SettingsGoodbits;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
use EightshiftForms\Integrations\Jira\SettingsJira;
use EightshiftForms\Integrations\Mailchimp\SettingsMailchimp;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Integrations\Mailerlite\SettingsMailerlite;
use EightshiftForms\Integrations\Moments\SettingsMoments;
use EightshiftForms\Integrations\Nationbuilder\SettingsNationbuilder;
use EightshiftForms\Integrations\Pardot\SettingsPardot;
use EightshiftForms\Integrations\Paycek\SettingsPaycek;
use EightshiftForms\Integrations\Pipedrive\SettingsPipedrive;
use EightshiftForms\Integrations\Talentlyft\SettingsTalentlyft;
use EightshiftForms\Integrations\Workable\SettingsWorkable;
use EightshiftForms\Migration\SettingsMigration;
use EightshiftForms\Transfer\SettingsTransfer;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftForms\Validation\SettingsValidation;

/**
 * Labels class.
 */
class Labels
{
	public const TYPE_GENERIC = 'generic';

	public const LABEL_ACTIVE_CAMPAIGN_MISSING_CONFIG = 'activeCampaignMissingConfig';
	public const LABEL_ACTIVE_CAMPAIGN_INVALID_EMAIL_ERROR = 'activeCampaignInvalidEmailError';
	public const LABEL_ACTIVE_CAMPAIGN_DUPLICATE_ERROR = 'activeCampaignDuplicateError';
	public const LABEL_ACTIVE_CAMPAIGN500_ERROR = 'activeCampaign500Error';
	public const LABEL_ACTIVE_CAMPAIGN_FORBIDDEN_ERROR = 'activeCampaignForbiddenError';
	public const LABEL_ACTIVE_CAMPAIGN_SUCCESS = 'activeCampaignSuccess';

	public const LABEL_AIRTABLE_MISSING_CONFIG = 'airtableMissingConfig';
	public const LABEL_AIRTABLE_NOT_FOUND_ERROR = 'airtableNotFoundError';
	public const LABEL_AIRTABLE_INVALID_PERMISSIONS_OR_MODEL_NOT_FOUND_ERROR = 'airtableInvalidPermissionsOrModelNotFoundError';
	public const LABEL_AIRTABLE_INVALID_PERMISSIONS_ERROR = 'airtableInvalidPermissionsError';
	public const LABEL_AIRTABLE_INVALID_REQUEST_UNKNOWN_ERROR = 'airtableInvalidRequestUnknownError';
	public const LABEL_AIRTABLE_INVALID_VALUE_FOR_COLUMN_ERROR = 'airtableInvalidValueForColumnError';
	public const LABEL_AIRTABLE_SUCCESS = 'airtableSuccess';

	public const LABEL_CACHE_TYPE_NOT_FOUND = 'cacheTypeNotFound';
	public const LABEL_CACHE_DELETED_SUCCESS = 'cacheDeletedSuccess';

	public const LABEL_CALCULATOR_MISSING_CONFIG = 'calculatorMissingConfig';
	public const LABEL_CALCULATOR_SUCCESS = 'calculatorSuccess';
	public const LABEL_CALCULATOR_BAD_REQUEST_ERROR = 'calculatorBadRequestError';
	public const LABEL_CALCULATOR_ERROR_SETTINGS_MISSING = 'calculatorErrorSettingsMissing';

	public const LABEL_CAPTCHA_FEATURE_DISABLED = 'captchaFeatureDisabled';
	public const LABEL_CAPTCHA_REQUEST_MISSING_TOKEN = 'captchaRequestMissingToken';
	public const LABEL_CAPTCHA_REQUEST_WP_ERROR = 'captchaRequestWpError';
	public const LABEL_CAPTCHA_FREE_OUTPUT_ERROR = 'captchaFreeOutputError';
	public const LABEL_CAPTCHA_ENTERPRISE_OUTPUT_ERROR = 'captchaEnterpriseOutputError';
	public const LABEL_CAPTCHA_WRONG_ACTION = 'captchaWrongAction';
	public const LABEL_CAPTCHA_SCORE_SPAM = 'captchaScoreSpam';
	public const LABEL_CAPTCHA_SUCCESS = 'captchaSuccess';
	public const LABEL_CAPTCHA_DEBUG_SKIP_CHECK = 'captchaDebugSkipCheck';
	public const LABEL_CAPTCHA_MISSING_CONFIG = 'captchaMissingConfig';
	public const LABEL_CAPTCHA_SKIP_CHECK = 'captchaSkipCheck';
	public const LABEL_CAPTCHA_BAD_REQUEST = 'captchaBadRequest';
	public const LABEL_CAPTCHA_ERROR = 'captchaError';
	public const LABEL_CAPTCHA_FRIENDLY_OUTPUT_ERROR = 'friendlyCaptchaOutputError';
	public const LABEL_CAPTCHA_FRIENDLY_HTTP_ERROR = 'friendlyCaptchaHttpError';
	public const LABEL_CAPTCHA_FRIENDLY_AUTH_ERROR = 'friendlyCaptchaAuthError';
	public const LABEL_CAPTCHA_FRIENDLY_BAD_REQUEST = 'friendlyCaptchaBadRequest';
	public const LABEL_CAPTCHA_FRIENDLY_INVALID_SOLUTION = 'friendlyCaptchaInvalidSolution';
	public const LABEL_CAPTCHA_FRIENDLY_TIMEOUT_OR_DUPLICATE = 'friendlyCaptchaTimeoutOrDuplicate';
	public const LABEL_CAPTCHA_FRIENDLY_ERROR = 'friendlyCaptchaError';
	public const LABEL_CAPTCHA_FRIENDLY_SUCCESS = 'friendlyCaptchaSuccess';

	public const LABEL_CLEARBIT_CRON_ERROR = 'clearbitCronError';
	public const LABEL_CLEARBIT_AUTH_REQUIRED_ERROR = 'clearbitAuthRequiredError';
	public const LABEL_CLEARBIT_INVALID_EMAIL_ERROR = 'clearbitInvalidEmailError';

	public const LABEL_CORVUS_MISSING_CONFIG = 'corvusMissingConfig';
	public const LABEL_CORVUS_MISSING_REQ_PARAMS = 'corvusMissingReqParams';
	public const LABEL_CORVUS_MISSING_STORE_ID = 'corvusMissingStoreId';
	public const LABEL_CORVUS_SUCCESS = 'corvusSuccess';

	public const LABEL_CRON_RUN_SUCCESS = 'cronRunSuccess';
	public const LABEL_CRON_RUN_NOT_FOUND = 'cronRunNotFound';

	public const LABEL_CUSTOM_NO_ACTION = 'customNoAction';
	public const LABEL_CUSTOM_SUCCESS_REDIRECT = 'customSuccessRedirect';
	public const LABEL_CUSTOM_ERROR = 'customError';
	public const LABEL_CUSTOM_WP_ERROR = 'customWpError';
	public const LABEL_CUSTOM_SUCCESS = 'customSuccess';
	public const LABEL_CUSTOM_MISSING_CONFIG = 'customMissingConfig';

	public const LABEL_ENCRYPT_FAILED = 'encryptFailed';
	public const LABEL_DECRYPT_FAILED = 'decryptFailed';
	public const LABEL_ENCRYPT_SUCCESS = 'encryptSuccess';
	public const LABEL_DECRYPT_SUCCESS = 'decryptSuccess';

	public const LABEL_SUBMIT_INTEGRATION_SUCCESS = 'submitIntegrationSuccess';
	public const LABEL_SUBMIT_INTEGRATION_ERROR_WP = 'submitWpError';
	public const LABEL_SUBMIT_FALLBACK_ERROR = 'submitFallbackError';
	public const LABEL_TEST_API_SUCCESS = 'testApiSuccess';
	public const LABEL_TEST_API_ERROR = 'testApiError';
	public const LABEL_GLOBAL_NOT_CONFIGURED = 'globalNotConfigured';
	public const LABEL_INTEGRATION_ITEMS_MISSING = 'integrationItemsMissing';
	public const LABEL_INTEGRATION_ITEMS_SUCCESS = 'integrationItemsSuccess';
	public const LABEL_FORM_FIELDS_MISSING = 'formFieldsMissing';
	public const LABEL_FORM_FIELDS_SUCCESS = 'formFieldsSuccess';
	public const LABEL_INCREMENT_RESET_SUCCESS = 'incrementResetSuccess';
	public const LABEL_LOCATIONS_RESULT_OUTPUT_ERROR = 'locationsResultOutputError';
	public const LABEL_LOCATIONS_FORM_ERROR = 'locationsFormError';
	public const LABEL_LOCATIONS_SUCCESS = 'locationsSuccess';
	public const LABEL_BULK_MISSING_ITEMS = 'bulkMissingItems';
	public const LABEL_GENERIC_SUCCESS = 'genericSuccess';
	public const LABEL_GENERIC_WARNING = 'genericWarning';
	public const LABEL_GENERIC_ERROR = 'genericError';
	public const LABEL_SETTINGS_SUCCESS = 'settingsSuccess';

	public const LABEL_GEOLOCATION_FEATURE_DISABLED = 'geolocationFeatureDisabled';
	public const LABEL_GEOLOCATION_MALFORMED_DECRYPT_DATA = 'geolocationMalformedDecryptData';
	public const LABEL_GEOLOCATION_DETECTION_FAILED = 'geolocationDetectionFailed';
	public const LABEL_GEOLOCATION_SUCCESS = 'geolocationSuccess';
	public const LABEL_GEOLOCATION_SKIP_CHECK = 'geolocationSkipCheck';
	public const LABEL_GEOLOCATION_MALFORMED_OR_NOT_VALID = 'geolocationMalformedOrNotValid';
	public const LABEL_GEOLOCATION_COUNTRIES_MISSING = 'geolocationCountriesMissing';
	public const LABEL_GEOLOCATION_COUNTRIES_SUCCESS = 'geolocationCountriesSuccess';

	public const LABEL_GOODBITS_MISSING_CONFIG = 'goodbitsMissingConfig';
	public const LABEL_GOODBITS_BAD_REQUEST_ERROR = 'goodbitsBadRequestError';
	public const LABEL_GOODBITS_SUCCESS = 'goodbitsSuccess';

	public const LABEL_GREENHOUSE_MISSING_CONFIG = 'greenhouseMissingConfig';
	public const LABEL_GREENHOUSE_BAD_REQUEST_ERROR = 'greenhouseBadRequestError';
	public const LABEL_GREENHOUSE_SUCCESS = 'greenhouseSuccess';

	public const LABEL_HUBSPOT_MISSING_CONFIG = 'hubspotMissingConfig';
	public const LABEL_HUBSPOT_BAD_REQUEST_ERROR = 'hubspotBadRequestError';
	public const LABEL_HUBSPOT_INVALID_REQUEST_ERROR = 'hubspotInvalidRequestError';
	public const LABEL_HUBSPOT_MAX_NUMBER_OF_SUBMITTED_VALUES_EXCEEDED_ERROR = 'hubspotMaxNumberOfSubmittedValuesExceededError';
	public const LABEL_HUBSPOT_INVALID_EMAIL_ERROR = 'hubspotInvalidEmailError';
	public const LABEL_HUBSPOT_BLOCKED_EMAIL_ERROR = 'hubspotBlockedEmailError';
	public const LABEL_HUBSPOT_INVALID_NUMBER_ERROR = 'hubspotInvalidNumberError';
	public const LABEL_HUBSPOT_INPUT_TOO_LARGE_ERROR = 'hubspotInputTooLargeError';
	public const LABEL_HUBSPOT_FIELD_NOT_IN_FORM_DEFINITION_ERROR = 'hubspotFieldNotInFormDefinitionError';
	public const LABEL_HUBSPOT_NUMBER_OUT_OF_RANGE_ERROR = 'hubspotNumberOutOfRangeError';
	public const LABEL_HUBSPOT_VALUE_NOT_IN_FIELD_DEFINITION_ERROR = 'hubspotValueNotInFieldDefinitionError';
	public const LABEL_HUBSPOT_INVALID_METADATA_ERROR = 'hubspotInvalidMetadataError';
	public const LABEL_HUBSPOT_INVALID_GOTOWEBINAR_WEBINAR_KEY_ERROR = 'hubspotInvalidGotoWebinarKeyError';
	public const LABEL_HUBSPOT_INVALID_HUTK_ERROR = 'hubspotInvalidHutkError';
	public const LABEL_HUBSPOT_INVALID_IP_ADDRESS_ERROR = 'hubspotInvalidIpAddressError';
	public const LABEL_HUBSPOT_INVALID_PAGE_URI_ERROR = 'hubspotInvalidPageUriError';
	public const LABEL_HUBSPOT_INVALID_LEGAL_OPTION_FORMAT_ERROR = 'hubspotInvalidLegalOptionFormatError';
	public const LABEL_HUBSPOT_MISSING_PROCESSING_CONSENT_ERROR = 'hubspotMissingProcessingConsentError';
	public const LABEL_HUBSPOT_MISSING_PROCESSING_CONSENT_TEXT_ERROR = 'hubspotMissingProcessingConsentTextError';
	public const LABEL_HUBSPOT_MISSING_COMMUNICATION_CONSENT_TEXT_ERROR = 'hubspotMissingCommunicationConsentTextError';
	public const LABEL_HUBSPOT_MISSING_LEGITIMATE_INTEREST_TEXT_ERROR = 'hubspotMissingLegitimateInterestTextError';
	public const LABEL_HUBSPOT_DUPLICATE_SUBSCRIPTION_TYPE_ID_ERROR = 'hubspotDuplicateSubscriptionTypeIdError';
	public const LABEL_HUBSPOT_FORM_HAS_RECAPTCHA_ENABLED_ERROR = 'hubspotHasRecaptchaEnabledError';
	public const LABEL_HUBSPOT_ERROR_429_ERROR = 'hubspotError429Error';
	public const LABEL_HUBSPOT_SUCCESS = 'hubspotSuccess';

	public const LABEL_JIRA_MISSING_CONFIG = 'jiraMissingConfig';
	public const LABEL_JIRA_MISSING_PROJECT = 'jiraMissingProject';
	public const LABEL_JIRA_MISSING_ISSUE_TYPE = 'jiraMissingIssueType';
	public const LABEL_JIRA_MISSING_SUMMARY = 'jiraMissingSummary';
	public const LABEL_JIRA_MISSING_EPIC_NAME = 'jiraMissingEpicName';
	public const LABEL_JIRA_AUTH_REQUIRED_ERROR = 'jiraAuthRequiredError';
	public const LABEL_JIRA_INVALID_EMAIL_ERROR = 'jiraInvalidEmailError';
	public const LABEL_JIRA_BAD_REQUEST_ERROR = 'jiraBadRequestError';
	public const LABEL_JIRA_SUCCESS = 'jiraSuccess';

	public const LABEL_MAILCHIMP_MISSING_CONFIG = 'mailchimpMissingConfig';
	public const LABEL_MAILCHIMP_BAD_REQUEST_ERROR = 'mailchimpBadRequestError';
	public const LABEL_MAILCHIMP_SUCCESS = 'mailchimpSuccess';

	public const LABEL_MAILER_MISSING_CONFIG = 'mailerMissingConfig';
	public const LABEL_MAILER_ERROR_EMAIL_SEND = 'mailerErrorEmailSend';
	public const LABEL_MAILER_SUCCESS = 'mailerSuccess';
	public const LABEL_MAILER_ERROR_EMAIL_CONFIRMATION_SEND = 'mailerErrorEmailConfirmationSend';

	public const LABEL_MAILERLITE_MISSING_CONFIG = 'mailerliteMissingConfig';
	public const LABEL_MAILERLITE_BAD_REQUEST_ERROR = 'mailerliteBadRequestError';
	public const LABEL_MAILERLITE_SUCCESS = 'mailerliteSuccess';

	public const LABEL_MIGRATION_TYPE_NOT_FOUND = 'migrationTypeNotFound';
	public const LABEL_MIGRATION_SUCCESS = 'migrationSuccess';

	public const LABEL_MOMENTS_BAD_REQUEST_ERROR = 'momentsBadRequestError';
	public const LABEL_MOMENTS_MISSING_CONFIG = 'momentsMissingConfig';
	public const LABEL_MOMENTS_EVENTS_ERROR = 'momentsEventsError';
	public const LABEL_MOMENTS_SUCCESS = 'momentsSuccess';

	public const LABEL_NATIONBUILDER_MISSING_CONFIG = 'nationbuilderMissingConfig';
	public const LABEL_NATIONBUILDER_LIST_ERROR = 'nationbuilderListError';
	public const LABEL_NATIONBUILDER_TAGS_ERROR = 'nationbuilderTagsError';
	public const LABEL_NATIONBUILDER_BAD_REQUEST_ERROR = 'nationbuilderBadRequestError';
	public const LABEL_NATIONBUILDER_ERROR_SETTINGS_MISSING = 'nationbuilderErrorSettingsMissing';
	public const LABEL_NATIONBUILDER_SERVER_ERROR = 'nationbuilderServerError';
	public const LABEL_NATIONBUILDER_SUCCESS = 'nationbuilderSuccess';

	public const LABEL_PARDOT_MISSING_CONFIG = 'pardotMissingConfig';
	public const LABEL_PARDOT_BAD_REQUEST_ERROR = 'pardotBadRequestError';
	public const LABEL_PARDOT_ERROR_SETTINGS_MISSING = 'pardotErrorSettingsMissing';
	public const LABEL_PARDOT_SERVER_ERROR = 'pardotServerError';
	public const LABEL_PARDOT_SUCCESS = 'pardotSuccess';

	public const LABEL_PAYCEK_MISSING_CONFIG = 'paycekMissingConfig';
	public const LABEL_PAYCEK_MISSING_REQ_PARAMS = 'paycekMissingReqParams';
	public const LABEL_PAYCEK_SUCCESS = 'paycekSuccess';

	public const LABEL_PIPEDRIVE_MISSING_CONFIG = 'pipedriveMissingConfig';
	public const LABEL_PIPEDRIVE_MISSING_NAME = 'pipedriveMissingName';
	public const LABEL_PIPEDRIVE_MISSING_ORGANIZATION = 'pipedriveMissingOrganization';
	public const LABEL_PIPEDRIVE_WRONG_ORGANIZATION_ID = 'pipedriveWrongOrganizationId';
	public const LABEL_PIPEDRIVE_WRONG_DATASET = 'pipedriveWrongDataset';
	public const LABEL_PIPEDRIVE_BAD_REQUEST_ERROR = 'pipedriveBadRequestError';
	public const LABEL_PIPEDRIVE_SUCCESS = 'pipedriveSuccess';

	public const LABEL_TALENTLYFT_MISSING_CONFIG = 'talentlyftMissingConfig';
	public const LABEL_TALENTLYFT_BAD_REQUEST_ERROR = 'talentlyftBadRequestError';
	public const LABEL_TALENTLYFT_VALIDATION_ERROR = 'talentlyftValidationError';
	public const LABEL_TALENTLYFT_SUCCESS = 'talentlyftSuccess';

	public const LABEL_TRANSFER_EXPORT_MISSING_FORMS = 'transferExportMissingForms';
	public const LABEL_TRANSFER_EXPORT_MISSING_RESULT_OUTPUTS = 'transferExportMissingResultOutputs';
	public const LABEL_TRANSFER_UPLOAD_MISSING_FILE = 'transferUploadMissingFile';
	public const LABEL_TRANSFER_UPLOAD_ERROR = 'transferUploadError';
	public const LABEL_TRANSFER_UPLOAD_MISSING_TYPE = 'transferUploadMissingType';
	public const LABEL_TRANSFER_SUCCESS = 'transferSuccess';
	public const LABEL_EXPORT_MISSING_ITEMS = 'exportMissingItems';
	public const LABEL_EXPORT_DATA_EMPTY = 'exportDataEmpty';
	public const LABEL_EXPORT_SUCCESS = 'exportSuccess';

	public const LABEL_PERMISSION_DENIED = 'permissionDenied';
	public const LABEL_VALIDATION_MISSING_MANDATORY_PARAMS = 'validationMissingMandatoryParams';
	public const LABEL_VALIDATION_SUBMIT_LOGGED_IN = 'validationSubmitLoggedIn';
	public const LABEL_VALIDATION_SUBMIT_ONCE = 'validationSubmitOnce';
	public const LABEL_VALIDATION_SECURITY = 'validationSecurity';
	public const LABEL_VALIDATION_PARAMS = 'validationParams';
	public const LABEL_VALIDATION_FILES = 'validationFiles';
	public const LABEL_FILES_UPLOAD_SUCCESS = 'filesUploadSuccess';
	public const LABEL_FILES_UPLOAD_ERROR = 'filesUploadError';
	public const LABEL_VALIDATION_STEPS_CURRENT_STEP_PROBLEM = 'validationStepsCurrentStepProblem';
	public const LABEL_VALIDATION_STEPS_FIELDS_PROBLEM = 'validationStepsFieldsProblem';
	public const LABEL_VALIDATION_STEPS_NEXT_STEP_PROBLEM = 'validationStepsNextStepProblem';
	public const LABEL_VALIDATION_STEPS_PARAMETERS_PROBLEM = 'validationStepsParametersProblem';
	public const LABEL_VALIDATION_STEPS_SUCCESS = 'validationStepsSuccess';
	public const LABEL_VALIDATION_REQUIRED = 'validationRequired';
	public const LABEL_VALIDATION_REQUIRED_COUNT = 'validationRequiredCount';
	public const LABEL_VALIDATION_INVALID = 'validationInvalid';
	public const LABEL_VALIDATION_EMAIL = 'validationEmail';
	public const LABEL_VALIDATION_EMAIL_EXISTS = 'validationEmailExists';
	public const LABEL_VALIDATION_EMAIL_TLD = 'validationEmailTld';
	public const LABEL_VALIDATION_URL = 'validationUrl';
	public const LABEL_VALIDATION_MIN = 'validationMin';
	public const LABEL_VALIDATION_MAX = 'validationMax';
	public const LABEL_VALIDATION_MIN_LENGTH = 'validationMinLength';
	public const LABEL_VALIDATION_MAX_LENGTH = 'validationMaxLength';
	public const LABEL_VALIDATION_MIN_COUNT = 'validationMinCount';
	public const LABEL_VALIDATION_MAX_COUNT = 'validationMaxCount';
	public const LABEL_VALIDATION_NUMBER = 'validationNumber';
	public const LABEL_VALIDATION_PATTERN = 'validationPattern';
	public const LABEL_VALIDATION_ACCEPT = 'validationAccept';
	public const LABEL_VALIDATION_ACCEPT_MIME = 'validationAcceptMime';
	public const LABEL_VALIDATION_ACCEPT_MIME_MULTIPLE = 'validationAcceptMimeMultiple';
	public const LABEL_VALIDATION_FILE_WRONG_UPLOAD_PATH = 'validationFileWrongUploadPath';
	public const LABEL_VALIDATION_FILE_NOT_LOCATED = 'validationFileNotLocated';
	public const LABEL_VALIDATION_FILE_UPLOAD = 'validationFileUpload';
	public const LABEL_VALIDATION_FILE_MAX_AMOUNT = 'validationFileMaxAmount';
	public const LABEL_VALIDATION_MIN_SIZE = 'validationMinSize';
	public const LABEL_VALIDATION_MAX_SIZE = 'validationMaxSize';
	public const LABEL_VALIDATION_PHONE = 'validationPhone';
	public const LABEL_VALIDATION_DATE = 'validationDate';
	public const LABEL_VALIDATION_DATE_TIME = 'validationDateTime';
	public const LABEL_VALIDATION_DATE_NO_FUTURE = 'validationDateNoFuture';
	public const LABEL_VALIDATION_MAILCHIMP_INVALID_ZIP = 'validationMailchimpInvalidZip';
	public const LABEL_VALIDATION_GREENHOUSE_ACCEPT_MIME = 'validationGreenhouseAcceptMime';
	public const LABEL_VALIDATION_MOMENTS_INVALID_PHONE_LENGTH = 'validationMomentsInvalidPhoneLength';
	public const LABEL_VALIDATION_MOMENTS_INVALID_SPECIAL_CHARACTERS = 'validationMomentsInvalidSpecialCharacters';
	public const LABEL_VALIDATION_WORKABLE_MAX_LENGTH127 = 'validationWorkableMaxLength127';
	public const LABEL_VALIDATION_WORKABLE_MAX_LENGTH255 = 'validationWorkableMaxLength255';
	public const LABEL_VALIDATION_FILE_EXTENSION_DENIED = 'validationFileExtensionDenied';
	public const LABEL_VALIDATION_FILE_MIME_MISMATCH = 'validationFileMimeMismatch';
	public const LABEL_VALIDATION_FILE_MIME_NOT_ALLOWED = 'validationFileMimeNotAllowed';
	public const LABEL_VALIDATION_FILE_SCAN_FAILED = 'validationFileScanFailed';
	public const LABEL_VALIDATION_FILE_PDF_UNSAFE = 'validationFilePdfUnsafe';
	public const LABEL_VALIDATION_FILE_IMAGE_UNSAFE = 'validationFileImageUnsafe';
	public const LABEL_VALIDATION_FILE_OFFICE_UNSAFE = 'validationFileOfficeUnsafe';
	public const LABEL_VALIDATION_FILE_CSV_UNSAFE = 'validationFileCsvUnsafe';
	public const LABEL_VALIDATION_FILE_ARCHIVE_UNSAFE = 'validationFileArchiveUnsafe';
	public const LABEL_VALIDATION_FILE_TEXT_UNSAFE = 'validationFileTextUnsafe';
	public const LABEL_VALIDATION_GLOBAL_MISSING_REQUIRED_PARAMS = 'validationGlobalMissingRequiredParams';
	public const LABEL_VALIDATION_FILE_UPLOAD_SUCCESS = 'validationFileUploadSuccess';

	public const LABEL_WORKABLE_MISSING_CONFIG = 'workableMissingConfig';
	public const LABEL_WORKABLE_BAD_REQUEST_ERROR = 'workableBadRequestError';
	public const LABEL_WORKABLE_ARCHIVED_JOB_ERROR = 'workableArchivedJobError';
	public const LABEL_WORKABLE_TOO_LONG_FILE_NAME_ERROR = 'workableTooLongFileNameError';
	public const LABEL_WORKABLE_SUCCESS = 'workableSuccess';
	/**
	 * List all label keys that are stored in local form everything else is global settings.
	 */
	public const ALL_LOCAL_LABELS = [
		self::LABEL_MAILER_SUCCESS,
		self::LABEL_GREENHOUSE_SUCCESS,
		self::LABEL_MAILCHIMP_SUCCESS,
		self::LABEL_HUBSPOT_SUCCESS,
		self::LABEL_MAILERLITE_SUCCESS,
		self::LABEL_GOODBITS_SUCCESS,
		self::LABEL_CUSTOM_SUCCESS,
		self::LABEL_ACTIVE_CAMPAIGN_SUCCESS,
		self::LABEL_AIRTABLE_SUCCESS,
		self::LABEL_MOMENTS_SUCCESS,
		self::LABEL_WORKABLE_SUCCESS,
		self::LABEL_TALENTLYFT_SUCCESS,
		self::LABEL_JIRA_SUCCESS,
		self::LABEL_CORVUS_SUCCESS,
		self::LABEL_PAYCEK_SUCCESS,
		self::LABEL_PIPEDRIVE_SUCCESS,
		self::LABEL_CALCULATOR_SUCCESS,
		self::LABEL_NATIONBUILDER_SUCCESS,
		self::LABEL_PARDOT_SUCCESS,
	];

	/**
	 * Get all labels
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function getLabels(): array
	{
		$output = [];

		$excludeCheck = [
			self::TYPE_GENERIC => 0,
			SettingsValidation::SETTINGS_TYPE_KEY => 0,
			SettingsCronJobs::SETTINGS_TYPE_KEY => 0,
			SettingsCache::SETTINGS_TYPE_KEY => 0,
		];

		foreach (self::getFlagsList() as $key => $flag) {
			$outputKey = $flag['output'] ?? '';
			$type = $flag['type'] ?? '';

			if (!$outputKey || !$type) {
				continue;
			}

			if (!SettingsHelpers::isOptionTypeActive($type) && !isset($excludeCheck[$type])) {
				continue;
			}

			$output[$type][$key] = $outputKey;
		}

		return $output;
	}

	/**
	 * Return one label by key
	 *
	 * @param string $key Label key.
	 * @param string $formId Form ID.
	 */
	public static function getLabel(string $key, string $formId = ''): string
	{
		// If form ID is not missing check form settings for the overrides.
		if ($formId !== '' && $formId !== '0') {
			$local = \array_flip(self::ALL_LOCAL_LABELS);

			if (isset($local[$key])) {
				$dbLabel = SettingsHelpers::getSettingValue($key, $formId);
			} else {
				$dbLabel = SettingsHelpers::getOptionValue($key);
			}

			// If there is an override in the DB use that.
			if ($dbLabel !== '' && $dbLabel !== '0') {
				return \esc_html($dbLabel);
			}
		}

		return \esc_html(self::getFlagsList()[$key]['output'] ?? '');
	}

	/**
	 * Get flags list.
	 *
	 * @return array<string, mixed>
	 */
	public static function getFlagsList(): array
	{
		static $labels = [];

		if (!$labels) {
			$labels = [
				// ActiveCampaign.
				self::LABEL_ACTIVE_CAMPAIGN_MISSING_CONFIG => [
					'type' => SettingsActiveCampaign::SETTINGS_TYPE_KEY,
					'label' => \__('When ActiveCampaign integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_ACTIVE_CAMPAIGN_INVALID_EMAIL_ERROR => [
					'type' => SettingsActiveCampaign::SETTINGS_TYPE_KEY,
					'label' => \__('When ActiveCampaign integration returns an invalid email error.', 'eightshift-forms'),
					'output' => \__('Enter a valid email address.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_ACTIVE_CAMPAIGN_DUPLICATE_ERROR => [
					'type' => SettingsActiveCampaign::SETTINGS_TYPE_KEY,
					'label' => \__('When ActiveCampaign integration returns a duplicate email error.', 'eightshift-forms'),
					'output' => \__('Email address already exists in the system.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_ACTIVE_CAMPAIGN500_ERROR => [
					'type' => SettingsActiveCampaign::SETTINGS_TYPE_KEY,
					'label' => \__('When ActiveCampaign integration returns a server error.', 'eightshift-forms'),
					'output' => \__('There was an error with the service. Please try again.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_ACTIVE_CAMPAIGN_FORBIDDEN_ERROR => [
					'type' => SettingsActiveCampaign::SETTINGS_TYPE_KEY,
					'label' => \__('When ActiveCampaign integration returns a forbidden error due to an unauthorized API key.', 'eightshift-forms'),
					'output' => \__('It looks like this API key is not authorized to make this request. Please check your API key and try again.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_ACTIVE_CAMPAIGN_SUCCESS => [
					'type' => SettingsActiveCampaign::SETTINGS_TYPE_KEY,
					'label' => \__('When ActiveCampaign integration submits the form successfully.', 'eightshift-forms'),
					'output' => \__('The form was submitted successfully. Thank you!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Airtable.
				self::LABEL_AIRTABLE_MISSING_CONFIG => [
					'type' => SettingsAirtable::SETTINGS_TYPE_KEY,
					'label' => \__('When Airtable integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_AIRTABLE_NOT_FOUND_ERROR => [
					'type' => SettingsAirtable::SETTINGS_TYPE_KEY,
					'label' => \__('When Airtable integrations returns a not found error.', 'eightshift-forms'),
					'output' => \__('Airtable integration is not configured correctly. Please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_AIRTABLE_INVALID_PERMISSIONS_OR_MODEL_NOT_FOUND_ERROR => [
					'type' => SettingsAirtable::SETTINGS_TYPE_KEY,
					'label' => \__('When Airtable integrations returns a invalid permissions or model not found error.', 'eightshift-forms'),
					'output' => \__('Invalid permissions, or the requested model was not found. Check that your token has the required permissions and that the model names and/or ids are correct.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_AIRTABLE_INVALID_PERMISSIONS_ERROR => [
					'type' => SettingsAirtable::SETTINGS_TYPE_KEY,
					'label' => \__('When Airtable integrations returns a invalid permissions error.', 'eightshift-forms'),
					'output' => \__('You are not permitted to perform this operation.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_AIRTABLE_INVALID_REQUEST_UNKNOWN_ERROR => [
					'type' => SettingsAirtable::SETTINGS_TYPE_KEY,
					'label' => \__('When Airtable integrations returns a invalid request unknown error.', 'eightshift-forms'),
					'output' => \__('Invalid request: parameter validation failed. Check your request data.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_AIRTABLE_INVALID_VALUE_FOR_COLUMN_ERROR => [
					'type' => SettingsAirtable::SETTINGS_TYPE_KEY,
					'label' => \__('When Airtable integrations returns a invalid value for column error.', 'eightshift-forms'),
					'output' => \__('One or more fields are invalid. Please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_AIRTABLE_SUCCESS => [
					'type' => SettingsAirtable::SETTINGS_TYPE_KEY,
					'label' => \__('When Airtable integration submits the form successfully.', 'eightshift-forms'),
					'output' => \__('The form was submitted successfully. Thank you!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Cache.
				self::LABEL_CACHE_TYPE_NOT_FOUND => [
					'type' => SettingsCache::SETTINGS_TYPE_KEY,
					'label' => \__('When the requested cache type does not exist.', 'eightshift-forms'),
					'output' => \__('cache doesn\'t exist.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_CACHE_DELETED_SUCCESS => [
					'type' => SettingsCache::SETTINGS_TYPE_KEY,
					'label' => \__('When the cache is deleted successfully.', 'eightshift-forms'),
					'output' => \__('cache deleted successfully!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Calculator.
				self::LABEL_CALCULATOR_MISSING_CONFIG => [
					'type' => SettingsCalculator::SETTINGS_TYPE_KEY,
					'label' => \__('When Calculator integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_CALCULATOR_SUCCESS => [
					'type' => SettingsCalculator::SETTINGS_TYPE_KEY,
					'label' => \__('When Calculator integrations is able to calculate the form.', 'eightshift-forms'),
					'output' => \__('Application submitted successfully. Thank you!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_CALCULATOR_BAD_REQUEST_ERROR => [
					'type' => SettingsCalculator::SETTINGS_TYPE_KEY,
					'label' => \__('When Calculator integration returns a bad request error.', 'eightshift-forms'),
					'output' => \__('Something is not right with the subscription. Please check all the fields and try again.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_CALCULATOR_ERROR_SETTINGS_MISSING => [
					'type' => SettingsCalculator::SETTINGS_TYPE_KEY,
					'label' => \__('When Calculator integration settings are missing.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Captcha.
				self::LABEL_CAPTCHA_FEATURE_DISABLED => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('Captcha feature is disabled.', 'eightshift-forms'),
					'output' => \__('Spam prevention system encountered an error. Please try again.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => false,
				],
				self::LABEL_CAPTCHA_REQUEST_MISSING_TOKEN => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('Captcha request is missing token.', 'eightshift-forms'),
					'output' => \__('Spam prevention system encountered an error. Please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_CAPTCHA_REQUEST_WP_ERROR => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('Captcha request has encountered an WP error.', 'eightshift-forms'),
					'output' => \__('Spam prevention service is currently unavailable. Please try again in a moment.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_CAPTCHA_FREE_OUTPUT_ERROR => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('Captcha type free returned an error response.', 'eightshift-forms'),
					'output' => \__('Spam prevention system encountered an error. Please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_CAPTCHA_ENTERPRISE_OUTPUT_ERROR => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('Captcha type enterprise returned an error response.', 'eightshift-forms'),
					'output' => \__('Spam prevention system encountered an error. Please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_CAPTCHA_WRONG_ACTION => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('Captcha action provided and action returned from the response don\'t match.', 'eightshift-forms'),
					'output' => \__('Spam prevention system encountered an error. Captcha response "action" is not valid.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_CAPTCHA_SCORE_SPAM => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('Captcha score has been detected as spam.', 'eightshift-forms'),
					'output' => \__('The request was marked as a potential spam request. Please try again.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_CAPTCHA_SUCCESS => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('Captcha request has been successful.', 'eightshift-forms'),
					'output' => \__('Success', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_CAPTCHA_DEBUG_SKIP_CHECK => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('Captcha debug skip check is active.', 'eightshift-forms'),
					'output' => \__('Form captcha skipped due to troubleshooting config set in settings.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_CAPTCHA_MISSING_CONFIG => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('When the captcha is not configured correctly.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_CAPTCHA_SKIP_CHECK => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('When the captcha check is skipped due to troubleshooting settings.', 'eightshift-forms'),
					'output' => \__('Form captcha skipped due to troubleshooting config set in settings.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_CAPTCHA_BAD_REQUEST => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('When the captcha request is invalid or malformed.', 'eightshift-forms'),
					'output' => \__('Spam prevention system encountered an error. Captcha "request" is invalid or malformed.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_CAPTCHA_ERROR => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('When the captcha check encounters an error.', 'eightshift-forms'),
					'output' => \__('Spam prevention system encountered an error. Please try again.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_CAPTCHA_FRIENDLY_OUTPUT_ERROR => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('Friendly Captcha returned an error response.', 'eightshift-forms'),
					'output' => \__('Spam prevention system encountered an error. Please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_CAPTCHA_FRIENDLY_HTTP_ERROR => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('Friendly Captcha siteverify request returned a non-success HTTP status.', 'eightshift-forms'),
					'output' => \__('Spam prevention service is currently unavailable. Please try again in a moment.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_CAPTCHA_FRIENDLY_AUTH_ERROR => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('Friendly Captcha API key is missing or invalid.', 'eightshift-forms'),
					'output' => \__('Spam prevention system is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_CAPTCHA_FRIENDLY_BAD_REQUEST => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('Friendly Captcha rejected the request as malformed.', 'eightshift-forms'),
					'output' => \__('Spam prevention system encountered an error. Friendly Captcha request is invalid or malformed.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_CAPTCHA_FRIENDLY_INVALID_SOLUTION => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('Friendly Captcha solution failed validation.', 'eightshift-forms'),
					'output' => \__('The request was marked as a potential spam request. Please try again.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_CAPTCHA_FRIENDLY_TIMEOUT_OR_DUPLICATE => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('Friendly Captcha solution expired or was already used.', 'eightshift-forms'),
					'output' => \__('Spam prevention check timed out or was reused. Please try again.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_CAPTCHA_FRIENDLY_ERROR => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('When Friendly Captcha encounters an error.', 'eightshift-forms'),
					'output' => \__('Spam prevention system encountered an error. Please try again.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_CAPTCHA_FRIENDLY_SUCCESS => [
					'type' => SettingsCaptcha::SETTINGS_TYPE_KEY,
					'label' => \__('When the Friendly Captcha check is successful.', 'eightshift-forms'),
					'output' => \__('Success', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Clearbit.
				self::LABEL_CLEARBIT_CRON_ERROR => [
					'type' => SettingsClearbit::SETTINGS_TYPE_KEY,
					'label' => \__('When Clearbit cron job is running, it can return an error for unknown entry.', 'eightshift-forms'),
					'output' => \__('There was an error with the service. Please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_CLEARBIT_AUTH_REQUIRED_ERROR => [
					'type' => SettingsClearbit::SETTINGS_TYPE_KEY,
					'label' => \__('When Clearbit integrations returns a auth required error.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_CLEARBIT_INVALID_EMAIL_ERROR => [
					'type' => SettingsClearbit::SETTINGS_TYPE_KEY,
					'label' => \__('When Clearbit integrations returns a invalid email error.', 'eightshift-forms'),
					'output' => \__('Enter a valid email address.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => false,
				],

				// Corvus.
				self::LABEL_CORVUS_MISSING_CONFIG => [
					'type' => SettingsCorvus::SETTINGS_TYPE_KEY,
					'label' => \__('When Corvus integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_CORVUS_MISSING_REQ_PARAMS => [
					'type' => SettingsCorvus::SETTINGS_TYPE_KEY,
					'label' => \__('When Corvus integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_CORVUS_MISSING_STORE_ID => [
					'type' => SettingsCorvus::SETTINGS_TYPE_KEY,
					'label' => \__('When Corvus integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_CORVUS_SUCCESS => [
					'type' => SettingsCorvus::SETTINGS_TYPE_KEY,
					'label' => \__('When Corvus integrations is able to send a request.', 'eightshift-forms'),
					'output' => \__('Application submitted successfully. Thank you!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Cron.
				self::LABEL_CRON_RUN_SUCCESS => [
					'type' => SettingsCronJobs::SETTINGS_TYPE_KEY,
					'label' => \__('When a cron job runs successfully.', 'eightshift-forms'),
					'output' => \__('Cron job run successfully!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_CRON_RUN_NOT_FOUND => [
					'type' => SettingsCronJobs::SETTINGS_TYPE_KEY,
					'label' => \__('When the requested cron job is not found.', 'eightshift-forms'),
					'output' => \__('Cron job not found.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Custom.
				self::LABEL_CUSTOM_NO_ACTION => [
					'type' => SettingsMailer::SETTINGS_TYPE_CUSTOM_KEY,
					'label' => \__('When custom action is not set.', 'eightshift-forms'),
					'output' => \__('There was an issue with form action. Check the form settings.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_CUSTOM_SUCCESS_REDIRECT => [
					'type' => SettingsMailer::SETTINGS_TYPE_CUSTOM_KEY,
					'label' => \__('When custom action is successful and redirect is set.', 'eightshift-forms'),
					'output' => \__('Form was successfully submitted. Redirecting you now.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_CUSTOM_ERROR => [
					'type' => SettingsMailer::SETTINGS_TYPE_CUSTOM_KEY,
					'label' => \__('When custom action returns an error.', 'eightshift-forms'),
					'output' => \__('There was an error with your form submission.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_CUSTOM_WP_ERROR => [
					'type' => SettingsMailer::SETTINGS_TYPE_CUSTOM_KEY,
					'label' => \__('When custom action returns a WP error.', 'eightshift-forms'),
					'output' => \__('There was an error with your form submission. Please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_CUSTOM_SUCCESS => [
					'type' => SettingsMailer::SETTINGS_TYPE_CUSTOM_KEY,
					'label' => \__('When custom action is successful.', 'eightshift-forms'),
					'output' => \__('Form was successfully submitted.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_CUSTOM_MISSING_CONFIG => [
					'type' => SettingsMailer::SETTINGS_TYPE_CUSTOM_KEY,
					'label' => \__('When the custom action is not configured correctly.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Debug.
				self::LABEL_ENCRYPT_FAILED => [
					'type' => SettingsDebug::SETTINGS_TYPE_KEY,
					'label' => \__('When data encryption fails.', 'eightshift-forms'),
					'output' => \__('Encrypt failed!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_DECRYPT_FAILED => [
					'type' => SettingsDebug::SETTINGS_TYPE_KEY,
					'label' => \__('When data decryption fails.', 'eightshift-forms'),
					'output' => \__('Decrypt failed!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_ENCRYPT_SUCCESS => [
					'type' => SettingsDebug::SETTINGS_TYPE_KEY,
					'label' => \__('When data encryption finishes successfully.', 'eightshift-forms'),
					'output' => \__('Encrypt finished successfully!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_DECRYPT_SUCCESS => [
					'type' => SettingsDebug::SETTINGS_TYPE_KEY,
					'label' => \__('When data decryption finishes successfully.', 'eightshift-forms'),
					'output' => \__('Decrypt finished successfully!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Generic.
				self::LABEL_SUBMIT_INTEGRATION_SUCCESS => [
					'type' => self::TYPE_GENERIC,
					'label' => \__('Someone tried to submit a form to an integration that returned a success.', 'eightshift-forms'),
					'output' => \__('The form was submitted successfully. Thank you!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => false,
				],
				self::LABEL_SUBMIT_INTEGRATION_ERROR_WP => [
					'type' => self::TYPE_GENERIC,
					'label' => \__('Someone tried to submit a form to an integration that returned an error that is not handled by the integration.', 'eightshift-forms'),
					'output' => \__('Something went wrong while submitting your form. Please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_SUBMIT_FALLBACK_ERROR => [
					'type' => self::TYPE_GENERIC,
					'label' => \__('When the form submission fails and the fallback error is used.', 'eightshift-forms'),
					'output' => \__('Something went wrong while submitting your form. Please try again.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_TEST_API_SUCCESS => [
					'type' => self::TYPE_GENERIC,
					'label' => \__('When the API connection test is successful.', 'eightshift-forms'),
					'output' => \__('The API test was successful.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_TEST_API_ERROR => [
					'type' => self::TYPE_GENERIC,
					'label' => \__('When the API connection test fails.', 'eightshift-forms'),
					'output' => \__('There seems to be an error with the API test. Please ensure that your credentials are correct.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_GLOBAL_NOT_CONFIGURED => [
					'type' => self::TYPE_GENERIC,
					'label' => \__('When the global settings for a feature are not configured correctly.', 'eightshift-forms'),
					'output' => \__('Global settings are not configured correctly. Please ensure that your feature is enabled in the settings.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_INTEGRATION_ITEMS_MISSING => [
					'type' => self::TYPE_GENERIC,
					'label' => \__('When the integration items are missing.', 'eightshift-forms'),
					'output' => \__('Integration items are missing.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_INTEGRATION_ITEMS_SUCCESS => [
					'type' => self::TYPE_GENERIC,
					'label' => \__('When the integration items are fetched successfully.', 'eightshift-forms'),
					'output' => \__('Integration items were successfully fetched.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_FORM_FIELDS_MISSING => [
					'type' => self::TYPE_GENERIC,
					'label' => \__('When the form has no fields to provide.', 'eightshift-forms'),
					'output' => \__('Form has no fields to provide, please check your form is configured correctly.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_FORM_FIELDS_SUCCESS => [
					'type' => self::TYPE_GENERIC,
					'label' => \__('When the form fields are fetched successfully.', 'eightshift-forms'),
					'output' => \__('Form fields were successfully fetched.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_INCREMENT_RESET_SUCCESS => [
					'type' => self::TYPE_GENERIC,
					'label' => \__('When the increment counter is reset successfully.', 'eightshift-forms'),
					'output' => \__('Increment reset successful.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_LOCATIONS_RESULT_OUTPUT_ERROR => [
					'type' => self::TYPE_GENERIC,
					'label' => \__('When the result output is not used in any location.', 'eightshift-forms'),
					'output' => \__('Your result output is not used in any location!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_LOCATIONS_FORM_ERROR => [
					'type' => self::TYPE_GENERIC,
					'label' => \__('When the form is not used in any location.', 'eightshift-forms'),
					'output' => \__('Your form is not used in any location!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_LOCATIONS_SUCCESS => [
					'type' => self::TYPE_GENERIC,
					'label' => \__('When the locations are fetched successfully.', 'eightshift-forms'),
					'output' => \__('Locations were successfully fetched.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_BULK_MISSING_ITEMS => [
					'type' => self::TYPE_GENERIC,
					'label' => \__('When no items are selected for a bulk action.', 'eightshift-forms'),
					'output' => \__('Please select the items you want to bulk action.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_GENERIC_SUCCESS => [
					'type' => self::TYPE_GENERIC,
					'label' => \__('Generic success message shown to the user.', 'eightshift-forms'),
					'output' => \__('Success', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_GENERIC_WARNING => [
					'type' => self::TYPE_GENERIC,
					'label' => \__('Generic warning message shown to the user.', 'eightshift-forms'),
					'output' => \__('Warning', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_GENERIC_ERROR => [
					'type' => self::TYPE_GENERIC,
					'label' => \__('Generic error message shown to the user.', 'eightshift-forms'),
					'output' => \__('Error', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_SETTINGS_SUCCESS => [
					'type' => self::TYPE_GENERIC,
					'label' => \__('When the settings are saved successfully.', 'eightshift-forms'),
					'output' => \__('Changes saved!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Geolocation.
				self::LABEL_GEOLOCATION_FEATURE_DISABLED => [
					'type' => SettingsGeolocation::SETTINGS_TYPE_KEY,
					'label' => \__('Geolocation feature is disabled.', 'eightshift-forms'),
					'output' => \__('There was an error with your form submission. Please try again.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => false,
				],
				self::LABEL_GEOLOCATION_MALFORMED_DECRYPT_DATA => [
					'type' => SettingsGeolocation::SETTINGS_TYPE_KEY,
					'label' => \__('Geolocation malformed decrypt data.', 'eightshift-forms'),
					'output' => \__('There was an error with your form submission. Please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_GEOLOCATION_DETECTION_FAILED => [
					'type' => SettingsGeolocation::SETTINGS_TYPE_KEY,
					'label' => \__('Geolocation detection failed.', 'eightshift-forms'),
					'output' => \__('There was an error with your form submission. Please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_GEOLOCATION_SUCCESS => [
					'type' => SettingsGeolocation::SETTINGS_TYPE_KEY,
					'label' => \__('Geolocation request has been successful.', 'eightshift-forms'),
					'output' => \__('Success geolocation', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_GEOLOCATION_SKIP_CHECK => [
					'type' => SettingsGeolocation::SETTINGS_TYPE_KEY,
					'label' => \__('When the geolocation check is skipped because the feature is inactive.', 'eightshift-forms'),
					'output' => \__('Form geolocation skipped. Feature inactive.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_GEOLOCATION_MALFORMED_OR_NOT_VALID => [
					'type' => SettingsGeolocation::SETTINGS_TYPE_KEY,
					'label' => \__('When the geolocation data is malformed or not valid.', 'eightshift-forms'),
					'output' => \__('The geolocation data is malformed or not valid.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_GEOLOCATION_COUNTRIES_MISSING => [
					'type' => SettingsGeolocation::SETTINGS_TYPE_KEY,
					'label' => \__('When the geolocation countries are missing.', 'eightshift-forms'),
					'output' => \__('Geolocation countries are missing.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_GEOLOCATION_COUNTRIES_SUCCESS => [
					'type' => SettingsGeolocation::SETTINGS_TYPE_KEY,
					'label' => \__('When the geolocation countries are fetched successfully.', 'eightshift-forms'),
					'output' => \__('Geolocation countries were successfully fetched.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Goodbits.
				self::LABEL_GOODBITS_MISSING_CONFIG => [
					'type' => SettingsGoodbits::SETTINGS_TYPE_KEY,
					'label' => \__('When Goodbits integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_GOODBITS_BAD_REQUEST_ERROR => [
					'type' => SettingsGoodbits::SETTINGS_TYPE_KEY,
					'label' => \__('When Goodbits integrations returns a bad request error.', 'eightshift-forms'),
					'output' => \__('Something is not right with the subscription. Please check all the fields and try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_GOODBITS_SUCCESS => [
					'type' => SettingsGoodbits::SETTINGS_TYPE_KEY,
					'label' => \__('When Goodbits integration submits the form successfully.', 'eightshift-forms'),
					'output' => \__('The newsletter subscription was successful. Thank you!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Greenhouse.
				self::LABEL_GREENHOUSE_MISSING_CONFIG => [
					'type' => SettingsGreenhouse::SETTINGS_TYPE_KEY,
					'label' => \__('When Greenhouse integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_GREENHOUSE_BAD_REQUEST_ERROR => [
					'type' => SettingsGreenhouse::SETTINGS_TYPE_KEY,
					'label' => \__('When Greenhouse integrations returns a bad request error.', 'eightshift-forms'),
					'output' => \__('Something is not right with the job application. Please check all the fields and try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_GREENHOUSE_SUCCESS => [
					'type' => SettingsGreenhouse::SETTINGS_TYPE_KEY,
					'label' => \__('When Greenhouse integration submits the form successfully.', 'eightshift-forms'),
					'output' => \__('Application submitted successfully. Thank you!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Hubspot.
				self::LABEL_HUBSPOT_MISSING_CONFIG => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_HUBSPOT_BAD_REQUEST_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a bad request error.', 'eightshift-forms'),
					'output' => \__('Something is not with the application. Please check all the fields and try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_INVALID_REQUEST_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a invalid request error.', 'eightshift-forms'),
					'output' => \__('Something is not right with the application. Please check all the fields and try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_MAX_NUMBER_OF_SUBMITTED_VALUES_EXCEEDED_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a max number of submitted values exceeded error.', 'eightshift-forms'),
					'output' => \__('More than 1000 fields were included in the response. Please contact website administrator.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_INVALID_EMAIL_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a invalid email error.', 'eightshift-forms'),
					'output' => \__('Enter a valid email address.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_BLOCKED_EMAIL_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a blocked email error.', 'eightshift-forms'),
					'output' => \__('We are sorry but you email was blocked in our blacklist.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_INVALID_NUMBER_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a invalid number error.', 'eightshift-forms'),
					'output' => \__('Some of number fields are not a valid number value.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_INPUT_TOO_LARGE_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a input too large error.', 'eightshift-forms'),
					'output' => \__('The value in the field is too large for the type of field.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_FIELD_NOT_IN_FORM_DEFINITION_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a field not in form definition error.', 'eightshift-forms'),
					'output' => \__('The field was included in the form submission but is not in the form definition.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_NUMBER_OUT_OF_RANGE_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a number out of range error.', 'eightshift-forms'),
					'output' => \__('The value of a number field outside the range specified in the field settings.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_VALUE_NOT_IN_FIELD_DEFINITION_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a value not in field definition error.', 'eightshift-forms'),
					'output' => \__('The value provided for an enumeration field (e.g. checkbox, dropdown, radio) is not one of the possible options.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_INVALID_METADATA_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a invalid metadata error.', 'eightshift-forms'),
					'output' => \__('The context object contains an unexpected attribute. Please contact website administrator.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_INVALID_GOTOWEBINAR_WEBINAR_KEY_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a invalid gotowebinar webinar key error.', 'eightshift-forms'),
					'output' => \__('The value in goToWebinarWebinarKey in the context object is invalid. Please contact website administrator.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_INVALID_HUTK_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a invalid hutk error.', 'eightshift-forms'),
					'output' => \__('The hutk field in the context object is invalid. Please contact website administrator.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_INVALID_IP_ADDRESS_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a invalid ip address error.', 'eightshift-forms'),
					'output' => \__('The ipAddress field in the context object is invalid. Please contact website administrator.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_INVALID_PAGE_URI_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a invalid page uri error.', 'eightshift-forms'),
					'output' => \__('The pageUri field in the context object is invalid. Please contact website administrator.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_INVALID_LEGAL_OPTION_FORMAT_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a invalid legal option format error.', 'eightshift-forms'),
					'output' => \__('LegalConsentOptions was empty or it contains both the consent and legitimateInterest fields. Please contact website administrator.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_MISSING_PROCESSING_CONSENT_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a missing processing consent error.', 'eightshift-forms'),
					'output' => \__('The consentToProcess field in consent or value field in legitimateInterest was false. Please contact website administrator.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_MISSING_PROCESSING_CONSENT_TEXT_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a missing processing consent text error.', 'eightshift-forms'),
					'output' => \__('The text field for processing consent was missing. Please contact website administrator.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_MISSING_COMMUNICATION_CONSENT_TEXT_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a missing communication consent text error.', 'eightshift-forms'),
					'output' => \__('The communication consent text was missing for a subscription. Please contact website administrator.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_MISSING_LEGITIMATE_INTEREST_TEXT_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a missing legitimate interest text error.', 'eightshift-forms'),
					'output' => \__('The legitimate interest consent text was missing. Please contact website administrator.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_DUPLICATE_SUBSCRIPTION_TYPE_ID_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a duplicate subscription type id error.', 'eightshift-forms'),
					'output' => \__('The communications list contains two or more items with the same subscriptionTypeId. Please contact website administrator.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_FORM_HAS_RECAPTCHA_ENABLED_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a form has recaptcha enabled error.', 'eightshift-forms'),
					'output' => \__('Your Hubspot form has reCaptcha enabled and we are not able to process the request. Please disable reCaptcha and try again. Please contact website administrator.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_ERROR_429_ERROR => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integrations returns a error 429 error.', 'eightshift-forms'),
					'output' => \__('The HubSpot account has reached the rate limit. Please contact website administrator.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_HUBSPOT_SUCCESS => [
					'type' => SettingsHubspot::SETTINGS_TYPE_KEY,
					'label' => \__('When Hubspot integration submits the form successfully.', 'eightshift-forms'),
					'output' => \__('The form was submitted successfully. Thank you!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Jira.
				self::LABEL_JIRA_MISSING_CONFIG => [
					'type' => SettingsJira::SETTINGS_TYPE_KEY,
					'label' => \__('When Jira integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_JIRA_MISSING_PROJECT => [
					'type' => SettingsJira::SETTINGS_TYPE_KEY,
					'label' => \__('When Jira integrations returns a missing project error.', 'eightshift-forms'),
					'output' => \__('Your form is missing project key. Please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_JIRA_MISSING_ISSUE_TYPE => [
					'type' => SettingsJira::SETTINGS_TYPE_KEY,
					'label' => \__('When Jira integrations returns a missing issue type error.', 'eightshift-forms'),
					'output' => \__('Your form is missing issue type. Please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_JIRA_MISSING_SUMMARY => [
					'type' => SettingsJira::SETTINGS_TYPE_KEY,
					'label' => \__('When Jira integrations returns a missing summary error.', 'eightshift-forms'),
					'output' => \__('Your form is missing issue summary. Please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_JIRA_MISSING_EPIC_NAME => [
					'type' => SettingsJira::SETTINGS_TYPE_KEY,
					'label' => \__('When Jira integrations returns a missing epic name error.', 'eightshift-forms'),
					'output' => \__('Your form is missing an epic name. Please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_JIRA_AUTH_REQUIRED_ERROR => [
					'type' => SettingsJira::SETTINGS_TYPE_KEY,
					'label' => \__('When Jira integrations returns a auth required error.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_JIRA_INVALID_EMAIL_ERROR => [
					'type' => SettingsJira::SETTINGS_TYPE_KEY,
					'label' => \__('When Jira integrations returns a invalid email error.', 'eightshift-forms'),
					'output' => \__('Enter a valid email address.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => false,
				],
				self::LABEL_JIRA_BAD_REQUEST_ERROR => [
					'type' => SettingsJira::SETTINGS_TYPE_KEY,
					'label' => \__('When Jira integration returns a bad request error.', 'eightshift-forms'),
					'output' => \__('Something is not right with the job application. Please check all the fields and try again.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_JIRA_SUCCESS => [
					'type' => SettingsJira::SETTINGS_TYPE_KEY,
					'label' => \__('When Jira integration submits the form successfully.', 'eightshift-forms'),
					'output' => \__('Application submitted successfully. Thank you!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Mailchimp.
				self::LABEL_MAILCHIMP_MISSING_CONFIG => [
					'type' => SettingsMailchimp::SETTINGS_TYPE_KEY,
					'label' => \__('When Mailchimp integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_MAILCHIMP_BAD_REQUEST_ERROR => [
					'type' => SettingsMailchimp::SETTINGS_TYPE_KEY,
					'label' => \__('When Mailchimp integrations returns a bad request error.', 'eightshift-forms'),
					'output' => \__('Something is not right with the subscription. Please check all the fields and try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_MAILCHIMP_SUCCESS => [
					'type' => SettingsMailchimp::SETTINGS_TYPE_KEY,
					'label' => \__('When Mailchimp integration submits the form successfully.', 'eightshift-forms'),
					'output' => \__('The newsletter subscription was successful. Thank you!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Mailer.
				self::LABEL_MAILER_MISSING_CONFIG => [
					'type' => SettingsMailer::SETTINGS_TYPE_KEY,
					'label' => \__('When Mailer integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_MAILER_ERROR_EMAIL_SEND => [
					'type' => SettingsMailer::SETTINGS_TYPE_KEY,
					'label' => \__('When Mailer integrations is not able to send an email.', 'eightshift-forms'),
					'output' => \__('E-mail was not sent due to an unknown issue. Please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_MAILER_SUCCESS => [
					'type' => SettingsMailer::SETTINGS_TYPE_KEY,
					'label' => \__('When Mailer integrations is able to send an email.', 'eightshift-forms'),
					'output' => \__('E-mail was sent successfully.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_MAILER_ERROR_EMAIL_CONFIRMATION_SEND => [
					'type' => SettingsMailer::SETTINGS_TYPE_KEY,
					'label' => \__('When the confirmation email fails to send.', 'eightshift-forms'),
					'output' => \__('Confirmation e-mail was not sent due to unknown issue. Please try again.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Mailerlite.
				self::LABEL_MAILERLITE_MISSING_CONFIG => [
					'type' => SettingsMailerlite::SETTINGS_TYPE_KEY,
					'label' => \__('When Mailerlite integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_MAILERLITE_BAD_REQUEST_ERROR => [
					'type' => SettingsMailerlite::SETTINGS_TYPE_KEY,
					'label' => \__('When Mailerlite integrations returns a bad request error.', 'eightshift-forms'),
					'output' => \__('Something is not right with the subscription. Please check all the fields and try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_MAILERLITE_SUCCESS => [
					'type' => SettingsMailerlite::SETTINGS_TYPE_KEY,
					'label' => \__('When Mailerlite integration submits the form successfully.', 'eightshift-forms'),
					'output' => \__('The newsletter subscription was successful. Thank you!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Migration.
				self::LABEL_MIGRATION_TYPE_NOT_FOUND => [
					'type' => SettingsMigration::SETTINGS_TYPE_KEY,
					'label' => \__('When the migration version type key is missing or not valid.', 'eightshift-forms'),
					'output' => \__('Migration version type key was not provided or not valid.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_MIGRATION_SUCCESS => [
					'type' => SettingsMigration::SETTINGS_TYPE_KEY,
					'label' => \__('When the migration finishes successfully.', 'eightshift-forms'),
					'output' => \__('Migration finished with success.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Moments.
				self::LABEL_MOMENTS_BAD_REQUEST_ERROR => [
					'type' => SettingsMoments::SETTINGS_TYPE_KEY,
					'label' => \__('When Moments integrations returns a bad request error.', 'eightshift-forms'),
					'output' => \__('Something is not right with the submission. Please check all the fields and try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_MOMENTS_MISSING_CONFIG => [
					'type' => SettingsMoments::SETTINGS_TYPE_KEY,
					'label' => \__('When Moments integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_MOMENTS_EVENTS_ERROR => [
					'type' => SettingsMoments::SETTINGS_TYPE_KEY,
					'label' => \__('When Moments events are being sent, it can return an error.', 'eightshift-forms'),
					'output' => \__('There was an error with the service. Please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_MOMENTS_SUCCESS => [
					'type' => SettingsMoments::SETTINGS_TYPE_KEY,
					'label' => \__('When Moments integration submits the form successfully.', 'eightshift-forms'),
					'output' => \__('The form was submitted successfully. Thank you!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Nationbuilder.
				self::LABEL_NATIONBUILDER_MISSING_CONFIG => [
					'type' => SettingsNationbuilder::SETTINGS_TYPE_KEY,
					'label' => \__('When Nationbuilder integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_NATIONBUILDER_LIST_ERROR => [
					'type' => SettingsNationbuilder::SETTINGS_TYPE_KEY,
					'label' => \__('When Nationbuilder cron job is running, it can return an error.', 'eightshift-forms'),
					'output' => \__('There was an error with the service. Please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_NATIONBUILDER_TAGS_ERROR => [
					'type' => SettingsNationbuilder::SETTINGS_TYPE_KEY,
					'label' => \__('When Nationbuilder cron job is running, it can return an error.', 'eightshift-forms'),
					'output' => \__('There was an error with the service. Please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_NATIONBUILDER_BAD_REQUEST_ERROR => [
					'type' => SettingsNationbuilder::SETTINGS_TYPE_KEY,
					'label' => \__('When Nationbuilder integrations returns a bad request error.', 'eightshift-forms'),
					'output' => \__('Something is not right with the subscription. Please check all the fields and try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_NATIONBUILDER_ERROR_SETTINGS_MISSING => [
					'type' => SettingsNationbuilder::SETTINGS_TYPE_KEY,
					'label' => \__('When Nationbuilder integrations returns a error settings missing error.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_NATIONBUILDER_SERVER_ERROR => [
					'type' => SettingsNationbuilder::SETTINGS_TYPE_KEY,
					'label' => \__('When Nationbuilder integrations returns a server error.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_NATIONBUILDER_SUCCESS => [
					'type' => SettingsNationbuilder::SETTINGS_TYPE_KEY,
					'label' => \__('When Nationbuilder integration submits the form successfully.', 'eightshift-forms'),
					'output' => \__('Application submitted successfully. Thank you!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Pardot.
				self::LABEL_PARDOT_MISSING_CONFIG => [
					'type' => SettingsPardot::SETTINGS_TYPE_KEY,
					'label' => \__('When Pardot integration is not configured correctly, either globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_PARDOT_BAD_REQUEST_ERROR => [
					'type' => SettingsPardot::SETTINGS_TYPE_KEY,
					'label' => \__('When Pardot integration returns a bad request error.', 'eightshift-forms'),
					'output' => \__('Something is not right with the submission. Please check all the fields and try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_PARDOT_ERROR_SETTINGS_MISSING => [
					'type' => SettingsPardot::SETTINGS_TYPE_KEY,
					'label' => \__('When Pardot integration returns a settings missing error.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_PARDOT_SERVER_ERROR => [
					'type' => SettingsPardot::SETTINGS_TYPE_KEY,
					'label' => \__('When Pardot integration returns a server error.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_PARDOT_SUCCESS => [
					'type' => SettingsPardot::SETTINGS_TYPE_KEY,
					'label' => \__('When Pardot integration submits the form successfully.', 'eightshift-forms'),
					'output' => \__('Application submitted successfully. Thank you!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Paycek.
				self::LABEL_PAYCEK_MISSING_CONFIG => [
					'type' => SettingsPaycek::SETTINGS_TYPE_KEY,
					'label' => \__('When Paycek integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_PAYCEK_MISSING_REQ_PARAMS => [
					'type' => SettingsPaycek::SETTINGS_TYPE_KEY,
					'label' => \__('When Paycek integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_PAYCEK_SUCCESS => [
					'type' => SettingsPaycek::SETTINGS_TYPE_KEY,
					'label' => \__('When Paycek integrations is able to send a request.', 'eightshift-forms'),
					'output' => \__('Payment submitted successfully. Thank you!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Pipedrive.
				self::LABEL_PIPEDRIVE_MISSING_CONFIG => [
					'type' => SettingsPipedrive::SETTINGS_TYPE_KEY,
					'label' => \__('When Pipedrive integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_PIPEDRIVE_MISSING_NAME => [
					'type' => SettingsPipedrive::SETTINGS_TYPE_KEY,
					'label' => \__('When Pipedrive integrations returns a missing name error.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_PIPEDRIVE_MISSING_ORGANIZATION => [
					'type' => SettingsPipedrive::SETTINGS_TYPE_KEY,
					'label' => \__('When Pipedrive integrations returns a missing organization error.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_PIPEDRIVE_WRONG_ORGANIZATION_ID => [
					'type' => SettingsPipedrive::SETTINGS_TYPE_KEY,
					'label' => \__('When Pipedrive integrations returns a wrong organization id error.', 'eightshift-forms'),
					'output' => \__('Organization ID is invalid.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_PIPEDRIVE_WRONG_DATASET => [
					'type' => SettingsPipedrive::SETTINGS_TYPE_KEY,
					'label' => \__('When Pipedrive integrations returns a wrong dataset error.', 'eightshift-forms'),
					'output' => \__('Integration dataset is invalid.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_PIPEDRIVE_BAD_REQUEST_ERROR => [
					'type' => SettingsPipedrive::SETTINGS_TYPE_KEY,
					'label' => \__('When Pipedrive integration returns a bad request error.', 'eightshift-forms'),
					'output' => \__('Something is not right with the job application. Please check all the fields and try again.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_PIPEDRIVE_SUCCESS => [
					'type' => SettingsPipedrive::SETTINGS_TYPE_KEY,
					'label' => \__('When Pipedrive integration submits the form successfully.', 'eightshift-forms'),
					'output' => \__('Application submitted successfully. Thank you!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Talentlyft.
				self::LABEL_TALENTLYFT_MISSING_CONFIG => [
					'type' => SettingsTalentlyft::SETTINGS_TYPE_KEY,
					'label' => \__('When Talentlyft integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_TALENTLYFT_BAD_REQUEST_ERROR => [
					'type' => SettingsTalentlyft::SETTINGS_TYPE_KEY,
					'label' => \__('When Talentlyft integrations returns a bad request error.', 'eightshift-forms'),
					'output' => \__('Something is not right with the job application. Please check all the fields and try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_TALENTLYFT_VALIDATION_ERROR => [
					'type' => SettingsTalentlyft::SETTINGS_TYPE_KEY,
					'label' => \__('When Talentlyft integrations returns a validation error.', 'eightshift-forms'),
					'output' => \__('It looks like there are some issues with your form fields. Please check all the fields and try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_TALENTLYFT_SUCCESS => [
					'type' => SettingsTalentlyft::SETTINGS_TYPE_KEY,
					'label' => \__('When Talentlyft integration submits the form successfully.', 'eightshift-forms'),
					'output' => \__('Application submitted successfully. Thank you!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Transfer.
				self::LABEL_TRANSFER_EXPORT_MISSING_FORMS => [
					'type' => SettingsTransfer::SETTINGS_TYPE_KEY,
					'label' => \__('When no forms are selected for export.', 'eightshift-forms'),
					'output' => \__('Please click on the forms you want to export.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_TRANSFER_EXPORT_MISSING_RESULT_OUTPUTS => [
					'type' => SettingsTransfer::SETTINGS_TYPE_KEY,
					'label' => \__('When no result outputs are selected for export.', 'eightshift-forms'),
					'output' => \__('Please click on the result outputs you want to export.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_TRANSFER_UPLOAD_MISSING_FILE => [
					'type' => SettingsTransfer::SETTINGS_TYPE_KEY,
					'label' => \__('When no file is provided for the upload.', 'eightshift-forms'),
					'output' => \__('Please use the upload field to provide the .json file for the upload.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_TRANSFER_UPLOAD_ERROR => [
					'type' => SettingsTransfer::SETTINGS_TYPE_KEY,
					'label' => \__('When the uploaded transfer file is invalid.', 'eightshift-forms'),
					'output' => \__('There was an issue with your upload file. Please make sure you use forms export file and try again.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_TRANSFER_UPLOAD_MISSING_TYPE => [
					'type' => SettingsTransfer::SETTINGS_TYPE_KEY,
					'label' => \__('When the transfer version type key is not provided.', 'eightshift-forms'),
					'output' => \__('Transfer version type key was not provided.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_TRANSFER_SUCCESS => [
					'type' => SettingsTransfer::SETTINGS_TYPE_KEY,
					'label' => \__('When the transfer finishes successfully.', 'eightshift-forms'),
					'output' => \__('successfully done!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_EXPORT_MISSING_ITEMS => [
					'type' => SettingsTransfer::SETTINGS_TYPE_KEY,
					'label' => \__('When no items are selected for export.', 'eightshift-forms'),
					'output' => \__('Please select the items you want to export.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_EXPORT_DATA_EMPTY => [
					'type' => SettingsTransfer::SETTINGS_TYPE_KEY,
					'label' => \__('When the data for export is empty.', 'eightshift-forms'),
					'output' => \__('Data for export is empty.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_EXPORT_SUCCESS => [
					'type' => SettingsTransfer::SETTINGS_TYPE_KEY,
					'label' => \__('When the data export finishes successfully.', 'eightshift-forms'),
					'output' => \__('Data export finished with success.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Validation.
				self::LABEL_PERMISSION_DENIED => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('Someone tried to access the forms API without the proper permissions.', 'eightshift-forms'),
					'output' => \__('You are not permitted to perform this operation.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_VALIDATION_MISSING_MANDATORY_PARAMS => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('Someone tried to submit a form without the proper mandatory params.', 'eightshift-forms'),
					'output' => \__('This form is malformed or not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_SUBMIT_LOGGED_IN => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('Someone tried to submit a form while not logged in.', 'eightshift-forms'),
					'output' => \__('This form can be submitted only by logged in users.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_SUBMIT_ONCE => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('Someone tried to submit a form more than once.', 'eightshift-forms'),
					'output' => \__('This form can be submitted only once.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_SECURITY => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('Someone tried to submit a form with too many requests and was blocked.', 'eightshift-forms'),
					'output' => \__('You have made too many requests in a short time. Please slow down and try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_PARAMS => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('Someone tried to submit a form with missing required params.', 'eightshift-forms'),
					'output' => \__('Some required fields are missing. Please check the form and try again.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => false,
				],
				self::LABEL_VALIDATION_FILES => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('Someone tried to submit a form with missing required files.', 'eightshift-forms'),
					'output' => \__('Some required files are missing. Please check the form and try again.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => false,
				],
				self::LABEL_FILES_UPLOAD_SUCCESS => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('Someone tried to submit a form with files upload success.', 'eightshift-forms'),
					'output' => \__('File uploaded successfully.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => false,
				],
				self::LABEL_FILES_UPLOAD_ERROR => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('Someone tried to submit a form with files upload error.', 'eightshift-forms'),
					'output' => \__('There was an error while uploading your file. Please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_VALIDATION_STEPS_CURRENT_STEP_PROBLEM => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When validation steps current step is not set.', 'eightshift-forms'),
					'output' => \__('It looks like there is some problem with current step, please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_STEPS_FIELDS_PROBLEM => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When validation steps fields are not set.', 'eightshift-forms'),
					'output' => \__('It looks like there is some problem with step fields, please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_STEPS_NEXT_STEP_PROBLEM => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When validation steps next step is not set.', 'eightshift-forms'),
					'output' => \__('It looks like there is some problem with next step, please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_STEPS_PARAMETERS_PROBLEM => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When validation steps parameters are not set.', 'eightshift-forms'),
					'output' => \__('It looks like there is some problem with parameters sent, please try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_STEPS_SUCCESS => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When validation steps is successful.', 'eightshift-forms'),
					'output' => \__('Step validation is successful, you may continue.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_REQUIRED => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a required field is left empty.', 'eightshift-forms'),
					'output' => \__('This field is required.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				// translators: %s used for displaying required number.
				self::LABEL_VALIDATION_REQUIRED_COUNT => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a field requires a minimum number of selected items.', 'eightshift-forms'),
					'output' => \__('This field is required, with at least %s items selected.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_INVALID => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a field value is not valid.', 'eightshift-forms'),
					'output' => \__('This field is not valid.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_EMAIL => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When an email field value is not valid.', 'eightshift-forms'),
					'output' => \__('This e-mail is not valid.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_EMAIL_EXISTS => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When an email already exists in the system.', 'eightshift-forms'),
					'output' => \__('This e-mail already exists in our system.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_EMAIL_TLD => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When an email top level domain is not valid.', 'eightshift-forms'),
					'output' => \__('This e-mails top level domain is not valid.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_URL => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a URL field value is not valid.', 'eightshift-forms'),
					'output' => \__('This URL is not valid.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				// translators: %s used for displaying min number to the user.
				self::LABEL_VALIDATION_MIN => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a number field value is less than the allowed minimum.', 'eightshift-forms'),
					'output' => \__('This field value is less than expected. Minimal number should be %s.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				// translators: %s used for displaying max number to the user.
				self::LABEL_VALIDATION_MAX => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a number field value is more than the allowed maximum.', 'eightshift-forms'),
					'output' => \__('This field value is more than expected. Maximal number should be %s.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				// translators: %s used for displaying length min number to the user.
				self::LABEL_VALIDATION_MIN_LENGTH => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a field value has fewer characters than allowed.', 'eightshift-forms'),
					'output' => \__('This field value has less characters than expected. We expect minimum %s characters.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				// translators: %s used for displaying length max number to the user.
				self::LABEL_VALIDATION_MAX_LENGTH => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a field value has more characters than allowed.', 'eightshift-forms'),
					'output' => \__('This field value has more characters than expected. We expect maximum %s characters.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				// translators: %s used for displaying length min number to the user.
				self::LABEL_VALIDATION_MIN_COUNT => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a field has fewer selected items than allowed.', 'eightshift-forms'),
					'output' => \__('This field value has less items than expected. We expect minimum %s items.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				// translators: %s used for displaying length max number to the user.
				self::LABEL_VALIDATION_MAX_COUNT => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a field has more selected items than allowed.', 'eightshift-forms'),
					'output' => \__('This field value has more items than expected. We expect maximum %s items.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_NUMBER => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a numbers-only field contains invalid characters.', 'eightshift-forms'),
					'output' => \__('This field should only contain numbers.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				// translators: %s used for displaying validation pattern to the user.
				self::LABEL_VALIDATION_PATTERN => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a field value does not match the required format.', 'eightshift-forms'),
					'output' => \__('This field value should be in this format: %s.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				// translators: %s used for displaying file type value.
				self::LABEL_VALIDATION_ACCEPT => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When an uploaded file type is not supported.', 'eightshift-forms'),
					'output' => \__('The file type is not supported. Only %s files are allowed.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				// translators: %s used for displaying file type value.
				self::LABEL_VALIDATION_ACCEPT_MIME => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When an uploaded file has a corrupted or invalid format.', 'eightshift-forms'),
					'output' => \__('The file seems to be corrupted or invalid format. Only %s are allowed.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				// translators: %s used for displaying file type value.
				self::LABEL_VALIDATION_ACCEPT_MIME_MULTIPLE => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When one or more uploaded files have a corrupted or invalid format.', 'eightshift-forms'),
					'output' => \__('One or more files seem to be corrupt or have invalid format. Only %s are allowed.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_FILE_WRONG_UPLOAD_PATH => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When one or more files are uploaded using an unauthorized method.', 'eightshift-forms'),
					'output' => \__('One or more files seem to be uploaded using an unauthorized method.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_FILE_NOT_LOCATED => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When one or more files were not uploaded to the server.', 'eightshift-forms'),
					'output' => \__('It seems that one or more files were not uploaded to the server. Please remove the files and try again.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_FILE_UPLOAD => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When there is an error with the file upload.', 'eightshift-forms'),
					'output' => \__('There seems to be an error with the file upload. Please try again.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_FILE_MAX_AMOUNT => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When more than one file is uploaded in a single-file field.', 'eightshift-forms'),
					'output' => \__('You can only upload a single file in this field. If you have multiple files, please remove them and try again.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				// translators: %s used for displaying number value.
				self::LABEL_VALIDATION_MIN_SIZE => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When an uploaded file is smaller than the allowed minimum size.', 'eightshift-forms'),
					'output' => \__('The file is smaller than allowed. Minimum file size is %s MB.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				// translators: %s used for displaying number value.
				self::LABEL_VALIDATION_MAX_SIZE => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When an uploaded file is larger than the allowed maximum size.', 'eightshift-forms'),
					'output' => \__('The file is larger than allowed. Maximum file size is %s MB.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_PHONE => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a phone number field value is not valid.', 'eightshift-forms'),
					'output' => \__('This phone number is not valid. It must contain a valid country/network prefix with only numbers.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_DATE => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a date field value is not valid.', 'eightshift-forms'),
					'output' => \__('This date format is not valid.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_DATE_TIME => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a date/time field value is not valid.', 'eightshift-forms'),
					'output' => \__('This date/time format is not valid.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_DATE_NO_FUTURE => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a date field only allows dates in the past.', 'eightshift-forms'),
					'output' => \__('This fields only allows dates in the past.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_MAILCHIMP_INVALID_ZIP => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a Mailchimp ZIP code field value is not valid.', 'eightshift-forms'),
					'output' => \__('This field value has more characters than expected. We expect maximum 5 numbers.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_GREENHOUSE_ACCEPT_MIME => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a Greenhouse file upload has an unsupported format.', 'eightshift-forms'),
					'output' => \__('The file seems to be corrupted or invalid format. Only pdf,doc,docx,txt,rtf are allowed.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_MOMENTS_INVALID_PHONE_LENGTH => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a Moments phone number field has an invalid length.', 'eightshift-forms'),
					'output' => \__('This field has invalid length for phone number.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_MOMENTS_INVALID_SPECIAL_CHARACTERS => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a Moments field contains forbidden special characters.', 'eightshift-forms'),
					'output' => \__('This field contains forbidden special characters.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_WORKABLE_MAX_LENGTH127 => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a Workable field exceeds the 127 character limit.', 'eightshift-forms'),
					'output' => \__('This field is too long. Max length is 127 characters.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_WORKABLE_MAX_LENGTH255 => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a Workable field exceeds the 255 character limit.', 'eightshift-forms'),
					'output' => \__('This field is too long. Max length is 255 characters.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_FILE_EXTENSION_DENIED => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When an uploaded file type is not allowed.', 'eightshift-forms'),
					'output' => \__('This file type is not allowed.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_FILE_MIME_MISMATCH => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When an uploaded file\'s contents do not match its extension.', 'eightshift-forms'),
					'output' => \__('The file contents do not match its extension.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_FILE_MIME_NOT_ALLOWED => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When an uploaded file type is not permitted on this site.', 'eightshift-forms'),
					'output' => \__('This file type is not permitted on this site.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_FILE_SCAN_FAILED => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When an uploaded file could not be processed for security inspection.', 'eightshift-forms'),
					'output' => \__('The file could not be processed for security inspection.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_FILE_PDF_UNSAFE => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When an uploaded PDF contains active content and is rejected.', 'eightshift-forms'),
					'output' => \__('This PDF contains active content (scripts, embedded files or auto-actions) and was rejected.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_FILE_IMAGE_UNSAFE => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When an uploaded image is malformed or contains unexpected content.', 'eightshift-forms'),
					'output' => \__('This image is malformed or contains unexpected content.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_FILE_OFFICE_UNSAFE => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When an uploaded document contains macros or embedded objects and is rejected.', 'eightshift-forms'),
					'output' => \__('This document contains macros, embedded objects or external references and was rejected.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_FILE_CSV_UNSAFE => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When an uploaded CSV or spreadsheet contains potentially malicious formula content.', 'eightshift-forms'),
					'output' => \__('This CSV/spreadsheet contains formula content that could be malicious and was rejected.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_FILE_ARCHIVE_UNSAFE => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When an uploaded archive contains disallowed or unsafe content.', 'eightshift-forms'),
					'output' => \__('This archive contains disallowed or unsafe content and was rejected.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_FILE_TEXT_UNSAFE => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When an uploaded text file contains script content and is rejected.', 'eightshift-forms'),
					'output' => \__('This text file contains script content and was rejected.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_GLOBAL_MISSING_REQUIRED_PARAMS => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When one or more required parameters are missing from the request.', 'eightshift-forms'),
					'output' => \__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
				self::LABEL_VALIDATION_FILE_UPLOAD_SUCCESS => [
					'type' => SettingsValidation::SETTINGS_TYPE_KEY,
					'label' => \__('When a file is uploaded successfully.', 'eightshift-forms'),
					'output' => \__('File uploaded successfully.', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],

				// Workable.
				self::LABEL_WORKABLE_MISSING_CONFIG => [
					'type' => SettingsWorkable::SETTINGS_TYPE_KEY,
					'label' => \__('When Workable integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
					'output' => \__('This form is not configured correctly. Please get in touch with the website administrator to resolve this issue.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => true,
				],
				self::LABEL_WORKABLE_BAD_REQUEST_ERROR => [
					'type' => SettingsWorkable::SETTINGS_TYPE_KEY,
					'label' => \__('When Workable integrations returns a bad request error.', 'eightshift-forms'),
					'output' => \__('Something is not right with the job application. Please check all the fields and try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_WORKABLE_ARCHIVED_JOB_ERROR => [
					'type' => SettingsWorkable::SETTINGS_TYPE_KEY,
					'label' => \__('When Workable integrations returns a archived job error.', 'eightshift-forms'),
					'output' => \__('We apologize, but this job is no longer available. Please try again later, or contact us if you believe this is a mistake.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_WORKABLE_TOO_LONG_FILE_NAME_ERROR => [
					'type' => SettingsWorkable::SETTINGS_TYPE_KEY,
					'label' => \__('When Workable integrations returns a too long file name error.', 'eightshift-forms'),
					'output' => \__('One of your uploaded files has a filename that is too long. Please reduce the filename and try again.', 'eightshift-forms'),
					'isRecommended' => true,
					'outputLabel' => false,
				],
				self::LABEL_WORKABLE_SUCCESS => [
					'type' => SettingsWorkable::SETTINGS_TYPE_KEY,
					'label' => \__('When Workable integration submits the form successfully.', 'eightshift-forms'),
					'output' => \__('Application submitted successfully. Thank you!', 'eightshift-forms'),
					'isRecommended' => false,
					'outputLabel' => true,
				],
			];
		}

		return $labels;
	}
}
