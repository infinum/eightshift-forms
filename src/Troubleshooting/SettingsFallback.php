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
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsFallback class.
 */
class SettingsFallback implements ServiceInterface, SettingsFallbackDataInterface, SettingGlobalInterface
{
	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_fallback';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_fallback';

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
	public const SETTINGS_FALLBACK_FLAG_SUBMIT_INTEGRATION_ERROR = 'submitIntegrationError';
	public const SETTINGS_FALLBACK_FLAG_SUBMIT_INTEGRATION_SUCCESS = 'submitIntegrationSuccess';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
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
											'selectOptionIsSelected' => $logLevel === 'default',
										],
										[
											'component' => 'select-option',
											'selectOptionValue' => 'insane',
											'selectOptionLabel' => \__('Insane', 'eightshift-forms'),
											'selectOptionIsSelected' => $logLevel === 'insane',
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
	 * Get flags output.
	 *
	 * @return array<string, mixed>
	 */
	private function getFlagsOutput(): array
	{
		$list = [
			self::SETTINGS_FALLBACK_FLAG_CAPTCHA_FEATURE_DISABLED => __('Captcha feature is disabled.', 'eightshift-forms'),
			self::SETTINGS_FALLBACK_FLAG_CAPTCHA_REQUEST_MISSING_TOKEN => __('Captcha request is missing token.', 'eightshift-forms'),
			self::SETTINGS_FALLBACK_FLAG_CAPTCHA_REQUEST_WP_ERROR => __('Captcha request has encountered an WP error.', 'eightshift-forms'),
			self::SETTINGS_FALLBACK_FLAG_CAPTCHA_FREE_OUTPUT_ERROR => __('Captcha type free returned an error response.', 'eightshift-forms'),
			self::SETTINGS_FALLBACK_FLAG_CAPTCHA_ENTERPRISE_OUTPUT_ERROR => __('Captcha type enterprise returned an error response.', 'eightshift-forms'),
			self::SETTINGS_FALLBACK_FLAG_CAPTCHA_WRONG_ACTION => __('Captcha action provided and action returned from the response don\'t match.', 'eightshift-forms'),
			self::SETTINGS_FALLBACK_FLAG_CAPTCHA_SCORE_SPAM => __('Captcha score has been detected as spam.', 'eightshift-forms'),
			self::SETTINGS_FALLBACK_FLAG_CAPTCHA_SUCCESS => __('Captcha request has been successful.', 'eightshift-forms'),
			self::SETTINGS_FALLBACK_FLAG_CAPTCHA_DEBUG_SKIP_CHECK => __('Captcha debug skip check is active.', 'eightshift-forms'),

			self::SETTINGS_FALLBACK_FLAG_GEOLOCATION_FEATURE_DISABLED => __('Geolocation feature is disabled.', 'eightshift-forms'),
			self::SETTINGS_FALLBACK_FLAG_GEOLOCATION_MALFORMED_DECRYPT_DATA => __('Geolocation malformed decrypt data.', 'eightshift-forms'),
			self::SETTINGS_FALLBACK_FLAG_GEOLOCATION_DETECTION_FAILED => __('Geolocation detection failed.', 'eightshift-forms'),
			self::SETTINGS_FALLBACK_FLAG_GEOLOCATION_SUCCESS => __('Geolocation request has been successful.', 'eightshift-forms'),

			self::SETTINGS_FALLBACK_FLAG_PERMISSION_DENIED => __('Someone tried to access the forms API without the proper permissions.', 'eightshift-forms'),
			self::SETTINGS_FALLBACK_FLAG_VALIDATION_MISSING_MANDATORY_PARAMS => __('Someone tried to submit a form without the proper mandatory params.', 'eightshift-forms'),
			self::SETTINGS_FALLBACK_FLAG_VALIDATION_SUBMIT_LOGGED_IN => __('Someone tried to submit a form while not logged in.', 'eightshift-forms'),
			self::SETTINGS_FALLBACK_FLAG_VALIDATION_SUBMIT_ONCE => __('Someone tried to submit a form more than once.', 'eightshift-forms'),
			self::SETTINGS_FALLBACK_FLAG_VALIDATION_SECURITY => __('Someone tried to submit a form with too many requests and was blocked.', 'eightshift-forms'),
			self::SETTINGS_FALLBACK_FLAG_VALIDATION_PARAMS => __('Someone tried to submit a form with missing required params.', 'eightshift-forms'),
			self::SETTINGS_FALLBACK_FLAG_SUBMIT_INTEGRATION_ERROR => __('Someone tried to submit a form to an integration that returned an error.', 'eightshift-forms'),
			self::SETTINGS_FALLBACK_FLAG_SUBMIT_INTEGRATION_SUCCESS => __('Someone tried to submit a form to an integration that returned a success.', 'eightshift-forms'),
		];

		$output = [];

		foreach ($list as $key => $label) {
			$output[] = [
				'component' => 'checkbox',
				'checkboxLabel' => $label,
				'checkboxHelp' => $key,
				'checkboxIsChecked' => SettingsHelpers::isOptionCheckboxChecked($key, self::SETTINGS_FALLBACK_FLAGS_KEY),
				'checkboxValue' => $key,
				'checkboxSingleSubmit' => true,
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
