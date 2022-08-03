<?php

/**
 * The Theme/Frontend Enqueue specific functionality.
 *
 * @package EightshiftForms\Enqueue\Theme
 */

declare(strict_types=1);

namespace EightshiftForms\Enqueue\Theme;

use EightshiftForms\Config\Config;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Tracking\TrackingInterface;
use EightshiftForms\Validation\SettingsCaptcha;
use EightshiftFormsVendor\EightshiftLibs\Manifest\ManifestInterface;
use EightshiftFormsVendor\EightshiftLibs\Enqueue\Theme\AbstractEnqueueTheme;

/**
 * Class EnqueueTheme
 */
class EnqueueTheme extends AbstractEnqueueTheme
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Instance variable of tracking data.
	 *
	 * @var TrackingInterface
	 */
	protected TrackingInterface $tracking;

	/**
	 * Create a new admin instance.
	 *
	 * @param ManifestInterface $manifest Inject manifest which holds data about assets from manifest.json.
	 * @param TrackingInterface $tracking Inject tracking which holds data about for storing to localstorage.
	 */
	public function __construct(ManifestInterface $manifest, TrackingInterface $tracking)
	{
		$this->manifest = $manifest;
		$this->tracking = $tracking;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('wp_enqueue_scripts', [$this, 'enqueueStylesLocal'], 10);
		\add_action('wp_enqueue_scripts', [$this, 'enqueueScriptsLocal']);
		\add_action('wp_enqueue_scripts', [$this, 'enqueueScriptsCaptcha']);
	}

	/**
	 * Method that returns frontend script with check.
	 *
	 * @return mixed
	 */
	public function enqueueScriptsLocal()
	{
		if ($this->isCheckboxOptionChecked(SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_SCRIPT_KEY, SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY)) {
			return null;
		}

		$this->enqueueScripts();
	}

	/**
	 * Method that returns frontend style with check.
	 *
	 * @return mixed
	 */
	public function enqueueStylesLocal()
	{
		if ($this->isCheckboxOptionChecked(SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_SCRIPT_KEY, SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY)) {
			return null;
		}

		$this->enqueueStyles();
	}

	/**
	 * Method that returns frontend script for captcha if settings are correct.
	 *
	 * @return mixed
	 */
	public function enqueueScriptsCaptcha()
	{
		// Check if Captcha data is set and valid.
		$isSettingsGlobalValid = \apply_filters(SettingsCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false);

		// Bailout if settings are not ok.
		if (!$isSettingsGlobalValid) {
			return;
		}

		$handle = "{$this->getAssetsPrefix()}-captcha";

		$siteKey = !empty(Variables::getGoogleReCaptchaSiteKey()) ? Variables::getGoogleReCaptchaSiteKey() : $this->getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_SITE_KEY);

		\wp_register_script(
			$handle,
			"https://www.google.com/recaptcha/api.js?render={$siteKey}",
			$this->getFrontendScriptDependencies(),
			$this->getAssetsVersion(),
			false
		);

		\wp_enqueue_script($handle);
	}

	/**
	 * Method that returns assets name used to prefix asset handlers.
	 *
	 * @return string
	 */
	public function getAssetsPrefix(): string
	{
		return Config::getProjectName();
	}

	/**
	 * Method that returns assets version for versioning asset handlers.
	 *
	 * @return string
	 */
	public function getAssetsVersion(): string
	{
		return Config::getProjectVersion();
	}

	/**
	 * Get script localizations
	 *
	 * @return array<string, mixed>
	 */
	protected function getLocalizations(): array
	{
		$restRoutesPath = \rest_url() . Config::getProjectRoutesNamespace() . '/' . Config::getProjectRoutesVersion();

		$hideGlobalMsgTimeoutFilterName = Filters::getBlockFilterName('form', 'hideGlobalMsgTimeout');
		$redirectionTimeoutFilterName = Filters::getBlockFilterName('form', 'redirectionTimeout');
		$previewRemoveLabelFilterName = Filters::getBlockFilterName('file', 'previewRemoveLabel');
		$hideLoadingStateTimeoutFilterName = Filters::getBlockFilterName('form', 'hideLoadingStateTimeout');

		$output = [
			'formSubmitRestApiUrl' => $restRoutesPath . '/form-submit',
			'hideGlobalMessageTimeout' => \apply_filters($hideGlobalMsgTimeoutFilterName, 6000),
			'redirectionTimeout' => \apply_filters($redirectionTimeoutFilterName, 300),
			'hideLoadingStateTimeout' => \apply_filters($hideLoadingStateTimeoutFilterName, 600),
			'fileCustomRemoveLabel' => \apply_filters($previewRemoveLabelFilterName, \esc_html__('Remove', 'eightshift-forms')),
			'formDisableScrollToFieldOnError' => $this->isCheckboxOptionChecked(
				SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR,
				SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
			),
			'formDisableScrollToGlobalMessageOnSuccess' => $this->isCheckboxOptionChecked(
				SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS,
				SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
			),
			'formDisableAutoInit' => $this->isCheckboxOptionChecked(
				SettingsGeneral::SETTINGS_GENERAL_DISABLE_AUTOINIT_ENQUEUE_SCRIPT_KEY,
				SettingsGeneral::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY
			),
			'formResetOnSuccess' => !Variables::isDevelopMode(),
			'captcha' => '',
			'storage' => '',
		];

		// Check if Captcha data is set and valid.
		$isCaptchaSettingsGlobalValid = \apply_filters(SettingsCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false);

		if ($isCaptchaSettingsGlobalValid) {
			$output['captcha'] = !empty(Variables::getGoogleReCaptchaSiteKey()) ? Variables::getGoogleReCaptchaSiteKey() : $this->getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_SITE_KEY);
		}

		// Localstorage data encrypted.
		$storage = $this->tracking->getTrackingToLocalStorage();

		if ($storage) {
			$output['storage'] = \wp_json_encode([
				'data' => $storage,
				'expiration' => $this->tracking->getTrackingExpiration(),
			]);
		}

		return [
			'esFormsLocalization' => $output,
		];
	}
}
