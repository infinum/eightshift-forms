<?php

/**
 * Documentation Settings class.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsDocumentation class.
 */
class SettingsDocumentation implements SettingGlobalInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

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
		$logo = '<a href="https://infinum.com" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" style="width: 12.5rem" width="980" height="87" viewBox="0 0 980 87" fill="none"><path d="M226.53 81.15V5.63h25.91v75.52h-25.91ZM389.4 5.63v75.52h-28.07L297.72 32.7v48.45h-25.91V5.52h31l60.71 46.3V5.63h25.88ZM510 26.17h-73.2V40h70.54v19.41h-70.5v21.74h-25.9V5.63H510v20.54Zm16.15 54.98V5.63h25.9v75.52h-25.9ZM691.17 5.63v75.52h-28.06L599.49 32.7v48.45h-25.91V5.52h31l60.71 46.3V5.63h25.88ZM737 44.6c0 5.54 1.597 9.84 4.79 12.9 3.194 3.06 8.897 4.587 17.11 4.58h13.72c14.74 0 22.11-5.827 22.11-17.48v-39h25.91v39.1c0 11.06-3.456 20.077-10.37 27.05-6.913 6.973-17.486 10.457-31.72 10.45h-25.71a58.382 58.382 0 0 1-19.35-2.9 37.85 37.85 0 0 1-15.3-10.61c-4.713-5.14-7.066-13.14-7.06-24V5.63H737V44.6Zm129.44 36.55h-25.91V5.63h37.34l32.23 49.5 31.38-49.5h37.87v75.52h-25.86V30.22l-32.28 50.93h-21.65l-33.12-50.82v50.82ZM132.26 0c-19 0-32.54 13.53-44.46 26C75.87 13.53 62.29 0 43.33 0a43.34 43.34 0 0 0 0 86.67c19 0 32.56-13.53 44.48-26 11.93 12.44 25.52 26 44.45 26a43.342 43.342 0 0 0 40.636-26.598 43.346 43.346 0 0 0-9.565-47.615A43.347 43.347 0 0 0 132.26 0Zm19 43.33a19 19 0 0 1-19 19c-8.94 0-17.59-8.76-27.43-19 9.52-9.94 18.54-19 27.43-19a18.996 18.996 0 0 1 17.581 11.722 18.994 18.994 0 0 1 1.449 7.278h-.03Zm-80.52 0c-9.14 9.56-18.54 19-27.44 19a19 19 0 0 1 0-38c8.93-.02 17.54 8.67 27.47 19.01l-.03-.01Z" fill="var(--global-colors-esf-gray-500)"/></svg></a>';

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
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
							<span>If you have any questions or problems, please open an <a href='%3\$s' target='_blank' rel='noopener noreferrer'>issue on GitHub</a>, and we will do our best to give you a timely answer.</span>&mdash;&mdash;%4\$s", 'eightshift-forms'), 'https://infinum.com/', 'https://eightshift.com/', 'https://github.com/infinum/eightshift-forms/issues', $logo),
					],
				],
			],
		];
	}
}
