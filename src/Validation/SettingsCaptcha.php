<?php

/**
 * Captcha Settings class - Google reCaptcha.
 *
 * @package EightshiftForms\Validation
 */

declare(strict_types=1);

namespace EightshiftForms\Validation;

use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsCaptcha class.
 */
class SettingsCaptcha implements SettingGlobalInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_captcha';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_captcha';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'captcha';

	/**
	 * Captcha Use key.
	 */
	public const SETTINGS_CAPTCHA_USE_KEY = 'captcha-use';

	/**
	 * Google reCaptcha site key.
	 */
	public const SETTINGS_CAPTCHA_SITE_KEY = 'captcha-site-key';

	/**
	 * Google reCaptcha secret key.
	 */
	public const SETTINGS_CAPTCHA_SECRET_KEY = 'captcha-secret-key';

	/**
	 * Google reCaptcha project_id key.
	 */
	public const SETTINGS_CAPTCHA_PROJECT_ID_KEY = 'captcha-project-id-key';
	/**
	 * Google reCaptcha api_key key.
	 */
	public const SETTINGS_CAPTCHA_API_KEY = 'captcha-api-key-key';

	/**
	 * Google reCaptcha score key.
	 */
	public const SETTINGS_CAPTCHA_SCORE_KEY = 'captcha-score';

	/**
	 * Google reCaptcha submit action key.
	 */
	public const SETTINGS_CAPTCHA_SUBMIT_ACTION_KEY = 'captcha-submit-action';
	public const SETTINGS_CAPTCHA_SUBMIT_ACTION_DEFAULT_KEY = 'submit';

	/**
	 * Google reCaptcha score default key.
	 */
	public const SETTINGS_CAPTCHA_SCORE_DEFAULT_KEY = 0.5;

	/**
	 * Is enterprise version key.
	 */
	public const SETTINGS_CAPTCHA_ENTERPRISE_KEY = 'captcha-enterprise';

	/**
	 * Load on init key.
	 */
	public const SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY = 'captcha-load-on-init';

	/**
	 * Google reCaptcha init action key.
	 */
	public const SETTINGS_CAPTCHA_INIT_ACTION_KEY = 'captcha-init-action';
	public const SETTINGS_CAPTCHA_INIT_ACTION_DEFAULT_KEY = 'homepage';

	/**
	 * Hide badge key.
	 */
	public const SETTINGS_CAPTCHA_HIDE_BADGE_KEY = 'captcha-hide-badge';

	/**
	 * Instance variable for labels data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Create a new instance.
	 *
	 * @param LabelsInterface $labels Inject documentsData which holds labels data.
	 */
	public function __construct(LabelsInterface $labels)
	{
		$this->labels = $labels;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_CAPTCHA_USE_KEY, self::SETTINGS_CAPTCHA_USE_KEY);
		$siteKey = !empty(Variables::getGoogleReCaptchaSiteKey()) ? Variables::getGoogleReCaptchaSiteKey() : $this->getOptionValue(self::SETTINGS_CAPTCHA_SITE_KEY);
		$secretKey = !empty(Variables::getGoogleReCaptchaSecretKey()) ? Variables::getGoogleReCaptchaSecretKey() : $this->getOptionValue(self::SETTINGS_CAPTCHA_SECRET_KEY);
		$apiKey = !empty(Variables::getGoogleReCaptchaApiKey()) ? Variables::getGoogleReCaptchaApiKey() : $this->getOptionValue(self::SETTINGS_CAPTCHA_API_KEY);
		$projectIdKey = !empty(Variables::getGoogleReCaptchaProjectIdKey()) ? Variables::getGoogleReCaptchaProjectIdKey() : $this->getOptionValue(self::SETTINGS_CAPTCHA_PROJECT_ID_KEY);

		$isEnterprise = $this->isCheckboxOptionChecked(self::SETTINGS_CAPTCHA_ENTERPRISE_KEY, self::SETTINGS_CAPTCHA_ENTERPRISE_KEY);

		if ($isEnterprise) {
			if (!$isUsed || empty($siteKey) || empty($apiKey) || empty($projectIdKey)) {
				return false;
			}
		} else {
			if (!$isUsed || empty($siteKey) || empty($secretKey)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		// Bailout if feature is not active.
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_CAPTCHA_USE_KEY, self::SETTINGS_CAPTCHA_USE_KEY)) {
			return $this->getNoActiveFeatureOutput();
		}

		$siteKey = Variables::getGoogleReCaptchaSiteKey();
		$secretKey = Variables::getGoogleReCaptchaSecretKey();
		$secretApiKey = Variables::getGoogleReCaptchaApiKey();
		$secretProjectId = Variables::getGoogleReCaptchaProjectIdKey();

		$isEnterprise = $this->isCheckboxOptionChecked(self::SETTINGS_CAPTCHA_ENTERPRISE_KEY, self::SETTINGS_CAPTCHA_ENTERPRISE_KEY);
		$isInit = $this->isCheckboxOptionChecked(self::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY, self::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY);

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'intro',
				// phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
				'introSubtitle' => \__('reCAPTCHA is a free service from Google that helps protect websites from spam and abuse. A “CAPTCHA” is a Turing test to tell human and bots apart.', 'eightshift-forms'),
			],
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('API', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => \__('Captcha type', 'eightshift-forms'),
								'checkboxesName' => $this->getSettingsName(self::SETTINGS_CAPTCHA_ENTERPRISE_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Is enterprise', 'eightshift-forms'),
										'checkboxIsChecked' => $isEnterprise,
										'checkboxValue' => self::SETTINGS_CAPTCHA_ENTERPRISE_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
										'checkboxAsToggleSize' => 'medium',
									],
								],
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_CAPTCHA_SITE_KEY),
								'inputFieldLabel' => \__('Site key', 'eightshift-forms'),
								'inputType' => 'password',
								'inputIsRequired' => true,
								'inputValue' => !empty($siteKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_CAPTCHA_SITE_KEY),
								'inputIsDisabled' => !empty($siteKey),
							],
							...(!$isEnterprise ? [
								[
									'component' => 'input',
									'inputName' => $this->getSettingsName(self::SETTINGS_CAPTCHA_SECRET_KEY),
									'inputFieldLabel' => \__('Secret key', 'eightshift-forms'),
									'inputType' => 'password',
									'inputIsRequired' => true,
									'inputValue' => !empty($secretKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_CAPTCHA_SECRET_KEY),
									'inputIsDisabled' => !empty($secretKey),
								]
							] : [
								[
									'component' => 'input',
									'inputName' => $this->getSettingsName(self::SETTINGS_CAPTCHA_PROJECT_ID_KEY),
									'inputFieldLabel' => \__('Project Id', 'eightshift-forms'),
									'inputType' => 'password',
									'inputIsRequired' => true,
									'inputValue' => !empty($secretProjectId) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_CAPTCHA_PROJECT_ID_KEY),
									'inputIsDisabled' => !empty($secretProjectId),
								],
								[
									'component' => 'input',
									'inputName' => $this->getSettingsName(self::SETTINGS_CAPTCHA_API_KEY),
									'inputFieldLabel' => \__('Api key', 'eightshift-forms'),
									'inputType' => 'password',
									'inputIsRequired' => true,
									'inputValue' => !empty($secretApiKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_CAPTCHA_API_KEY),
									'inputIsDisabled' => !empty($secretApiKey),
								]
							]),
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Advanced', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldHideLabel' => true,
								'checkboxesName' => $this->getSettingsName(self::SETTINGS_CAPTCHA_HIDE_BADGE_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Hide captcha badge', 'eightshift-forms'),
										'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_CAPTCHA_HIDE_BADGE_KEY, self::SETTINGS_CAPTCHA_HIDE_BADGE_KEY),
										'checkboxValue' => self::SETTINGS_CAPTCHA_HIDE_BADGE_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
										'checkboxHelp' => \__('We don\'t reccomend hiding the badge because it is against Google policy. Use at your own risk.', 'eightshift-forms'),
									],
								],
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_CAPTCHA_SCORE_KEY),
								'inputFieldLabel' => \__('"Spam unlikely" threshold', 'eightshift-forms'),
								'inputFieldHelp' => \__('This number determines the level above which a submission is <strong>not</strong> considered spam. In general, normal users will get a score of around 0.8-0.9. The value should be between 0.1 and 1.0 (default is 0.5).', 'eightshift-forms'),
								'inputType' => 'number',
								'inputValue' => $this->getOptionValue(self::SETTINGS_CAPTCHA_SCORE_KEY),
								'inputMin' => 0,
								'inputMax' => 1,
								'inputStep' => 0.1,
								'inputPlaceholder' => self::SETTINGS_CAPTCHA_SCORE_DEFAULT_KEY,
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_CAPTCHA_SUBMIT_ACTION_KEY),
								'inputFieldLabel' => \__('Submit action name', 'eightshift-forms'),
								'inputFieldHelp' => \__('This is the action name that will be sent to Google reCaptcha on form submit.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputValue' => $this->getOptionValue(self::SETTINGS_CAPTCHA_SUBMIT_ACTION_KEY),
								'inputPlaceholder' => self::SETTINGS_CAPTCHA_SUBMIT_ACTION_DEFAULT_KEY,
							],
							[
								'component' => 'divider',
							],
							[
								'component' => 'checkboxes',
								'checkboxesFieldHideLabel' => true,
								'checkboxesName' => $this->getSettingsName(self::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Load on init', 'eightshift-forms'),
										'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY, self::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY),
										'checkboxValue' => self::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
										'checkboxAsToggleSize' => 'medium',
									],
								],
							],
							$isInit ? [
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_CAPTCHA_INIT_ACTION_KEY),
								'inputFieldLabel' => \__('Init action name', 'eightshift-forms'),
								'inputFieldHelp' => \__('This is the action name that will be sent to Google reCaptcha on form init.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputValue' => $this->getOptionValue(self::SETTINGS_CAPTCHA_INIT_ACTION_KEY),
								'inputPlaceholder' => self::SETTINGS_CAPTCHA_INIT_ACTION_DEFAULT_KEY,
							] : [],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									// translators: %s will be replaced with the external link.
									\sprintf(\__('Visit this <a href="%s" target="_blank" rel="noopener noreferrer">link</a>.', 'eightshift-forms'), 'https://www.google.com/recaptcha/admin/create'),
									\__('Configure all the options. Make sure to select <strong>reCaptcha version 3</strong>!', 'eightshift-forms'),
									\__('Copy the API key into the field under the API tab or use the global constant.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}
}
