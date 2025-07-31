<?php

/**
 * Moments Settings class.
 *
 * @package EightshiftForms\Integrations\Moments
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Moments;

use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Integrations\AbstractSettingsIntegrations;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsMoments class.
 */
class SettingsMoments extends AbstractSettingsIntegrations implements SettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_moments';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_moments';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_moments';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'moments';

	/**
	 * Moments Use key.
	 */
	public const SETTINGS_MOMENTS_USE_KEY = 'moments-use';

	/**
	 * API Url.
	 */
	public const SETTINGS_MOMENTS_API_URL_KEY = 'moments-api-url';

	/**
	 * API Key.
	 */
	public const SETTINGS_MOMENTS_API_KEY_KEY = 'moments-api-key';

	/**
	 * Skip integration.
	 */
	public const SETTINGS_MOMENTS_SKIP_INTEGRATION_KEY = 'moments-skip-integration';

	/**
	 * Moments Use Events key.
	 */
	public const SETTINGS_MOMENTS_USE_EVENTS_KEY = 'moments-use-events';

	/**
	 * Moments Events event name key.
	 */
	public const SETTINGS_MOMENTS_EVENTS_EVENT_NAME_KEY = 'moments-events-event-name';

	/**
	 * Moments Events map key.
	 */
	public const SETTINGS_MOMENTS_EVENTS_MAP_KEY = 'moments-events-map';

	/**
	 * Moments Events email field key.
	 */
	public const SETTINGS_MOMENTS_EVENTS_EMAIL_FIELD_KEY = 'moments-events-email-field';

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
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
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
		$isUsed = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_MOMENTS_USE_KEY, self::SETTINGS_MOMENTS_USE_KEY);
		$apiKey = (bool) SettingsHelpers::getOptionWithConstant(Variables::getApiKeyMoments(), self::SETTINGS_MOMENTS_API_KEY_KEY);
		$url = (bool) SettingsHelpers::getOptionWithConstant(Variables::getApiUrlMoments(), self::SETTINGS_MOMENTS_API_URL_KEY);

		if (!$isUsed || !$apiKey || !$url) {
			return false;
		}

		return true;
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
		$useEvents = SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_MOMENTS_USE_EVENTS_KEY, self::SETTINGS_MOMENTS_USE_EVENTS_KEY, $formId);

		$formFields = GeneralHelpers::getFormDetails($formId)[Config::FD_FIELD_NAMES] ?? [];

		$eventsMap = \array_fill(1, \count($formFields) - 1, 'question');

		$eventsMapValue = SettingsHelpers::getSettingValueGroup(self::SETTINGS_MOMENTS_EVENTS_MAP_KEY, $formId);
		$eventsEmailFieldValue = SettingsHelpers::getSettingValue(self::SETTINGS_MOMENTS_EVENTS_EMAIL_FIELD_KEY, $formId);
		$eventsEventNameValue = SettingsHelpers::getSettingValue(self::SETTINGS_MOMENTS_EVENTS_EVENT_NAME_KEY, $formId);

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
								'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_MOMENTS_USE_EVENTS_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Use Events', 'eightshift-forms'),
										'checkboxHelp' => \__('Send events to Moments', 'eightshift-forms'),
										'checkboxIsChecked' => $useEvents,
										'checkboxValue' => self::SETTINGS_MOMENTS_USE_EVENTS_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									]
								]
							],
							...($useEvents ? [
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								[
									'component' => 'select',
									'selectName' => SettingsHelpers::getSettingName(self::SETTINGS_MOMENTS_EVENTS_EMAIL_FIELD_KEY),
									'selectFieldHelp' => \__('You must select what field is used as an e-mail.', 'eightshift-forms'),
									'selectFieldLabel' => \__('E-mail field', 'eightshift-forms'),
									'selectPlaceholder' => \__('Select email field', 'eightshift-forms'),
									'selectIsRequired' => true,
									'selectContent' => \array_map(
										static function ($option) use ($eventsEmailFieldValue) {
											return [
												'component' => 'select-option',
												'selectOptionLabel' => $option,
												'selectOptionValue' => $option,
												'selectOptionIsSelected' => $eventsEmailFieldValue === $option,
											];
										},
										$formFields
									),
								],
								[
									'component' => 'input',
									'inputName' => SettingsHelpers::getSettingName(self::SETTINGS_MOMENTS_EVENTS_EVENT_NAME_KEY),
									'inputFieldLabel' => \__('Event name', 'eightshift-forms'),
									'inputFieldHelp' => \__('Set event name used in the Moments integrations.', 'eightshift-forms'),
									'inputType' => 'text',
									'inputIsRequired' => true,
									'inputValue' => $eventsEventNameValue,
								],
								...(($eventsEmailFieldValue && $eventsEventNameValue) ? [
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => true,
									],
									[
										'component' => 'group',
										'groupName' => SettingsHelpers::getSettingName(self::SETTINGS_MOMENTS_EVENTS_MAP_KEY),
										'groupSaveOneField' => true,
										'groupStyle' => 'default-listing',
										'groupContent' => [
											[
												'component' => 'field',
												'fieldLabel' => '<b>' . \__('Moments event key', 'eightshift-forms') . '</b>',
												'fieldContent' => '<b>' . \__('Form fields', 'eightshift-forms') . '</b>',
												'fieldBeforeContent' => '&emsp;', // "Em space" to pad it out a bit.
												'fieldIsFiftyFiftyHorizontal' => true,
											],
											...\array_map(
												static function ($item, $index) use ($eventsMapValue, $formFields) {
													$indexName = \str_pad((string) $index, 2, '0', \STR_PAD_LEFT);
													$name = "{$item}{$indexName}";

													$selectedValue = $eventsMapValue[$name] ?? '';
													return [
														'component' => 'select',
														'selectName' => "{$item}{$indexName}",
														'selectFieldLabel' => '<code>' . $name . '</code>',
														'selectFieldBeforeContent' => '&rarr;',
														'selectIsRequired' => true,
														'selectFieldIsFiftyFiftyHorizontal' => true,
														'selectPlaceholder' => \__('Select option', 'eightshift-forms'),
														'selectContent' => \array_map(
															static function ($option) use ($selectedValue) {
																return [
																	'component' => 'select-option',
																	'selectOptionLabel' => $option,
																	'selectOptionValue' => $option,
																	'selectOptionIsSelected' => $selectedValue === $option,
																];
															},
															$formFields
														),
													];
												},
												$eventsMap,
												\array_keys($eventsMap)
											),
										],
									],
								] : []),
							] : []),
						],
					],
				],
			],
		];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		// Bailout if feature is not active.
		if (!SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_MOMENTS_USE_KEY, self::SETTINGS_MOMENTS_USE_KEY)) {
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		$deactivateIntegration = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_MOMENTS_SKIP_INTEGRATION_KEY, self::SETTINGS_MOMENTS_SKIP_INTEGRATION_KEY);

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
								'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_MOMENTS_SKIP_INTEGRATION_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => SettingsOutputHelpers::getPartialDeactivatedIntegration('checkboxLabel'),
										'checkboxHelp' => SettingsOutputHelpers::getPartialDeactivatedIntegration('checkboxHelp'),
										'checkboxIsChecked' => $deactivateIntegration,
										'checkboxValue' => self::SETTINGS_MOMENTS_SKIP_INTEGRATION_KEY,
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
									Variables::getApiKeyMoments(),
									self::SETTINGS_MOMENTS_API_KEY_KEY,
									'ES_API_KEY_MOMENTS',
									\__('API key', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								SettingsOutputHelpers::getInputFieldWithGlobalVariable(
									Variables::getApiUrlMoments(),
									self::SETTINGS_MOMENTS_API_URL_KEY,
									'ES_API_URL_MOMENTS',
									\__('API url', 'eightshift-forms'),
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
					$this->settingsFallback->getOutputGlobalFallback(SettingsMoments::SETTINGS_TYPE_KEY),
					[
						'component' => 'tab',
						'tabLabel' => \__('Help', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'steps',
								'stepsTitle' => \__('How to get the API key?', 'eightshift-forms'),
								'stepsContent' => [
									\__('Log in to your Moments Account.', 'eightshift-forms'),
									// translators: %s will be replaced with the link.
									\sprintf(\__('Go to <a target="_blank" rel="noopener noreferrer" href="%s">Developer API</a>.', 'eightshift-forms'), 'https://www.infobip.com/docs/api/'),
									\__('Copy the API key into the field under the API tab or use the global constant.', 'eightshift-forms'),
									\__('Copy the Base Url key into the field under the API tab or use the global constant.', 'eightshift-forms'),
								],
							],
						],
					],
				],
			],
		];
	}
}
