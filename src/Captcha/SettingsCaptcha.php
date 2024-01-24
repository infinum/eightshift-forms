<?php

/**
 * Captcha Settings class - Google reCaptcha.
 *
 * @package EightshiftForms\Captcha
 */

declare(strict_types=1);

namespace EightshiftForms\Captcha;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsCaptcha class.
 */
class SettingsCaptcha implements UtilsSettingGlobalInterface, ServiceInterface
{
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
		$isUsed = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_CAPTCHA_USE_KEY, self::SETTINGS_CAPTCHA_USE_KEY);
		$siteKey = UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getGoogleReCaptchaSiteKey(), self::SETTINGS_CAPTCHA_SITE_KEY)['value'];
		$secretKey = UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getGoogleReCaptchaSecretKey(), self::SETTINGS_CAPTCHA_SECRET_KEY)['value'];
		$apiKey = UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getGoogleReCaptchaApiKey(), self::SETTINGS_CAPTCHA_API_KEY)['value'];
		$projectIdKey = UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getGoogleReCaptchaProjectIdKey(), self::SETTINGS_CAPTCHA_PROJECT_ID_KEY)['value'];

		$isEnterprise = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_CAPTCHA_ENTERPRISE_KEY, self::SETTINGS_CAPTCHA_ENTERPRISE_KEY);

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
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_CAPTCHA_USE_KEY, self::SETTINGS_CAPTCHA_USE_KEY)) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		$isEnterprise = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_CAPTCHA_ENTERPRISE_KEY, self::SETTINGS_CAPTCHA_ENTERPRISE_KEY);
		$isInit = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY, self::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY);

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'intro',
				// phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
				'introSubtitle' => \__('Protect your website from spam and abuse using Google\'s reCAPTCHA.<br />A captcha is a simple task that is easy for humans to do, but difficult for bots.', 'eightshift-forms'),
			],
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('General', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldHideLabel' => true,
								'checkboxesName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_CAPTCHA_ENTERPRISE_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Use reCAPTCHA Enterprise', 'eightshift-forms'),
										'checkboxIsChecked' => $isEnterprise,
										'checkboxValue' => self::SETTINGS_CAPTCHA_ENTERPRISE_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									],
								],
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							UtilsSettingsOutputHelper::getPasswordFieldWithGlobalVariable(
								UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(
									Variables::getGoogleReCaptchaSiteKey(),
									self::SETTINGS_CAPTCHA_SITE_KEY,
									'ES_GOOGLE_RECAPTCHA_SITE_KEY'
								),
								\__('Site key', 'eightshift-forms'),
							),

							...(!$isEnterprise ? [
								UtilsSettingsOutputHelper::getPasswordFieldWithGlobalVariable(
									UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(
										Variables::getGoogleReCaptchaSecretKey(),
										self::SETTINGS_CAPTCHA_SECRET_KEY,
										'ES_GOOGLE_RECAPTCHA_SECRET_KEY'
									),
									\__('Secret key', 'eightshift-forms'),
								),
							] : [
								UtilsSettingsOutputHelper::getInputFieldWithGlobalVariable(
									UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(
										Variables::getGoogleReCaptchaProjectIdKey(),
										self::SETTINGS_CAPTCHA_PROJECT_ID_KEY,
										'ES_GOOGLE_RECAPTCHA_PROJECT_ID_KEY'
									),
									\__('Project ID', 'eightshift-forms'),
								),
								UtilsSettingsOutputHelper::getPasswordFieldWithGlobalVariable(
									UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(
										Variables::getGoogleReCaptchaApiKey(),
										self::SETTINGS_CAPTCHA_API_KEY,
										'ES_GOOGLE_RECAPTCHA_API_KEY'
									),
									\__('API key', 'eightshift-forms'),
								),
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
								'checkboxesName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_CAPTCHA_HIDE_BADGE_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Hide badge', 'eightshift-forms'),
										'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_CAPTCHA_HIDE_BADGE_KEY, self::SETTINGS_CAPTCHA_HIDE_BADGE_KEY),
										'checkboxValue' => self::SETTINGS_CAPTCHA_HIDE_BADGE_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
										'checkboxHelp' => \__('Not recommended, as it is against Google\'s terms of use.', 'eightshift-forms'),
									],
								],
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'input',
								'inputName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_CAPTCHA_SCORE_KEY),
								'inputFieldLabel' => \__('"Spam unlikely" threshold', 'eightshift-forms'),
								'inputFieldHelp' => \__('The level above which a submission is <strong>not</strong> considered spam. Should be between 0.1 and 1.0.<br />In most cases, a user will receive as core between 0.8 and 0.9.', 'eightshift-forms'),
								'inputType' => 'number',
								'inputValue' => UtilsSettingsHelper::getOptionValue(self::SETTINGS_CAPTCHA_SCORE_KEY),
								'inputMin' => 0.1,
								'inputMax' => 1,
								'inputStep' => 0.1,
								'inputIsNumber' => true,
								'inputPlaceholder' => self::SETTINGS_CAPTCHA_SCORE_DEFAULT_KEY,
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'input',
								'inputName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_CAPTCHA_SUBMIT_ACTION_KEY),
								'inputFieldLabel' => \__('"On submit" action name', 'eightshift-forms'),
								'inputFieldHelp' => \__('Name of the action sent to reCAPTCHA on form submission.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputValue' => UtilsSettingsHelper::getOptionValue(self::SETTINGS_CAPTCHA_SUBMIT_ACTION_KEY),
								'inputPlaceholder' => self::SETTINGS_CAPTCHA_SUBMIT_ACTION_DEFAULT_KEY,
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'checkboxes',
								'checkboxesFieldHideLabel' => true,
								'checkboxesName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Load Captcha on website load', 'eightshift-forms'),
										'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY, self::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY),
										'checkboxValue' => self::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY,
										'checkboxHelp' => \__('By default, Captcha is only loaded on pages that contain forms. However, with this option, you can load Captcha on every page.', 'eightshift-forms'),
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
										'checkboxAsToggleSize' => 'medium',
									],
								],
							],
							$isInit ? [
								'component' => 'input',
								'inputName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_CAPTCHA_INIT_ACTION_KEY),
								'inputFieldLabel' => \__('Action name', 'eightshift-forms'),
								'inputFieldHelp' => \__('Name of the action sent to reCAPTCHA when Captcha is loaded on every page.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputValue' => UtilsSettingsHelper::getOptionValue(self::SETTINGS_CAPTCHA_INIT_ACTION_KEY),
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
