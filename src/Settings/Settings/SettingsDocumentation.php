<?php

/**
 * Documentation Settings class.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsDocumentation class.
 */
class SettingsDocumentation implements SettingInterface, ServiceInterface
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
	 * Get Form settings data array.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsData(string $formId): array
	{
		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
		];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		$links = \implode('', \array_values(\array_filter(\array_map(
			static function ($item, $key) {
				if ($item['type'] === Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION) {
					$title = Filters::getSettingsLabels($key);
					$detail = Filters::getSettingsLabels($key, 'detail');
					$url = Filters::getSettingsLabels($key, 'externalLink');

					// translators: %s will be replaced with the link.
					return \sprintf("<li><a href='%s' target='_blank' rel='noopener noreferrer'>{$title}</a> - {$detail}</li>", $url);
				}
			},
			Filters::ALL,
			\array_keys(Filters::ALL)
		))));

		$logo = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 979.35 86.67"><defs><style>.cls-1{fill:#d82828;}</style></defs><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path d="M226.53,81.15V5.63h25.91V81.15Z"/><path d="M389.4,5.63V81.15H361.33L297.72,32.7V81.15H271.81V5.52h31l60.71,46.3V5.63Z"/><path d="M510,26.17h-73.2V40h70.54V59.41H436.84V81.15h-25.9V5.63H510Z"/><path d="M526.15,81.15V5.63h25.9V81.15Z"/><path d="M691.17,5.63V81.15H663.11L599.49,32.7V81.15H573.58V5.52h31l60.71,46.3V5.63Z"/><path d="M737,44.6q0,8.31,4.79,12.9t17.11,4.58h13.72q22.11,0,22.11-17.48v-39h25.91V44.7q0,16.59-10.37,27.05T778.55,82.2H752.84a58.38,58.38,0,0,1-19.35-2.9,37.85,37.85,0,0,1-15.3-10.61q-7.07-7.71-7.06-24V5.63H737Z"/><path d="M866.44,81.15H840.53V5.63h37.34l32.23,49.5,31.38-49.5h37.87V81.15H953.49V30.22L921.21,81.15H899.56L866.44,30.33Z"/><path class="cls-1" d="M132.26,0c-19,0-32.54,13.53-44.46,26C75.87,13.53,62.29,0,43.33,0a43.34,43.34,0,0,0,0,86.67c19,0,32.56-13.53,44.48-26,11.93,12.44,25.52,26,44.45,26a43.34,43.34,0,1,0,0-86.67Zm19,43.33a19,19,0,0,1-19,19c-8.94,0-17.59-8.76-27.43-19,9.52-9.94,18.54-19,27.43-19A19,19,0,0,1,151.29,43.33Zm-80.52,0c-9.14,9.56-18.54,19-27.44,19a19,19,0,0,1,0-38C52.23,24.31,60.84,33,70.77,43.34Z"/></g></g></svg>';

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'divider',
			],
			[
				'component' => 'intro',
				'introTitle' => \__('What is Eightshift Forms?', 'eightshift-forms'),
				'introSubtitle' => \__('Eightshift forms plugin is a complete form builder tool that utilizes modern Block editor features with multiple third-party integrations to boost your project to another level.', 'eightshift-forms'),
			],
			[
				'component' => 'divider',
			],
			[
				'component' => 'intro',
				'introTitle' => \__('What integrations are available?', 'eightshift-forms'),
				// translators: %s will be replaced with links list items.
				'introSubtitle' => \sprintf(\__("
					We implemented multiple modern third-party integrations, and we will keep adding new ones in the future. Here you can find all available integrations that we support:<br /><br />
					<ul>
						%s
					</ul>
				", 'eightshift-forms'), \wp_kses_post($links)),
			],
			[
				'component' => 'divider',
			],
			[
				'component' => 'intro',
				'introTitle' => \__('What separates you from other plugins?', 'eightshift-forms'),
				'introSubtitle' => \__('
					While using multiple other plugins for form building, we found out that they are all good, but none of them fully allows you to customize the forms to your needs.<br /><br />
					Our goal is to give the users and editors a smooth UX and UI experience but also provide developers with a full range of customizations and the ability to change anything with our hooks.
				', 'eightshift-forms'),
			],
			[
				'component' => 'divider',
			],
			[
				'component' => 'intro',
				'introIsHighlighted' => true,
				'introTitle' => \__('Where can I find developer documentation?', 'eightshift-forms'),
				// translators: %s will be replaced with the link.
				'introSubtitle' => \sprintf(\__("We provide complete documentation for all features and hooks you can use on this <a href='%s' target='_blank' rel='noopener noreferrer'>link</a>.", 'eightshift-forms'), 'https://github.com/infinum/eightshift-forms/tree/develop/src/Hooks'),
			],
			[
				'component' => 'divider',
			],
			[
				'component' => 'intro',
				'introTitle' => \__('Credits', 'eightshift-forms'),
				// translators: %s will be replaced with links.
				'introSubtitle' => \sprintf(\__("
					WordPress team @<a href='%1\$s' target='_blank' rel='noopener noreferrer'>Infinum</a> created this plugin using <a href='%2\$s' target='_blank' rel='noopener noreferrer'>Eightshift Development Kit</a>.<br />
					If you have any questions or problems, please open an <a href='%3\$s' target='_blank' rel='noopener noreferrer'>issue on GitHub</a>, and we will do our best to give you a timely answer. <br /> <br />%4\$s", 'eightshift-forms'), 'https://infinum.com/', 'https://eightshift.com/', 'https://github.com/infinum/eightshift-forms/issues', $logo),
			],
			[
				'component' => 'divider',
			],
		];
	}
}
