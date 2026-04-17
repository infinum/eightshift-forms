<?php

/**
 * The Theme/Frontend Enqueue specific functionality - Friendly Captcha.
 *
 * @package EightshiftForms\Enqueue\FriendlyCaptcha
 */

declare(strict_types=1);

namespace EightshiftForms\Enqueue\FriendlyCaptcha;

use EightshiftForms\FriendlyCaptcha\SettingsFriendlyCaptcha;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\HooksHelpers;
use EightshiftFormsVendor\EightshiftLibs\Enqueue\Theme\AbstractEnqueueTheme;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

/**
 * Class EnqueueFriendlyCaptcha
 */
class EnqueueFriendlyCaptcha extends AbstractEnqueueTheme
{
	/**
	 * Friendly Captcha enqueue handle.
	 *
	 * @var string
	 */
	public const FRIENDLY_CAPTCHA_ENQUEUE_HANDLE = 'friendly-captcha';

	/**
	 * Register all the hooks.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('wp_enqueue_scripts', [$this, 'enqueueScriptsFriendlyCaptcha']);
	}

	/**
	 * Get frontend script dependencies.
	 *
	 * @return array<int, string> List of all the script dependencies.
	 */
	protected function getFrontendScriptDependencies(): array
	{
		if (!\apply_filters(SettingsFriendlyCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			return [];
		}

		$scriptsDependency = HooksHelpers::getFilterName(['scripts', 'dependency', 'friendlyCaptcha']);
		$scriptsDependencyOutput = [];

		if (\has_filter($scriptsDependency)) {
			$scriptsDependencyOutput = \apply_filters($scriptsDependency, []);
		}

		return $scriptsDependencyOutput;
	}

	/**
	 * Method that returns frontend script for Friendly Captcha if settings are correct.
	 *
	 * @return void
	 */
	public function enqueueScriptsFriendlyCaptcha(): void
	{
		// Check if Friendly Captcha data is set and valid.
		$isSettingsGlobalValid = \apply_filters(SettingsFriendlyCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false);

		// Bailout if settings are not ok.
		if (!$isSettingsGlobalValid) {
			return;
		}

		$handle = "{$this->getAssetsPrefix()}-" . self::FRIENDLY_CAPTCHA_ENQUEUE_HANDLE;

		\wp_register_script(
			$handle,
			'https://cdn.jsdelivr.net/npm/@friendlycaptcha/sdk@0.2.0/site.min.js',
			$this->getFrontendScriptDependencies(),
			$this->getAssetsVersion(),
			\is_wp_version_compatible('6.3') ? $this->scriptArgs() : $this->scriptInFooter()
		);
		\wp_enqueue_script($handle);
	}

	/**
	 * Load script 'defer' or 'async'.
	 *
	 * @return string Whether to enqueue the script normally, with defer or async.
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
		return Config::MAIN_PLUGIN_ENQUEUE_ASSETS_PREFIX;
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
