<?php

/**
 * Moments Settings class.
 *
 * @package EightshiftForms\Integrations\Moments
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Moments;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftForms\General\SettingsGeneral;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftForms\Hooks\FiltersOuputMock;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsMoments class.
 */
class SettingsMoments implements UtilsSettingGlobalInterface, ServiceInterface
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
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_MOMENTS_USE_KEY, self::SETTINGS_MOMENTS_USE_KEY);
		$apiKey = UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiKeyMoments(), self::SETTINGS_MOMENTS_API_KEY_KEY)['value'];
		$url = UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiUrlMoments(), self::SETTINGS_MOMENTS_API_URL_KEY)['value'];

		if (!$isUsed || empty($apiKey) || empty($url)) {
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
		$useEvents = UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_MOMENTS_USE_EVENTS_KEY, self::SETTINGS_MOMENTS_USE_EVENTS_KEY, $formId);

		$formFields = UtilsGeneralHelper::getFormDetails($formId)[UtilsConfig::FD_FIELD_NAMES] ?? [];

		$eventsMap = \array_fill(1, \count($formFields) - 1, 'question');

		$eventsMapValue = UtilsSettingsHelper::getSettingValueGroup(self::SETTINGS_MOMENTS_EVENTS_MAP_KEY, $formId);
		$eventsEmailFieldValue = UtilsSettingsHelper::getSettingValue(self::SETTINGS_MOMENTS_EVENTS_EMAIL_FIELD_KEY, $formId);
		$eventsEventNameValue = UtilsSettingsHelper::getSettingValue(self::SETTINGS_MOMENTS_EVENTS_EVENT_NAME_KEY, $formId);

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
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
								'checkboxesName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_MOMENTS_USE_EVENTS_KEY),
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
									'selectName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_MOMENTS_EVENTS_EMAIL_FIELD_KEY),
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
									'inputName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_MOMENTS_EVENTS_EVENT_NAME_KEY),
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
										'groupName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_MOMENTS_EVENTS_MAP_KEY),
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
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_MOMENTS_USE_KEY, self::SETTINGS_MOMENTS_USE_KEY)) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		$successRedirectUrl = FiltersOuputMock::getSuccessRedirectUrlFilterValue(self::SETTINGS_TYPE_KEY, '');

		$deactivateIntegration = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_MOMENTS_SKIP_INTEGRATION_KEY, self::SETTINGS_MOMENTS_SKIP_INTEGRATION_KEY);

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
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
								'checkboxesName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_MOMENTS_SKIP_INTEGRATION_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => UtilsSettingsOutputHelper::getPartialDeactivatedIntegration('checkboxLabel'),
										'checkboxHelp' => UtilsSettingsOutputHelper::getPartialDeactivatedIntegration('checkboxHelp'),
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
									'introSubtitle' => UtilsSettingsOutputHelper::getPartialDeactivatedIntegration('introSubtitle'),
									'introIsHighlighted' => true,
									'introIsHighlightedImportant' => true,
								],
							] : [
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								UtilsSettingsOutputHelper::getPasswordFieldWithGlobalVariable(
									UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(
										Variables::getApiKeyMoments(),
										self::SETTINGS_MOMENTS_API_KEY_KEY,
										'ES_API_KEY_MOMENTS'
									),
									\__('API key', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								UtilsSettingsOutputHelper::getInputFieldWithGlobalVariable(
									UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(
										Variables::getApiUrlMoments(),
										self::SETTINGS_MOMENTS_API_URL_KEY,
										'ES_API_URL_MOMENTS'
									),
									\__('API url', 'eightshift-forms'),
								),
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								UtilsSettingsOutputHelper::getTestAliConnection(self::SETTINGS_TYPE_KEY),
							]),
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Options', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_TYPE_KEY . '-' . SettingsGeneral::SETTINGS_GLOBAL_REDIRECT_SUCCESS_KEY),
								'inputFieldLabel' => \__('After submit redirect URL', 'eightshift-forms'),
								// translators: %s will be replaced with forms field name and filter output copy.
								'inputFieldHelp' => \sprintf(\__('
									If URL is provided, after a successful submission the user is redirected to the provided URL and the success message will <strong>not</strong> show.
									<br />
									%s', 'eightshift-forms'), $successRedirectUrl['settingsGlobal']),
								'inputType' => 'url',
								'inputIsUrl' => true,
								'inputValue' => $successRedirectUrl['dataGlobal'],
							],
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
