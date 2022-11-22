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
use EightshiftForms\Hooks\Variables;
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
