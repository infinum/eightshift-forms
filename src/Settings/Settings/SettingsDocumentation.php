<?php

/**
 * Documentation Settings class.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Helpers\Helper;
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
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
	}

	/**
	 * Get Form settings data array
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
		$links = implode('', array_values(array_filter(array_map(
			static function($item, $key) {
				if ($item['type'] === Settings::SETTINGS_SIEDBAR_TYPE_INTEGRATION) {
					$title = Filters::getSettingsLabels($key);
					$detail = Filters::getSettingsLabels($key, 'detail');
					$url = Filters::getSettingsLabels($key, 'externalLink');

					return sprintf("<li><a href='%s' target='_blank' rel='noopener noreferrer'>{$title}</a> - {$detail}</li>", $url);
				}
			},
			Filters::ALL,
			array_keys(Filters::ALL)
		))));


		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'divider',
			],
			[
				'component' => 'intro',
				'introTitle' => __('What is Eightshift forms?', 'eightshift-forms'),
				'introSubtitle' => __('Eightshift forms plugins is a complete form builder tool that utilizes modern Block editor features with multiple third-party integrations to boost your project to another level.', 'eightshift-forms'),
			],
			[
				'component' => 'divider',
			],
			[
				'component' => 'intro',
				'introTitle' => __('What integrations are available?', 'eightshift-forms'),
				'introSubtitle' => __("
					We implemented multiple modern third-party integrations, and we will keep adding new ones in the future. Here you can find all available integrations that we support:<br /><br />
					<ul>
						{$links}
					</ul>
				", 'eightshift-forms'),
			],
			[
				'component' => 'divider',
			],
			[
				'component' => 'intro',
				'introTitle' => __('What separates you from other plugins?', 'eightshift-forms'),
				'introSubtitle' => __('
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
				'introTitle' => __('Where can I found developers documentation?', 'eightshift-forms'),
				'introSubtitle' => sprintf(__("We provided a complete documentation for all our features and hooks you can use on this <a href='%s' target='_blank' rel='noopener noreferrer'>link</a>.", 'eightshift-forms'), 'https://github.com/infinum/eightshift-forms/tree/develop/src/Hooks'),
			],
			[
				'component' => 'divider',
			],
			[
				'component' => 'intro',
				'introTitle' => __('Credits', 'eightshift-forms'),
				'introSubtitle' => sprintf(__("
					WordPress team @<a href='%s' target='_blank' rel='noopener noreferrer'>Infinum</a> created this plugin using our <a href='%s' target='_blank' rel='noopener noreferrer'>Eightshift boilerplate</a> as a platform.<br />
					If you have any questions or problems, please open an <a href='%s' target='_blank' rel='noopener noreferrer'>issue on GitHub</a>, and we will do our best to give you a timely answer.
				", 'eightshift-forms'),
				'https://infinum.com/',
				'https://eightshift.com/',
				'https://github.com/infinum/eightshift-forms/issues'
			),
			],
			[
				'component' => 'divider',
			],
		];
	}
}
