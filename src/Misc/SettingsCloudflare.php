<?php

/**
 * Cloudflare Settings class.
 *
 * @package EightshiftForms\Misc
 */

declare(strict_types=1);

namespace EightshiftForms\Misc;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsCloudflare class.
 */
class SettingsCloudflare implements UtilsSettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_cloudflare';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_cloudflare';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'cloudflare';

	/**
	 * Cloudflare use key.
	 */
	public const SETTINGS_CLOUDFLARE_USE_KEY = 'cloudflare-use';

	/**
	 * Register all the hooks.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_CLOUDFLARE_USE_KEY, self::SETTINGS_CLOUDFLARE_USE_KEY);

		if (!$isUsed) {
			return false;
		}

		return true;
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_CLOUDFLARE_USE_KEY, self::SETTINGS_CLOUDFLARE_USE_KEY)) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			UtilsSettingsOutputHelper::getMiscDisclaimer(),
			[
				'component' => 'intro',
				'introTitle' => \__('Features affected by Cloudflare are:', 'eightshift-forms'),
			],
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-card',
				'layoutContent' => [
					[
						'component' => 'intro',
						'introTitle' => \__('Geolocation', 'eightshift-forms'),
						'introSubtitle' => \sprintf(\__("
							By default geolocation uses IP database to get users location.
							With Cloudflare enabled, geolocation will use Cloudflare headers to get the user location.
							<p>
								Make sure you have enabled <strong>IP Geolocation</strong> in your Cloudflare dashboard.
								You can find more details on how to enable it <a href='%1\$s' rel='noopener noreferrer' target='_blank'>here</a>.
							</p>
						", 'eightshift-forms'), 'https://developers.cloudflare.com/support/network/configuring-ip-geolocation/'),
					],
				],
			],
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-card',
				'layoutContent' => [
					[
						'component' => 'intro',
						'introTitle' => \__('Security', 'eightshift-forms'),
						'introSubtitle' => \__("When using Cloudflare, the user's IP address is masked and replaced with the IP address of the Cloudflare server. This ensures proper functionality.", 'eightshift-forms'),
					],
				],
			],
		];
	}
}
