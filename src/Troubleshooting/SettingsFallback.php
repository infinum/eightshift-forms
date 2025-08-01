<?php

/**
 * Troubleshooting Settings class.
 *
 * @package EightshiftForms\Troubleshooting
 */

declare(strict_types=1);

namespace EightshiftForms\Troubleshooting;

use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Settings\SettingInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsFallback class.
 */
class SettingsFallback implements ServiceInterface, SettingsFallbackDataInterface, SettingInterface, SettingGlobalInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_fallback';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_fallback';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_fallback';

	/**
	 * Filter settings should log activity key.
	 */
	public const FILTER_SETTINGS_SHOULD_LOG_ACTIVITY_NAME = 'es_forms_settings_should_log_activity_fallback';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'fallback';

	/**
	 * Fallback Use key.
	 */
	public const SETTINGS_FALLBACK_USE_KEY = 'fallback-use';

	/**
	 * Fallback Email key.
	 */
	public const SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY = 'fallback-email';

	/**
	 * Fallback Activity Log Use key.
	 */
	public const SETTINGS_FALLBACK_ACTIVITY_LOG_USE_KEY = 'fallback-activity-log-use';

	/**
	 * Fallback Log Level key.
	 */
	public const SETTINGS_FALLBACK_LOG_LEVEL_KEY = 'fallback-log-level';

	/**
	 * Fallback Auto Delete key.
	 */
	public const SETTINGS_FALLBACK_AUTO_DELETE_KEY = 'fallback-auto-delete';

	/**
	 * Fallback Auto Delete Retention key.
	 */
	public const SETTINGS_FALLBACK_AUTO_DELETE_RETENTION_KEY = 'fallback-auto-delete-retention';

	/**
	 * Fallback Auto Delete Retention Default value.
	 */
	public const SETTINGS_FALLBACK_AUTO_DELETE_RETENTION_DEFAULT_VALUE = 30;

	/**
	 * Fallback Keys key.
	 */
	public const SETTINGS_FALLBACK_FLAGS_KEY = 'fallback-flags';
	public const SETTINGS_FALLBACK_FLAG_CAPTCHA_FEATURE_DISABLED = 'captchaFeatureDisabled';
	public const SETTINGS_FALLBACK_FLAG_CAPTCHA_REQUEST_MISSING_TOKEN = 'captchaRequestMissingToken';
	public const SETTINGS_FALLBACK_FLAG_CAPTCHA_REQUEST_WP_ERROR = 'captchaRequestWpError';
	public const SETTINGS_FALLBACK_FLAG_CAPTCHA_FREE_OUTPUT_ERROR = 'captchaFreeOutputError';
	public const SETTINGS_FALLBACK_FLAG_CAPTCHA_ENTERPRISE_OUTPUT_ERROR = 'captchaEnterpriseOutputError';
	public const SETTINGS_FALLBACK_FLAG_CAPTCHA_WRONG_ACTION = 'captchaWrongAction';
	public const SETTINGS_FALLBACK_FLAG_CAPTCHA_SCORE_SPAM = 'captchaScoreSpam';
	public const SETTINGS_FALLBACK_FLAG_CAPTCHA_SUCCESS = 'captchaSuccess';
	public const SETTINGS_FALLBACK_FLAG_CAPTCHA_DEBUG_SKIP_CHECK = 'captchaDebugSkipCheck';

	public const SETTINGS_FALLBACK_FLAG_GEOLOCATION_FEATURE_DISABLED = 'geolocationFeatureDisabled';
	public const SETTINGS_FALLBACK_FLAG_GEOLOCATION_MALFORMED_DECRYPT_DATA = 'geolocationMalformedDecryptData';
	public const SETTINGS_FALLBACK_FLAG_GEOLOCATION_DETECTION_FAILED = 'geolocationDetectionFailed';
	public const SETTINGS_FALLBACK_FLAG_GEOLOCATION_SUCCESS = 'geolocationSuccess';

	public const SETTINGS_FALLBACK_FLAG_PERMISSION_DENIED = 'permissionDenied';
	public const SETTINGS_FALLBACK_FLAG_VALIDATION_MISSING_MANDATORY_PARAMS = 'validationMissingMandatoryParams';
	public const SETTINGS_FALLBACK_FLAG_VALIDATION_SUBMIT_LOGGED_IN = 'validationSubmitLoggedIn';
	public const SETTINGS_FALLBACK_FLAG_VALIDATION_SUBMIT_ONCE = 'validationSubmitOnce';
	public const SETTINGS_FALLBACK_FLAG_VALIDATION_SECURITY = 'validationSecurity';
	public const SETTINGS_FALLBACK_FLAG_VALIDATION_PARAMS = 'validationParams';
	public const SETTINGS_FALLBACK_FLAG_VALIDATION_FILES = 'validationFiles';
	public const SETTINGS_FALLBACK_FLAG_FILES_UPLOAD_SUCCESS = 'filesUploadSuccess';
	public const SETTINGS_FALLBACK_FLAG_FILES_UPLOAD_ERROR = 'filesUploadError';
	public const SETTINGS_FALLBACK_FLAG_SUBMIT_INTEGRATION_ERROR = 'submitIntegrationError';
	public const SETTINGS_FALLBACK_FLAG_SUBMIT_INTEGRATION_SUCCESS = 'submitIntegrationSuccess';

	public const SETTINGS_FALLBACK_FLAG_CLEARBIT_CRON_ERROR = 'clearbitCronError';
	public const SETTINGS_FALLBACK_FLAG_MOMENTS_EVENTS_ERROR = 'momentsEventsError';

	public const SETTINGS_FALLBACK_FLAG_NATIONBUILDER_LIST_ERROR = 'nationbuilderListError';
	public const SETTINGS_FALLBACK_FLAG_NATIONBUILDER_TAGS_ERROR = 'nationbuilderTagsError';

	public const SETTINGS_FALLBACK_FLAG_WORKABLE_MISSING_CONFIG = 'workableMissingConfig';
	public const SETTINGS_FALLBACK_FLAG_TALENTLYFT_MISSING_CONFIG = 'talentlyftMissingConfig';
	public const SETTINGS_FALLBACK_FLAG_PIPEDRIVE_MISSING_CONFIG = 'pipedriveMissingConfig';
	public const SETTINGS_FALLBACK_FLAG_PAYCEK_MISSING_CONFIG = 'paycekMissingConfig';
	public const SETTINGS_FALLBACK_FLAG_PAYCEK_MISSING_REQ_PARAMS = 'paycekMissingReqParams';
	public const SETTINGS_FALLBACK_FLAG_PAYCEK_SUCCESS = 'paycekSuccess';
	public const SETTINGS_FALLBACK_FLAG_NATIONBUILDER_MISSING_CONFIG = 'nationbuilderMissingConfig';
	public const SETTINGS_FALLBACK_FLAG_MOMENTS_MISSING_CONFIG = 'momentsMissingConfig';
	public const SETTINGS_FALLBACK_FLAG_MAILERLITE_MISSING_CONFIG = 'mailerliteMissingConfig';
	public const SETTINGS_FALLBACK_FLAG_MAILER_MISSING_CONFIG = 'mailerMissingConfig';
	public const SETTINGS_FALLBACK_FLAG_MAILER_ERROR_EMAIL_SEND = 'mailerErrorEmailSend';
	public const SETTINGS_FALLBACK_FLAG_MAILER_SUCCESS = 'mailerSuccess';
	public const SETTINGS_FALLBACK_FLAG_MAILCHIMP_MISSING_CONFIG = 'mailchimpMissingConfig';
	public const SETTINGS_FALLBACK_FLAG_JIRA_MISSING_CONFIG = 'jiraMissingConfig';
	public const SETTINGS_FALLBACK_FLAG_HUBSPOT_MISSING_CONFIG = 'hubspotMissingConfig';
	public const SETTINGS_FALLBACK_FLAG_GREENHOUSE_MISSING_CONFIG = 'greenhouseMissingConfig';
	public const SETTINGS_FALLBACK_FLAG_GOODBITS_MISSING_CONFIG = 'goodbitsMissingConfig';
	public const SETTINGS_FALLBACK_FLAG_CORVUS_MISSING_CONFIG = 'corvusMissingConfig';
	public const SETTINGS_FALLBACK_FLAG_CORVUS_MISSING_REQ_PARAMS = 'corvusMissingReqParams';
	public const SETTINGS_FALLBACK_FLAG_CORVUS_MISSING_STORE_ID = 'corvusMissingStoreId';
	public const SETTINGS_FALLBACK_FLAG_CORVUS_SUCCESS = 'corvusSuccess';
	public const SETTINGS_FALLBACK_FLAG_CALCULATOR_MISSING_CONFIG = 'calculatorMissingConfig';
	public const SETTINGS_FALLBACK_FLAG_CALCULATOR_SUCCESS = 'calculatorSuccess';
	public const SETTINGS_FALLBACK_FLAG_AIRTABLE_MISSING_CONFIG = 'airtableMissingConfig';
	public const SETTINGS_FALLBACK_FLAG_ACTIVE_CAMPAIGN_MISSING_CONFIG = 'activeCampaignMissingConfig';
	public const SETTINGS_FALLBACK_FLAG_CUSTOM_NO_ACTION = 'customNoAction';
	public const SETTINGS_FALLBACK_FLAG_CUSTOM_SUCCESS_REDIRECT = 'customSuccessRedirect';
	public const SETTINGS_FALLBACK_FLAG_CUSTOM_ERROR = 'customError';
	public const SETTINGS_FALLBACK_FLAG_CUSTOM_WP_ERROR = 'customWpError';
	public const SETTINGS_FALLBACK_FLAG_CUSTOM_SUCCESS = 'customSuccess';

	public const SETTINGS_FALLBACK_FLAG_VALIDATION_STEPS_CURRENT_STEP_PROBLEM = 'validationStepsCurrentStepProblem';
	public const SETTINGS_FALLBACK_FLAG_VALIDATION_STEPS_FIELDS_PROBLEM = 'validationStepsFieldsProblem';
	public const SETTINGS_FALLBACK_FLAG_VALIDATION_STEPS_NEXT_STEP_PROBLEM = 'validationStepsNextStepProblem';
	public const SETTINGS_FALLBACK_FLAG_VALIDATION_STEPS_PARAMETERS_PROBLEM = 'validationStepsParametersProblem';
	public const SETTINGS_FALLBACK_FLAG_VALIDATION_STEPS_SUCCESS = 'validationStepsSuccess';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
		\add_filter(self::FILTER_SETTINGS_SHOULD_LOG_ACTIVITY_NAME, [$this, 'shouldLogActivity'], 10, 2);
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_FALLBACK_USE_KEY, self::SETTINGS_FALLBACK_USE_KEY);
		$isActivityLogUsed = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_FALLBACK_ACTIVITY_LOG_USE_KEY, self::SETTINGS_FALLBACK_ACTIVITY_LOG_USE_KEY);

		if (!$isUsed || !$isActivityLogUsed) {
			return false;
		}

		return true;
	}

	/**
	 * Log activity.
	 *
	 * @param bool $isSettingsValid Is settings valid.
	 * @param string $key Key to check.
	 *
	 * @return bool
	 */
	public function shouldLogActivity(bool $isSettingsValid, string $key): bool
	{
		if (!$key) {
			return false;
		}

		if (!$this->isSettingsGlobalValid()) {
			return false;
		}

		return SettingsHelpers::isOptionCheckboxChecked($key, SettingsFallback::SETTINGS_FALLBACK_FLAGS_KEY);
	}

	/**
	 * Get Form settings data array
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsData(string $formId): array
	{
		// Bailout if feature is not active.
		if (!$this->isSettingsGlobalValid()) {
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-clean',
				'layoutContent' => [
					[
						'component' => 'card-inline',
						'cardInlineTitle' => \__('View all activity logs in database', 'eightshift-forms'),
						'cardInlineRightContent' => [
							[
								'component' => 'submit',
								'submitVariant' => 'ghost',
								'submitButtonAsLink' => true,
								'submitButtonAsLinkUrl' => GeneralHelpers::getListingPageUrl(Config::SLUG_ADMIN_LISTING_ACTIVITY_LOGS, $formId),
								'submitValue' => \__('View', 'eightshift-forms'),
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		if (!SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_FALLBACK_USE_KEY, self::SETTINGS_FALLBACK_USE_KEY)) {
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		$activityLogUse = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_FALLBACK_ACTIVITY_LOG_USE_KEY, self::SETTINGS_FALLBACK_ACTIVITY_LOG_USE_KEY);
		$logLevel = SettingsHelpers::getOptionValue(self::SETTINGS_FALLBACK_LOG_LEVEL_KEY);
		$autoDeleteIsUsed = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_FALLBACK_AUTO_DELETE_KEY, self::SETTINGS_FALLBACK_AUTO_DELETE_KEY);

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('E-mail', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_FALLBACK_ACTIVITY_LOG_USE_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Enable activity log', 'eightshift-forms'),
										'checkboxIsChecked' => $activityLogUse,
										'checkboxValue' => self::SETTINGS_FALLBACK_ACTIVITY_LOG_USE_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									],
								],
							],
							...($activityLogUse ? [
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								[
									'component' => 'layout',
									'layoutType' => 'layout-v-stack-clean',
									'layoutContent' => [
										[
											'component' => 'card-inline',
											'cardInlineTitle' => \__('View all activity logs in database', 'eightshift-forms'),
											'cardInlineRightContent' => [
												[
													'component' => 'submit',
													'submitVariant' => 'ghost',
													'submitButtonAsLink' => true,
													'submitButtonAsLinkUrl' => GeneralHelpers::getListingPageUrl(Config::SLUG_ADMIN_LISTING_ACTIVITY_LOGS),
													'submitValue' => \__('View all activity logs', 'eightshift-forms'),
												],
											],
										],
									],
								],
								[
									'component' => 'checkboxes',
									'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_FALLBACK_AUTO_DELETE_KEY),
									'checkboxesContent' => [
										[
											'component' => 'checkbox',
											'checkboxLabel' => \__('Auto-delete old activity logs', 'eightshift-forms'),
											'checkboxHelp' => \__('Activity logs older than the retention interval will be automatically deleted.', 'eightshift-forms'),
											'checkboxIsChecked' => $autoDeleteIsUsed,
											'checkboxValue' => self::SETTINGS_FALLBACK_AUTO_DELETE_KEY,
											'checkboxSingleSubmit' => true,
											'checkboxAsToggle' => true,
										],
									],
								],
								...($autoDeleteIsUsed ? [
									[
										'component' => 'input',
										'inputName' => SettingsHelpers::getSettingName(self::SETTINGS_FALLBACK_AUTO_DELETE_RETENTION_KEY),
										'inputFieldLabel' => \__('Retention interval', 'eightshift-forms'),
										'inputFieldHelp' => \__('Duration of time in days an activity log should be retained in the database.', 'eightshift-forms'),
										'inputType' => 'number',
										'inputMin' => 1,
										'inputMax' => 365,
										'inputStep' => 1,
										'inputIsNumber' => true,
										'inputPlaceholder' => self::SETTINGS_FALLBACK_AUTO_DELETE_RETENTION_DEFAULT_VALUE,
										'inputFieldAfterContent' => \__('days', 'eightshift-forms'),
										'inputFieldInlineBeforeAfterContent' => true,
										'inputValue' => SettingsHelpers::getOptionValue(self::SETTINGS_FALLBACK_AUTO_DELETE_RETENTION_KEY),
									],
								] : []),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								[
									'component' => 'input',
									'inputName' => SettingsHelpers::getOptionName(self::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY),
									'inputFieldLabel' => \__('Fallback e-mail', 'eightshift-forms'),
									'inputFieldHelp' => \__('E-mail will be added to the "CC" field; the "From" field will be read from global settings.<br />Use commas to separate multiple e-mails.', 'eightshift-forms'),
									'inputType' => 'text',
									'inputValue' => SettingsHelpers::getOptionValue(self::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY),
								],
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								[
									'component' => 'select',
									'selectName' => SettingsHelpers::getOptionName(self::SETTINGS_FALLBACK_LOG_LEVEL_KEY),
									'selectValue' => $logLevel,
									'selectFieldLabel' => \__('Log level', 'eightshift-forms'),
									'selectFieldHelp' => \__('The log level to use for the activity log.', 'eightshift-forms'),
									'selectContent' => [
										[
											'component' => 'select-option',
											'selectOptionValue' => 'minimal',
											'selectOptionLabel' => \__('Minimal', 'eightshift-forms'),
											'selectOptionIsSelected' => $logLevel === 'minimal',
										],
										[
											'component' => 'select-option',
											'selectOptionValue' => 'default',
											'selectOptionLabel' => \__('Default', 'eightshift-forms'),
											'selectOptionIsSelected' => $logLevel === 'default' || $logLevel === '',
										],
										[
											'component' => 'select-option',
											'selectOptionValue' => 'fullMax',
											'selectOptionLabel' => \__('FULL MAX', 'eightshift-forms'),
											'selectOptionIsSelected' => $logLevel === 'fullMax',
										],
									],
								],
								$this->getFlagsOutput(),
							] : []),
						],
					],
				],
			],
		];
	}

	/**
	 * Output array settings for form.
	 *
	 * @param string $integration Integration name used for fallback.
	 *
	 * @return array<string, array<int, array<string, bool|string>>|string>
	 */
	public function getOutputGlobalFallback(string $integration): array
	{
		return $this->isSettingsGlobalValid() ? [
			'component' => 'tab',
			'tabLabel' => \__('Fallback e-mail', 'eightshift-forms'),
			'tabContent' => [
				[
					'component' => 'intro',
					'introSubtitle' => \__('In case a form submission fails, Eightshift Forms can send a plain-text e-mail with all the submitted data as a fallback. The data can then be used for debugging and manual processing.', 'eightshift-forms'),
				],
				[
					'component' => 'divider',
					'dividerExtraVSpacing' => true,
				],
				[
					'component' => 'input',
					'inputName' => SettingsHelpers::getOptionName(self::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY . '-' . $integration),
					'inputFieldLabel' => \__('Fallback e-mail', 'eightshift-forms'),
					'inputFieldHelp' => \__('E-mail will be added to the "CC" field; the "From" field will be read from global settings.<br />Use commas to separate multiple e-mails.', 'eightshift-forms'),
					'inputType' => 'text',
					'inputValue' => SettingsHelpers::getOptionValue(self::SETTINGS_FALLBACK_FALLBACK_EMAIL_KEY . '-' . $integration),
				],
			],
		] : [];
	}

	/**
	 * Get flag label.
	 *
	 * @param string $key Key to get label for.
	 *
	 * @return string
	 */
	public function getFlagLabel(string $key): string
	{
		return $this->getFlagsList()[$key]['label'] ?? '';
	}

	/**
	 * Get flags list.
	 *
	 * @return array<string, mixed>
	 */
	private function getFlagsList(): array
	{
		return [
			// Captcha.
			self::SETTINGS_FALLBACK_FLAG_CAPTCHA_FEATURE_DISABLED => [
				'label' => __('Captcha feature is disabled.', 'eightshift-forms'),
				'isRecommended' => false,
			],
			self::SETTINGS_FALLBACK_FLAG_CAPTCHA_REQUEST_MISSING_TOKEN => [
				'label' => __('Captcha request is missing token.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_CAPTCHA_REQUEST_WP_ERROR => [
				'label' => __('Captcha request has encountered an WP error.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_CAPTCHA_FREE_OUTPUT_ERROR => [
				'label' => __('Captcha type free returned an error response.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_CAPTCHA_ENTERPRISE_OUTPUT_ERROR => [
				'label' => __('Captcha type enterprise returned an error response.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_CAPTCHA_WRONG_ACTION => [
				'label' => __('Captcha action provided and action returned from the response don\'t match.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_CAPTCHA_SCORE_SPAM => [
				'label' => __('Captcha score has been detected as spam.', 'eightshift-forms'),
				'isRecommended' => false,
			],
			self::SETTINGS_FALLBACK_FLAG_CAPTCHA_SUCCESS => [
				'label' => __('Captcha request has been successful.', 'eightshift-forms'),
				'isRecommended' => false,
			],
			self::SETTINGS_FALLBACK_FLAG_CAPTCHA_DEBUG_SKIP_CHECK => [
				'label' => __('Captcha debug skip check is active.', 'eightshift-forms'),
				'isRecommended' => true,
			],

			// Geolocation.
			self::SETTINGS_FALLBACK_FLAG_GEOLOCATION_FEATURE_DISABLED => [
				'label' => __('Geolocation feature is disabled.', 'eightshift-forms'),
				'isRecommended' => false,
			],
			self::SETTINGS_FALLBACK_FLAG_GEOLOCATION_MALFORMED_DECRYPT_DATA => [
				'label' => __('Geolocation malformed decrypt data.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_GEOLOCATION_DETECTION_FAILED => [
				'label' => __('Geolocation detection failed.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_GEOLOCATION_SUCCESS => [
				'label' => __('Geolocation request has been successful.', 'eightshift-forms'),
				'isRecommended' => false,
			],

			// Validation.
			self::SETTINGS_FALLBACK_FLAG_PERMISSION_DENIED => [
				'label' => __('Someone tried to access the forms API without the proper permissions.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_VALIDATION_MISSING_MANDATORY_PARAMS => [
				'label' => __('Someone tried to submit a form without the proper mandatory params.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_VALIDATION_SUBMIT_LOGGED_IN => [
				'label' => __('Someone tried to submit a form while not logged in.', 'eightshift-forms'),
				'isRecommended' => false,
			],
			self::SETTINGS_FALLBACK_FLAG_VALIDATION_SUBMIT_ONCE => [
				'label' => __('Someone tried to submit a form more than once.', 'eightshift-forms'),
				'isRecommended' => false,
			],
			self::SETTINGS_FALLBACK_FLAG_VALIDATION_SECURITY => [
				'label' => __('Someone tried to submit a form with too many requests and was blocked.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_VALIDATION_PARAMS => [
				'label' => __('Someone tried to submit a form with missing required params.', 'eightshift-forms'),
				'isRecommended' => false,
			],
			self::SETTINGS_FALLBACK_FLAG_VALIDATION_FILES => [
				'label' => __('Someone tried to submit a form with missing required files.', 'eightshift-forms'),
				'isRecommended' => false,
			],
			self::SETTINGS_FALLBACK_FLAG_FILES_UPLOAD_SUCCESS => [
				'label' => __('Someone tried to submit a form with files upload success.', 'eightshift-forms'),
				'isRecommended' => false,
			],
			self::SETTINGS_FALLBACK_FLAG_FILES_UPLOAD_ERROR => [
				'label' => __('Someone tried to submit a form with files upload error.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_SUBMIT_INTEGRATION_ERROR => [
				'label' => __('Someone tried to submit a form to an integration that returned an error.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_SUBMIT_INTEGRATION_SUCCESS => [
				'label' => __('Someone tried to submit a form to an integration that returned a success.', 'eightshift-forms'),
				'isRecommended' => false,
			],

			// Clearbit.
			self::SETTINGS_FALLBACK_FLAG_CLEARBIT_CRON_ERROR => [
				'label' => __('When Clearbit cron job is running, it can return an error for unknown entry.', 'eightshift-forms'),
				'isRecommended' => true,
			],

			// Moments.
			self::SETTINGS_FALLBACK_FLAG_MOMENTS_MISSING_CONFIG => [
				'label' => __('When Moments integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_MOMENTS_EVENTS_ERROR => [
				'label' => __('When Moments events are being sent, it can return an error.', 'eightshift-forms'),
				'isRecommended' => true,
			],

			// Nationbuilder.
			self::SETTINGS_FALLBACK_FLAG_NATIONBUILDER_MISSING_CONFIG => [
				'label' => __('When Nationbuilder integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_NATIONBUILDER_LIST_ERROR => [
				'label' => __('When Nationbuilder cron job is running, it can return an error.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_NATIONBUILDER_TAGS_ERROR => [
				'label' => __('When Nationbuilder cron job is running, it can return an error.', 'eightshift-forms'),
				'isRecommended' => true,
			],

			// Workable.
			self::SETTINGS_FALLBACK_FLAG_WORKABLE_MISSING_CONFIG => [
				'label' => __('When Workable integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
				'isRecommended' => true,
			],

			// Talentlyft.
			self::SETTINGS_FALLBACK_FLAG_TALENTLYFT_MISSING_CONFIG => [
				'label' => __('When Talentlyft integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
				'isRecommended' => true,
			],

			// Pipedrive.
			self::SETTINGS_FALLBACK_FLAG_PIPEDRIVE_MISSING_CONFIG => [
				'label' => __('When Pipedrive integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
				'isRecommended' => true,
			],

			// Paycek.
			self::SETTINGS_FALLBACK_FLAG_PAYCEK_MISSING_CONFIG => [
				'label' => __('When Paycek integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_PAYCEK_MISSING_REQ_PARAMS => [
				'label' => __('When Paycek integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_PAYCEK_SUCCESS => [
				'label' => __('When Paycek integrations is able to send a request.', 'eightshift-forms'),
				'isRecommended' => false,
			],

			// Mailerlite.
			self::SETTINGS_FALLBACK_FLAG_MAILERLITE_MISSING_CONFIG => [
				'label' => __('When Mailerlite integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
				'isRecommended' => true,
			],

			// Mailer.
			self::SETTINGS_FALLBACK_FLAG_MAILER_MISSING_CONFIG => [
				'label' => __('When Mailer integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_MAILER_ERROR_EMAIL_SEND => [
				'label' => __('When Mailer integrations is not able to send an email.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_MAILER_SUCCESS => [
				'label' => __('When Mailer integrations is able to send an email.', 'eightshift-forms'),
				'isRecommended' => false,
			],

			// Mailchimp.
			self::SETTINGS_FALLBACK_FLAG_MAILCHIMP_MISSING_CONFIG => [
				'label' => __('When Mailchimp integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
				'isRecommended' => true,
			],

			// Jira.
			self::SETTINGS_FALLBACK_FLAG_JIRA_MISSING_CONFIG => [
				'label' => __('When Jira integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
				'isRecommended' => true,
			],

			// Hubspot.
			self::SETTINGS_FALLBACK_FLAG_HUBSPOT_MISSING_CONFIG => [
				'label' => __('When Hubspot integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
				'isRecommended' => true,
			],

			// Greenhouse.
			self::SETTINGS_FALLBACK_FLAG_GREENHOUSE_MISSING_CONFIG => [
				'label' => __('When Greenhouse integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
				'isRecommended' => true,
			],

			// Goodbits.
			self::SETTINGS_FALLBACK_FLAG_GOODBITS_MISSING_CONFIG => [
				'label' => __('When Goodbits integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
				'isRecommended' => true,
			],

			// Corvus.
			self::SETTINGS_FALLBACK_FLAG_CORVUS_MISSING_CONFIG => [
				'label' => __('When Corvus integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_CORVUS_MISSING_REQ_PARAMS => [
				'label' => __('When Corvus integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_CORVUS_MISSING_STORE_ID => [
				'label' => __('When Corvus integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_CORVUS_SUCCESS => [
				'label' => __('When Corvus integrations is able to send a request.', 'eightshift-forms'),
				'isRecommended' => false,
			],

			// Calculator.
			self::SETTINGS_FALLBACK_FLAG_CALCULATOR_MISSING_CONFIG => [
				'label' => __('When Calculator integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_CALCULATOR_SUCCESS => [
				'label' => __('When Calculator integrations is able to calculate the form.', 'eightshift-forms'),
				'isRecommended' => false,
			],

			// Airtable.
			self::SETTINGS_FALLBACK_FLAG_AIRTABLE_MISSING_CONFIG => [
				'label' => __('When Airtable integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
				'isRecommended' => true,
			],

			// ActiveCampaign.
			self::SETTINGS_FALLBACK_FLAG_ACTIVE_CAMPAIGN_MISSING_CONFIG => [
				'label' => __('When ActiveCampaign integrations is not configured correctly, ether globally or per form.', 'eightshift-forms'),
				'isRecommended' => true,
			],

			// Custom.
			self::SETTINGS_FALLBACK_FLAG_CUSTOM_NO_ACTION => [
				'label' => __('When custom action is not set.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_CUSTOM_SUCCESS_REDIRECT => [
				'label' => __('When custom action is successful and redirect is set.', 'eightshift-forms'),
				'isRecommended' => false,
			],
			self::SETTINGS_FALLBACK_FLAG_CUSTOM_ERROR => [
				'label' => __('When custom action returns an error.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_CUSTOM_WP_ERROR => [
				'label' => __('When custom action returns a WP error.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_CUSTOM_SUCCESS => [
				'label' => __('When custom action is successful.', 'eightshift-forms'),
				'isRecommended' => false,
			],

			// Steps.
			self::SETTINGS_FALLBACK_FLAG_VALIDATION_STEPS_CURRENT_STEP_PROBLEM => [
				'label' => __('When validation steps current step is not set.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_VALIDATION_STEPS_FIELDS_PROBLEM => [
				'label' => __('When validation steps fields are not set.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_VALIDATION_STEPS_NEXT_STEP_PROBLEM => [
				'label' => __('When validation steps next step is not set.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_VALIDATION_STEPS_PARAMETERS_PROBLEM => [
				'label' => __('When validation steps parameters are not set.', 'eightshift-forms'),
				'isRecommended' => true,
			],
			self::SETTINGS_FALLBACK_FLAG_VALIDATION_STEPS_SUCCESS => [
				'label' => __('When validation steps is successful.', 'eightshift-forms'),
				'isRecommended' => false,
			],
		];
	}

	/**
	 * Get flags output.
	 *
	 * @return array<string, mixed>
	 */
	private function getFlagsOutput(): array
	{


		$output = [];

		foreach ($this->getFlagsList() as $key => $value) {
			$label = $value['label'] ?? '';
			$isRecommended = $value['isRecommended'] ?? false;

			$output[] = [
				'component' => 'checkbox',
				'checkboxLabel' => $key,
				'checkboxHelp' => sprintf(__('%s %s', 'eightshift-forms'), $label, ($isRecommended ? \__('<br/><strong class="info-strong">Recommended.</strong>', 'eightshift-forms') : '')),
				'checkboxIsChecked' => SettingsHelpers::isOptionCheckboxChecked($key, self::SETTINGS_FALLBACK_FLAGS_KEY),
				'checkboxValue' => $key,
				'checkboxAsToggle' => true,
			];
		}

		return [
			'component' => 'checkboxes',
			'checkboxesFieldLabel' => '',
			'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_FALLBACK_FLAGS_KEY),
			'checkboxesContent' => $output,
		];
	}
}
