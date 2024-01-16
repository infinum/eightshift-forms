<?php

/**
 * General Settings class.
 *
 * @package EightshiftForms\General
 */

declare(strict_types=1);

namespace EightshiftForms\General;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Hooks\FiltersOuputMock;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsGeneral class.
 */
class SettingsGeneral implements UtilsSettingGlobalInterface, UtilsSettingInterface, ServiceInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_general';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_general';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'general';

	/**
	 * Redirection Success key.
	 */
	public const SETTINGS_GENERAL_REDIRECT_SUCCESS_KEY = 'general-redirection-success';

	/**
	 * Redirection Success key for each integration with type prefix.
	 */
	public const SETTINGS_GLOBAL_REDIRECT_SUCCESS_KEY = 'redirection-success';

	/**
	 * Tracking event name key.
	 */
	public const SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY = 'general-tracking-event-name';

	/**
	 * Tracking additional data key.
	 */
	public const SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_KEY = 'general-tracking-additional-data';
	public const SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_SUCCESS_KEY = 'general-tracking-additional-data-success';
	public const SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_ERROR_KEY = 'general-tracking-additional-data-error';

	public const SETTINGS_GENERAL_SUCCESS_REDIRECT_VARIATION_OPTIONS_KEY = 'general-success-redirect-variation-options';
	public const SETTINGS_GENERAL_SUCCESS_REDIRECT_VARIATION_KEY = 'general-success-redirect-variation';

	/**
	 * Form custom name key.
	 */
	public const SETTINGS_GENERAL_FORM_CUSTOM_NAME_KEY = 'form-custom-name';

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
	 * Get Form settings data array
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsData(string $formId): array
	{
		$specialConstants = \array_map(
			static function ($item, $key) {
				return "<li><code>{$key}</code> - {$item}</li>";
			},
			UtilsGeneralHelper::getSpecialConstants('tracking'),
			\array_keys(UtilsGeneralHelper::getSpecialConstants('tracking'))
		);

		$formDetails = UtilsGeneralHelper::getFormDetails($formId);
		$formType = $formDetails[UtilsConfig::FD_TYPE] ?? '';

		$successRedirectUrl = FiltersOuputMock::getSuccessRedirectUrlFilterValue($formType, $formId);
		$successRedirectVariation = FiltersOuputMock::getSuccessRedirectVariationFilterValue($formType, $formId);
		$successRedirectVariationOptions = FiltersOuputMock::getSuccessRedirectVariationOptionsFilterValue();
		$trackingEventName = FiltersOuputMock::getTrackingEventNameFilterValue($formType, $formId);
		$trackingAdditionalData = FiltersOuputMock::getTrackingAditionalDataFilterValue($formType, $formId);

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('After form submission', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_GENERAL_REDIRECT_SUCCESS_KEY),
								'inputFieldLabel' => \__('Redirect to URL', 'eightshift-forms'),
								// translators: %s will be replaced with forms field name and filter output copy.
								'inputFieldHelp' => \sprintf(\__('
									After a successful submission, the user will be redirected to the provided URL and the success message will <b>not</b> be shown.<br /><br />
									If you need to include some of the submitted data, use template tags (e.g. <code>{field-name}</code>).<br />
									<details class="is-filter-applied">
										<summary>Available tags</summary>
										<ul>
											%1$s
										</ul>

										<br />
										Tag missing? Make sure its field has a <b>Name</b> set!
									</details>
									%2$s', 'eightshift-forms'), UtilsSettingsOutputHelper::getPartialFormFieldNames($formDetails[UtilsConfig::FD_FIELD_NAMES_TAGS]), $successRedirectUrl['settingsLocal']),
								'inputType' => 'url',
								'inputIsUrl' => true,
								'inputIsDisabled' => $successRedirectUrl['filterUsed'],
								'inputValue' => $successRedirectUrl['dataLocal'],
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'select',
								'selectFieldLabel' => \__('Redirect variation', 'eightshift-forms'),
								'selectName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_GENERAL_SUCCESS_REDIRECT_VARIATION_KEY),
								'selectPlaceholder' => \__('Pick an option', 'eightshift-forms'),
								'selectIsDisabled' => $successRedirectVariation['filterUsed'],
								// translators: %s will be replaced with forms field name and filter output copy.
								'selectFieldHelp' => \sprintf(\__('
									Variation value will be appended to the redirect URL as a parameter to allow you to privide different version of the "Thank you" page.
									<br />
									%s', 'eightshift-forms'), $successRedirectVariation['settings']),
								'selectContent' => \array_values(
									\array_map(
										function ($selectOption) use ($successRedirectVariation) {
											return [
												'component' => 'select-option',
												'selectOptionLabel' => $selectOption[1],
												'selectOptionValue' => $selectOption[0],
												'selectOptionIsSelected' => $selectOption[0] === $successRedirectVariation['data'],
											];
										},
										$successRedirectVariationOptions['data']
									)
								),
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Tracking', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY),
								'inputFieldLabel' => \__('Event name', 'eightshift-forms'),
								// translators: %s will be replaced with th filter output copy.
								'inputFieldHelp' => UtilsGeneralHelper::minifyString(\sprintf(\__('
									If blank, the event is not sent to the tracking platform.%s', 'eightshift-forms'), $trackingEventName['settings'])),
								'inputType' => 'text',
								'inputIsDisabled' => $trackingEventName['filterUsed'],
								'inputValue' => $trackingEventName['data'],
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'textarea',
								'textareaName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_KEY),
								'textareaIsMonospace' => true,
								'textareaSaveAsJson' => true,
								'textareaFieldLabel' => \__('Additional parameters', 'eightshift-forms'),
								// translators: %s will be list example keys.
								'textareaFieldHelp' => UtilsGeneralHelper::minifyString(\sprintf(\__("
									These parameters are always sent to the tracking platform.<br /><br />
									Provide one key-value pair per line, following this format: %1\$s
									<br />
									%2\$s", 'eightshift-forms'), '<code>keyName : keyValue</code>', $trackingAdditionalData['settings']['general'] ?? '')),
								'textareaValue' => UtilsSettingsHelper::getSettingValueAsJson(self::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_KEY, $formId, 2),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'textarea',
								'textareaName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_SUCCESS_KEY),
								'textareaIsMonospace' => true,
								'textareaSaveAsJson' => true,
								'textareaFieldLabel' => \__('Additional parameters on successful submit', 'eightshift-forms'),
								// translators: %s will be list example keys.
								'textareaFieldHelp' => UtilsGeneralHelper::minifyString(\sprintf(\__("
									These parameters are sent to the tracking platform when a form is submitted successfully.<br /><br />
									Provide one key-value pair per line, following this format: %1\$s
									<br />
									%2\$s
									", 'eightshift-forms'), '<code>keyName : keyValue</code>', $trackingAdditionalData['settings']['success'] ?? '')),
								'textareaValue' => UtilsSettingsHelper::getSettingValueAsJson(self::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_SUCCESS_KEY, $formId, 2),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'textarea',
								'textareaName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_ERROR_KEY),
								'textareaIsMonospace' => true,
								'textareaSaveAsJson' => true,
								'textareaFieldLabel' => \__('Additional parameters on error', 'eightshift-forms'),
								// translators: %s will be list example keys.
								'textareaFieldHelp' => UtilsGeneralHelper::minifyString(\sprintf(\__("
									These parameters are sent to the tracking platform when a form submission fails.<br /><br />
									Provide one key-value pair per line, following this format: %1\$s
									<br /><br />
									To provide additional data, use these constants:
									<ul>
									%2\$s
									</ul>
									%3\$s
									", 'eightshift-forms'), '<code>keyName : keyValue</code>', \implode('', $specialConstants), $trackingAdditionalData['settings']['error'] ?? '')),
								'textareaValue' => UtilsSettingsHelper::getSettingValueAsJson(self::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_ERROR_KEY, $formId, 2),
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Identification', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_GENERAL_FORM_CUSTOM_NAME_KEY),
								'inputId' => UtilsSettingsHelper::getSettingName(self::SETTINGS_GENERAL_FORM_CUSTOM_NAME_KEY),
								'inputFieldLabel' => \__('Form custom name', 'eightshift-forms'),
								'inputFieldHelp' => \__('Target a form (or a set of forms) and apply changes through filters, in code.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputValue' => UtilsSettingsHelper::getSettingValue(self::SETTINGS_GENERAL_FORM_CUSTOM_NAME_KEY, $formId),
							]
						],
					],
				]
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
		$successRedirectVariationOptions = FiltersOuputMock::getSuccessRedirectVariationOptionsFilterValue();

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Submit', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'textarea',
								'textareaName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_GENERAL_SUCCESS_REDIRECT_VARIATION_OPTIONS_KEY),
								'textareaIsMonospace' => true,
								'selectIsDisabled' => $successRedirectVariationOptions['filterUsed'],
								'textareaSaveAsJson' => true,
								'textareaFieldLabel' => \__('After form submission redirect variants', 'eightshift-forms'),
								// translators: %s will be replaced with local validation patterns.
								'textareaFieldHelp' => UtilsGeneralHelper::minifyString(\sprintf(\__("
									These entries will populate the dropdown in the \"Thank you page\" options so different styles can be selected based on the use case.<br /><br />
									Provide one key-value pair per line, in this format: %1\$s
									%2\$s", 'eightshift-forms'), '<code>slug : label</code>', $successRedirectVariationOptions['settings'])),
								'textareaValue' => UtilsSettingsHelper::getOptionValueAsJson(self::SETTINGS_GENERAL_SUCCESS_REDIRECT_VARIATION_OPTIONS_KEY, 2),
							],
						],
					],
				],
			],
		];
	}
}
