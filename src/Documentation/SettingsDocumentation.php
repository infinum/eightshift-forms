<?php

/**
 * Documentation Settings class.
 *
 * @package EightshiftForms\Documentation
 */

declare(strict_types=1);

namespace EightshiftForms\Documentation;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsDocumentation class.
 */
class SettingsDocumentation implements UtilsSettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_documentation';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'documentation';

	/**
	 * Register all the hooks.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-card',
				'layoutContent' => [
					[
						'component' => 'intro',
						'introTitle' => \__('What is Eightshift Forms?', 'eightshift-forms'),
						// translators: %s will be replaced with links.
						'introSubtitle' => \sprintf(\__("
						Eightshift forms plugin is a complete form builder tool that utilizes modern Block editor features with multiple third-party integrations to boost your project to another level.<br /><br />
							<span>Documentation for all features and hooks can be found <a href='%s' target='_blank' rel='noopener noreferrer'>here</a>.</span>", 'eightshift-forms'), 'https://eightshift.com/forms/welcome'),
					],
				],
			],
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-card',
				'layoutContent' => [
					[
						'component' => 'intro',
						'introTitle' => \__('Credits', 'eightshift-forms'),
						// translators: %s will be replaced with links.
						'introSubtitle' => \sprintf(\__("
							<span>Made by the WordPress team at <a href='%1\$s' target='_blank' rel='noopener noreferrer'>Infinum</a>, using the <a href='%2\$s' target='_blank' rel='noopener noreferrer'>Eightshift development kit</a>.</span>
							<span>If you have any questions or problems, please open an <a href='%3\$s' target='_blank' rel='noopener noreferrer'>issue on GitHub</a>, and we will do our best to give you a timely answer.</span>", 'eightshift-forms'), 'https://infinum.com/', 'https://eightshift.com/', 'https://github.com/infinum/eightshift-forms/issues'),
					],
				],
			],
		];
	}
}
