<?php

/**
 * The Theme/Frontend Enqueue specific functionality.
 *
 * @package EightshiftForms\Enqueue\Theme
 */

declare(strict_types=1);

namespace EightshiftForms\Enqueue\Theme;

use EightshiftForms\Config\Config;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\Settings\SettingsSettings;
use EightshiftForms\Captcha\SettingsCaptcha;
use EightshiftForms\Hooks\Filters;
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

	public const CAPTCHA_ENQUEUE_HANDLE = 'captcha';

	/**
	 * Create a new admin instance.
	 *
	 * @param ManifestInterface $manifest Inject manifest which holds data about assets from manifest.json.
	 */
	public function __construct(ManifestInterface $manifest)
	{
		$this->manifest = $manifest;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('wp_enqueue_scripts', [$this, 'enqueueScriptsCaptcha']);
		\add_filter('script_loader_tag', [$this, 'enqueueScriptsCaptchaDefer'], 10, 2);
	}

	/**
	 * Method that returns frontend script with check.
	 *
	 * @return mixed
	 */
	public function enqueueScriptsLocal()
	{
		if ($this->isCheckboxOptionChecked(SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_SCRIPT_KEY, SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY)) {
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
		if ($this->isCheckboxOptionChecked(SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_SCRIPT_KEY, SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY)) {
			return null;
		}

		$this->enqueueStyles();
	}

	/**
	 * Get frontend script dependencies.
	 *
	 * @return array<int, string> List of all the script dependencies.
	 */
	protected function getFrontendScriptDependencies(): array
	{
		if (!\apply_filters(SettingsCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			return [];
		}

		$scriptsDependency = Filters::getFilterName(['general', 'scriptsDependency']);
		$scriptsDependencyOutput = [];

		if (\has_filter($scriptsDependency)) {
			$scriptsDependencyOutput = \apply_filters($scriptsDependency, []);
		}

		return $scriptsDependencyOutput;
	}


	/**
	 * Method that returns frontend script for captcha if settings are correct.
	 *
	 * @return void
	 */
	public function enqueueScriptsCaptcha(): void
	{
		// Check if Captcha data is set and valid.
		$isSettingsGlobalValid = \apply_filters(SettingsCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false);

		// Bailout if settings are not ok.
		if (!$isSettingsGlobalValid) {
			return;
		}

		$handle = "{$this->getAssetsPrefix()}-" . self::CAPTCHA_ENQUEUE_HANDLE;

		$siteKey = !empty(Variables::getGoogleReCaptchaSiteKey()) ? Variables::getGoogleReCaptchaSiteKey() : $this->getOptionValue(SettingsCaptcha::SETTINGS_CAPTCHA_SITE_KEY);

		$isEnterprise = $this->isCheckboxOptionChecked(SettingsCaptcha::SETTINGS_CAPTCHA_ENTERPRISE_KEY, SettingsCaptcha::SETTINGS_CAPTCHA_ENTERPRISE_KEY);

		$url = "https://www.google.com/recaptcha/api.js?render={$siteKey}";

		if ($isEnterprise) {
			$url = "https://www.google.com/recaptcha/enterprise.js?render={$siteKey}";
		}

		\wp_register_script(
			$handle,
			$url,
			$this->getFrontendScriptDependencies(),
			$this->getAssetsVersion(),
			true
		);
		\wp_script_add_data($handle, 'defer', true);
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
	 * Overide default script tag and add defer to it.
	 *
	 * @param string $tag The <script> tag for the enqueued script.
	 * @param string $handle The script's registered handle.
	 *
	 * @return string
	 */
	public function enqueueScriptsCaptchaDefer(string $tag, string $handle): string
	{
		if ($handle !== "{$this->getAssetsPrefix()}-captcha") {
			return $tag;
		}

		return \str_replace(' src', ' defer src', $tag);
	}
}
