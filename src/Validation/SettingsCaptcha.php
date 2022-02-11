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
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_CAPTCHA_USE_KEY, self::SETTINGS_CAPTCHA_USE_KEY);
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
			'label' => __('Captcha', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.5 13v-1.5m4 1.5v-1.5m-2-8c.828 0 1.25-.422 1.25-1.25C6.75 1.422 6.328 1 5.5 1c-.828 0-1.25.422-1.25 1.25 0 .828.422 1.25 1.25 1.25zm0 0V5M1 7.3a1.8 1.8 0 0 1 1.8-1.8h5.4A1.8 1.8 0 0 1 10 7.3v2.4a1.8 1.8 0 0 1-1.8 1.8H2.8A1.8 1.8 0 0 1 1 9.7V7.3z" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><circle cx="3.75" cy="8.25" r=".75" fill="#29A3A3"/><circle cx="7.25" cy="8.25" r=".75" fill="#29A3A3"/><path d="M12.264 17.918a4 4 0 0 0 5.654-5.654m-5.654 5.654a4 4 0 1 1 5.654-5.654m-5.654 5.654 5.654-5.654" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
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
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_CAPTCHA_USE_KEY, self::SETTINGS_CAPTCHA_USE_KEY);

		$output = [
			[
				'component' => 'intro',
				'introTitle' => __('Google reCaptcha', 'eightshift-forms'),
				'introSubtitle' => __("To get the Google reCaptcha site key please visit this <a href='https://www.google.com/recaptcha/admin/create' target='_blank' rel='noopener noreferrer'>link</a>. <br /> <br /> <strong>Important:</strong> Make sure to select <strong>reCaptcha version 3</strong>!"),
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => '',
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
						'inputFieldLabel' => __('"Spam unlikely" threshold', 'eightshift-forms'),
						'inputFieldHelp' => __('This number determines the level above which a submission is <strong>not</strong> considered spam. <br /> <br /> The value should be between 0.0 and 1.0 (default is 0.5).', 'eightshift-forms'),
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
