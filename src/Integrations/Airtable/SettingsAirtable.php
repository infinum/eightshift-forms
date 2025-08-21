<?php

/**
 * Airtable Settings class.
 *
 * @package EightshiftForms\Integrations\Airtable
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Airtable;

use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Integrations\AbstractSettingsIntegrations;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsAirtable class.
 */
class SettingsAirtable extends AbstractSettingsIntegrations implements SettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_airtable';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_airtable';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'airtable';

	/**
	 * Airtable Use key.
	 */
	public const SETTINGS_AIRTABLE_USE_KEY = 'airtable-use';

	/**
	 * API Key.
	 */
	public const SETTINGS_AIRTABLE_API_KEY_KEY = 'airtable-api-key';

	/**
	 * Skip integration.
	 */
	public const SETTINGS_AIRTABLE_SKIP_INTEGRATION_KEY = 'airtable-skip-integration';

	/**
	 * Instance variable for Fallback settings.
	 *
	 * @var SettingsFallbackDataInterface
	 */
	protected $settingsFallback;

	/**
	 * Create a new instance.
	 *
	 * @param SettingsFallbackDataInterface $settingsFallback Inject Fallback which holds Fallback settings data.
	 */
	public function __construct(SettingsFallbackDataInterface $settingsFallback)
	{
		$this->settingsFallback = $settingsFallback;
	}
	/**
	 * Register all the hooks
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
		$isUsed = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_AIRTABLE_USE_KEY, self::SETTINGS_AIRTABLE_USE_KEY);
		$apiKey = (bool) SettingsHelpers::getOptionWithConstant(Variables::getApiKeyAirtable(), self::SETTINGS_AIRTABLE_API_KEY_KEY);

		if (!$isUsed || !$apiKey) {
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
		// Bailout if feature is not active.
		if (!SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_AIRTABLE_USE_KEY, self::SETTINGS_AIRTABLE_USE_KEY)) {
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		$deactivateIntegration = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_AIRTABLE_SKIP_INTEGRATION_KEY, self::SETTINGS_AIRTABLE_SKIP_INTEGRATION_KEY);

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('General', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_AIRTABLE_SKIP_INTEGRATION_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => SettingsOutputHelpers::getPartialDeactivatedIntegration('checkboxLabel'),
										'checkboxHelp' => SettingsOutputHelpers::getPartialDeactivatedIntegration('checkboxHelp'),
										'checkboxIsChecked' => $deactivateIntegration,
										'checkboxValue' => self::SETTINGS_AIRTABLE_SKIP_INTEGRATION_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									]
								]
							],
							...($deactivateIntegration ? [
								[
									'component' => 'intro',
									'introSubtitle' => SettingsOutputHelpers::getPartialDeactivatedIntegration('introSubtitle'),
									'introIsHighlighted' => true,
									'introIsHighlightedImportant' => true,
								],
							] : [
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								SettingsOutputHelpers::getPasswordFieldWithGlobalVariable(
									Variables::getApiKeyAirtable(),
									self::SETTINGS_AIRTABLE_API_KEY_KEY,
									'ES_API_KEY_AIRTABLE',
									\__('API key', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								SettingsOutputHelpers::getTestApiConnection(self::SETTINGS_TYPE_KEY),
							]),
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Options', 'eightshift-forms'),
						'tabContent' => [
							...$this->getGlobalGeneralSettings(self::SETTINGS_TYPE_KEY),
						],
					],
					$this->settingsFallback->getOutputGlobalFallback(SettingsAirtable::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									\__('Log in to your Airtable Account.', 'eightshift-forms'),
									// translators: %s will be replaced with the link.
									\sprintf(\__('Go to the <a target="_blank" rel="noopener noreferrer" href="%s">Developers Hub</a>.', 'eightshift-forms'), 'https://airtable.com/create/tokens/new'),
									\__('Create a new Personal access token with the scopes <strong>"data.records:write"</strong>, <strong>"data.records:read"</strong> and <strong>"schema.bases:read"</strong>.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}
}
