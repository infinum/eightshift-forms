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
use EightshiftForms\Settings\Settings\SettingsDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsCaptcha class.
 */
class SettingsCaptcha implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_captcha';

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
	 * Google reCaptcha score key.
	 */
	public const SETTINGS_CAPTCHA_SCORE_KEY = 'captcha-score';

	/**
	 * Google reCaptcha score default key.
	 */
	public const SETTINGS_CAPTCHA_SCORE_DEFAULT_KEY = 0.5;

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
		\add_filter(self::FILTER_SETTINGS_SIDEBAR_NAME, [$this, 'getSettingsSidebar']);
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
		$isUsed = (bool) $this->isCheckboxOptionChecked(self::SETTINGS_CAPTCHA_USE_KEY, self::SETTINGS_CAPTCHA_USE_KEY);
		$siteKey = !empty(Variables::getGoogleReCaptchaSiteKey()) ? Variables::getGoogleReCaptchaSiteKey() : $this->getOptionValue(self::SETTINGS_CAPTCHA_SITE_KEY);
		$secretKey = !empty(Variables::getGoogleReCaptchaSecretKey()) ? Variables::getGoogleReCaptchaSecretKey() : $this->getOptionValue(self::SETTINGS_CAPTCHA_SECRET_KEY);

		if (!$isUsed || empty($siteKey) || empty($secretKey)) {
			return false;
		}

		return true;
	}

	/**
	 * Get Settings sidebar data.
	 *
	 * @return array<string, mixed>
	 */
	public function getSettingsSidebar(): array
	{
		return [
			'label' => __('Validation', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => '<svg width="30" height="30" xmlns="http://www.w3.org/2000/svg"><g fill-rule="nonzero" fill="none"><path d="M30 14.935c0-.215-.006-.429-.015-.642V2.153L26.629 5.51A14.95 14.95 0 0 0 15.023 0C10.152 0 5.825 2.325 3.089 5.925l5.502 5.56a7.288 7.288 0 0 1 2.228-2.5c.96-.75 2.321-1.363 4.204-1.363.227 0 .403.026.531.077a7.252 7.252 0 0 1 5.545 3.339l-3.894 3.894c4.932-.02 10.504-.03 12.795.002" fill="#1C3AA9"/><path d="M14.935 0c-.215.001-.429.006-.642.016H2.153L5.51 3.372A14.95 14.95 0 0 0 0 14.978c0 4.87 2.325 9.198 5.925 11.933l5.56-5.501a7.288 7.288 0 0 1-2.5-2.228c-.75-.96-1.363-2.322-1.363-4.204 0-.227.026-.403.077-.532a7.253 7.253 0 0 1 3.339-5.544l3.894 3.894c-.02-4.932-.03-10.504.002-12.795" fill="#4285F4"/><path d="M0 14.977c.001.216.006.43.016.642v12.14l3.356-3.356a14.95 14.95 0 0 0 11.606 5.51c4.87 0 9.198-2.325 11.933-5.926l-5.501-5.559a7.288 7.288 0 0 1-2.228 2.5c-.96.75-2.322 1.363-4.204 1.363-.227 0-.403-.027-.532-.077a7.252 7.252 0 0 1-5.544-3.34l3.894-3.893C7.864 15 2.292 15.01 0 14.978" fill="#ABABAB"/></g></svg>',
		];
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
		return [];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		$isUsed = (bool) $this->isCheckboxOptionChecked(self::SETTINGS_CAPTCHA_USE_KEY, self::SETTINGS_CAPTCHA_USE_KEY);

		$output = [
			[
				'component' => 'intro',
				'introTitle' => __('Google reCaptcha', 'eightshift-forms'),
				// translators: %s will be replaced with the google reCaptcha link.
				'introSubtitle' => sprintf(__("Configure your Google reCaptcha in one place. To get Google reCaptcha site key please visit this <a href='%s' target='_blank' rel='noopener noreferrer'>link</a> use <strong>reCaptcha v3 version</strong>."), esc_url('https://www.google.com/recaptcha/admin/create')),
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => __('Check options to use', 'eightshift-forms'),
				'checkboxesFieldHelp' => __('Select integrations you want to use in your form.', 'eightshift-forms'),
				'checkboxesName' => $this->getSettingsName(self::SETTINGS_CAPTCHA_USE_KEY),
				'checkboxesId' => $this->getSettingsName(self::SETTINGS_CAPTCHA_USE_KEY),
				'checkboxesIsRequired' => true,
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('Use Google reCaptcha', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_CAPTCHA_USE_KEY, self::SETTINGS_CAPTCHA_USE_KEY),
						'checkboxValue' => self::SETTINGS_CAPTCHA_USE_KEY,
						'checkboxSingleSubmit' => true,
					]
				]
			],
		];

		if ($isUsed) {
			$siteKey = Variables::getGoogleReCaptchaSiteKey();
			$secretKey = Variables::getGoogleReCaptchaSecretKey();

			$output = array_merge(
				$output,
				[
					[
						'component' => 'input',
						'inputName' => $this->getSettingsName(self::SETTINGS_CAPTCHA_SITE_KEY),
						'inputId' => $this->getSettingsName(self::SETTINGS_CAPTCHA_SITE_KEY),
						'inputFieldLabel' => __('Site key', 'eightshift-forms'),
						'inputType' => 'password',
						'inputIsRequired' => true,
						'inputValue' => !empty($siteKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_CAPTCHA_SITE_KEY),
						'inputIsDisabled' => !empty($siteKey),
					],
					[
						'component' => 'input',
						'inputName' => $this->getSettingsName(self::SETTINGS_CAPTCHA_SECRET_KEY),
						'inputId' => $this->getSettingsName(self::SETTINGS_CAPTCHA_SECRET_KEY),
						'inputFieldLabel' => __('Secret key', 'eightshift-forms'),
						'inputType' => 'password',
						'inputIsRequired' => true,
						'inputValue' => !empty($secretKey) ? 'xxxxxxxxxxxxxxxx' : $this->getOptionValue(self::SETTINGS_CAPTCHA_SECRET_KEY),
						'inputIsDisabled' => !empty($secretKey),
					],
					[
						'component' => 'input',
						'inputId' => $this->getSettingsName(self::SETTINGS_CAPTCHA_SCORE_KEY),
						'inputFieldLabel' => __('Score number', 'eightshift-forms'),
						'inputFieldHelp' => __('Set the score number for what you consider not spam. The scale goes from 0.0 to 1.0. Default is score is 0.5.', 'eightshift-forms'),
						'inputType' => 'number',
						'inputValue' => $this->getOptionValue(self::SETTINGS_CAPTCHA_SCORE_KEY),
						'inputMin' => 0,
						'inputMax' => 1,
						'inputStep' => 0.1,
						'inputPlaceholder' => self::SETTINGS_CAPTCHA_SCORE_DEFAULT_KEY,
					],
				]
			);
		}

		return $output;
	}
}
