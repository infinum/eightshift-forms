<?php

/**
 * The Theme/Frontend Enqueue specific functionality - Captcha.
 *
 * @package EightshiftForms\Enqueue\Captcha
 */

declare(strict_types=1);

namespace EightshiftForms\Enqueue\Captcha;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Captcha\SettingsCaptcha;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Enqueue\Theme\AbstractEnqueueTheme;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

/**
 * Class EnqueueCaptcha
 */
class EnqueueCaptcha extends AbstractEnqueueTheme
{
	/**
	 * Captcha enqueue handle.
	 *
	 * @var string
	 */
	public const CAPTCHA_ENQUEUE_HANDLE = 'captcha';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('wp_enqueue_scripts', [$this, 'enqueueScriptsCaptcha']);
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

		$scriptsDependency = UtilsHooksHelper::getFilterName(['scripts', 'dependency', 'captcha']);
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

		$siteKey = UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getGoogleReCaptchaSiteKey(), SettingsCaptcha::SETTINGS_CAPTCHA_SITE_KEY)['value'];

		$isEnterprise = UtilsSettingsHelper::isOptionCheckboxChecked(SettingsCaptcha::SETTINGS_CAPTCHA_ENTERPRISE_KEY, SettingsCaptcha::SETTINGS_CAPTCHA_ENTERPRISE_KEY);

		$url = "https://www.google.com/recaptcha/api.js?render={$siteKey}";

		if ($isEnterprise) {
			$url = "https://www.google.com/recaptcha/enterprise.js?render={$siteKey}";
		}

		\wp_register_script(
			$handle,
			$url,
			$this->getFrontendScriptDependencies(),
			$this->getAssetsVersion(),
			\is_wp_version_compatible('6.3') ? $this->scriptArgs() : $this->scriptInFooter()
		);
		\wp_enqueue_script($handle);
	}

	/**
	 * Load script 'defer' or 'async'.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_enqueue_style/
	 *
	 * @return string Whether to enqueue the script normally, with defer or async.
	 * Default value: normal
	 */
	protected function scriptStrategy(): string
	{
		return 'defer';
	}

	/**
	 * Method that returns assets name used to prefix asset handlers.
	 *
	 * @return string
	 */
	public function getAssetsPrefix(): string
	{
		return UtilsConfig::MAIN_PLUGIN_ENQUEUE_ASSETS_PREFIX;
	}

	/**
	 * Method that returns assets version for versioning asset handlers.
	 *
	 * @return string
	 */
	public function getAssetsVersion(): string
	{
		return Helpers::getPluginVersion();
	}
}
