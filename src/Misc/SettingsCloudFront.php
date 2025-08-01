<?php

/**
 * CloudFront Settings class.
 *
 * @package EightshiftForms\Misc
 */

declare(strict_types=1);

namespace EightshiftForms\Misc;

use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsCloudFront class.
 */
class SettingsCloudFront implements SettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_cloudfront';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_cloudfront';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'cloudfront';

	/**
	 * CloudFront use key.
	 */
	public const SETTINGS_CLOUDFRONT_USE_KEY = 'cloudfront-use';

	/**
	 * Register all the hooks.
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
		$isUsed = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_CLOUDFRONT_USE_KEY, self::SETTINGS_CLOUDFRONT_USE_KEY);

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
		if (!SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_CLOUDFRONT_USE_KEY, self::SETTINGS_CLOUDFRONT_USE_KEY)) {
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			SettingsOutputHelpers::getMiscDisclaimer(\__('CloudFront', 'eightshift-forms')),
			[
				'component' => 'intro',
				'introTitle' => \__('Features affected by CloudFront are:', 'eightshift-forms'),
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
							With CloudFront enabled, geolocation will use CloudFront headers to get the user location.
							<p>
								Make sure you have enabled <strong>IP Geolocation</strong> in your CloudFront dashboard.
								You can find more details on how to enable it <a href='%1\$s' rel='noopener noreferrer' target='_blank'>here</a>.
							</p>
						", 'eightshift-forms'), 'https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/adding-cloudfront-headers.html'),
					],
				],
			],
		];
	}
}
