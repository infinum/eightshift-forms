<?php

/**
 * Captcha Settings class - Google reCaptcha.
 *
 * @package EightshiftForms\Captcha
 */

declare(strict_types=1);

namespace EightshiftForms\Captcha;

use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsCaptcha class.
 */
class SettingsCaptcha implements SettingGlobalInterface, ServiceInterface
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
		$isUsed = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_CAPTCHA_USE_KEY, self::SETTINGS_CAPTCHA_USE_KEY);
		$siteKey = (bool) SettingsHelpers::getOptionWithConstant(Variables::getGoogleReCaptchaSiteKey(), self::SETTINGS_CAPTCHA_SITE_KEY);
		$secretKey = (bool) SettingsHelpers::getOptionWithConstant(Variables::getGoogleReCaptchaSecretKey(), self::SETTINGS_CAPTCHA_SECRET_KEY);
		$apiKey = (bool) SettingsHelpers::getOptionWithConstant(Variables::getGoogleReCaptchaApiKey(), self::SETTINGS_CAPTCHA_API_KEY);
		$projectIdKey = (bool) SettingsHelpers::getOptionWithConstant(Variables::getGoogleReCaptchaProjectIdKey(), self::SETTINGS_CAPTCHA_PROJECT_ID_KEY);

		$isEnterprise = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_CAPTCHA_ENTERPRISE_KEY, self::SETTINGS_CAPTCHA_ENTERPRISE_KEY);

		if ($isEnterprise) {
			if (!$isUsed || !$siteKey || !$apiKey || !$projectIdKey) {
				return false;
			}
		} else {
			if (!$isUsed || !$siteKey || !$secretKey) {
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
		if (!SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_CAPTCHA_USE_KEY, self::SETTINGS_CAPTCHA_USE_KEY)) {
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		$isEnterprise = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_CAPTCHA_ENTERPRISE_KEY, self::SETTINGS_CAPTCHA_ENTERPRISE_KEY);
		$isInit = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY, self::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY);

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
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
								'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_CAPTCHA_ENTERPRISE_KEY),
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
							SettingsOutputHelpers::getPasswordFieldWithGlobalVariable(
								Variables::getGoogleReCaptchaSiteKey(),
								self::SETTINGS_CAPTCHA_SITE_KEY,
								'ES_GOOGLE_RECAPTCHA_SITE_KEY',
								\__('Site key', 'eightshift-forms'),
							),

							...(!$isEnterprise ? [
								SettingsOutputHelpers::getPasswordFieldWithGlobalVariable(
									Variables::getGoogleReCaptchaSecretKey(),
									self::SETTINGS_CAPTCHA_SECRET_KEY,
									'ES_GOOGLE_RECAPTCHA_SECRET_KEY',
									\__('Secret key', 'eightshift-forms'),
								),
							] : [
								SettingsOutputHelpers::getInputFieldWithGlobalVariable(
									Variables::getGoogleReCaptchaProjectIdKey(),
									self::SETTINGS_CAPTCHA_PROJECT_ID_KEY,
									'ES_GOOGLE_RECAPTCHA_PROJECT_ID_KEY',
									\__('Project ID', 'eightshift-forms'),
								),
								SettingsOutputHelpers::getPasswordFieldWithGlobalVariable(
									Variables::getGoogleReCaptchaApiKey(),
									self::SETTINGS_CAPTCHA_API_KEY,
									'ES_GOOGLE_RECAPTCHA_API_KEY',
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
								'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_CAPTCHA_HIDE_BADGE_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Hide badge', 'eightshift-forms'),
										'checkboxIsChecked' => SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_CAPTCHA_HIDE_BADGE_KEY, self::SETTINGS_CAPTCHA_HIDE_BADGE_KEY),
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
								'inputName' => SettingsHelpers::getOptionName(self::SETTINGS_CAPTCHA_SCORE_KEY),
								'inputFieldLabel' => \__('"Spam unlikely" threshold', 'eightshift-forms'),
								'inputFieldHelp' => \__('The level above which a submission is <strong>not</strong> considered spam. Should be between 0.1 and 1.0.<br />In most cases, a user will receive as core between 0.8 and 0.9.', 'eightshift-forms'),
								'inputType' => 'number',
								'inputValue' => SettingsHelpers::getOptionValue(self::SETTINGS_CAPTCHA_SCORE_KEY),
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
								'inputName' => SettingsHelpers::getOptionName(self::SETTINGS_CAPTCHA_SUBMIT_ACTION_KEY),
								'inputFieldLabel' => \__('"On submit" action name', 'eightshift-forms'),
								'inputFieldHelp' => \__('Name of the action sent to reCAPTCHA on form submission.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputValue' => SettingsHelpers::getOptionValue(self::SETTINGS_CAPTCHA_SUBMIT_ACTION_KEY),
								'inputPlaceholder' => self::SETTINGS_CAPTCHA_SUBMIT_ACTION_DEFAULT_KEY,
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'checkboxes',
								'checkboxesFieldHideLabel' => true,
								'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Load Captcha on website load', 'eightshift-forms'),
										'checkboxIsChecked' => SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY, self::SETTINGS_CAPTCHA_LOAD_ON_INIT_KEY),
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
								'inputName' => SettingsHelpers::getOptionName(self::SETTINGS_CAPTCHA_INIT_ACTION_KEY),
								'inputFieldLabel' => \__('Action name', 'eightshift-forms'),
								'inputFieldHelp' => \__('Name of the action sent to reCAPTCHA when Captcha is loaded on every page.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputValue' => SettingsHelpers::getOptionValue(self::SETTINGS_CAPTCHA_INIT_ACTION_KEY),
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
								'stepsTitle' => \__('How to get the Free reCAPTCHA API key?', 'eightshift-forms'),
								'stepsContent' => [
									// translators: %s will be replaced with the external link.
									\sprintf(\__('Visit this <a href="%s" target="_blank" rel="noopener noreferrer">link</a>.', 'eightshift-forms'), 'https://www.google.com/recaptcha/admin/create'),
									\__('Configure all the options. Make sure to select <strong>reCaptcha version 3</strong>!', 'eightshift-forms'),
									\__('Copy the API key into the field under the API tab or use the global constant.', 'eightshift-forms'),
								],
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the Enterprise reCAPTCHA API key?', 'eightshift-forms'),
								'stepsContent' => [
									// translators: %s will be replaced with the external link.
									\sprintf(\__('Visit <a href="%s" target="_blank" rel="noopener noreferrer">Google Cloud Console</a>.', 'eightshift-forms'), 'https://console.cloud.google.com/'),
									\__('Create a new project and set that project as <strong>Project ID</strong>.', 'eightshift-forms'),
									\__('Search and go to <strong>reCAPTCHA</strong> product.', 'eightshift-forms'),
									\__('You will probably need to set billing service for this product.', 'eightshift-forms'),
									\__('Create a new key and set that key as <strong>Site key</strong>.', 'eightshift-forms'),
									// translators: %s will be replaced with the website domain.
									\sprintf(\__('Limit the key to your website domain. Domain: <strong>%s</strong> (exact, no trailing slash and protocol).', 'eightshift-forms'), \preg_replace("(^https?://)", "", \site_url())),
									\__('Search and go to <strong>API & Services</strong> product.', 'eightshift-forms'),
									\__('Go to <strong>Credentials</strong> section and create a new API key.', 'eightshift-forms'),
									// translators: %s will be replaced with the website domain.
									\sprintf(\__('Create a new key for <strong>Website</strong>, add restrictions to your website domain <strong>%s</strong> (exact, no trailing slash, with protocol) and set API restrictions to <strong>reCAPTCHA Enterprise</strong>.', 'eightshift-forms'), \esc_url(\site_url())),
									\__('Set that key as <strong>API key</strong>.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}
}
