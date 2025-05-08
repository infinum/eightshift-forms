<?php

/**
 * General Settings class.
 *
 * @package EightshiftForms\General
 */

declare(strict_types=1);

namespace EightshiftForms\General;

use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Settings\SettingInterface;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Hooks\FiltersOuputMock;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsGeneral class.
 */
class SettingsGeneral implements SettingInterface, ServiceInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_general';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'general';

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

	/**
	 * Variation key.
	 */
	public const SETTINGS_VARIATION_KEY = 'variation';

	/**
	 * Variation should append on global key.
	 */
	public const SETTINGS_VARIATION_SHOULD_APPEND_ON_GLOBAL_KEY = 'variation-should-append-on-global';

	/**
	 * Form custom name key.
	 */
	public const SETTINGS_FORM_CUSTOM_NAME_KEY = 'form-custom-name';

	/**
	 * Use single submit key.
	 */
	public const SETTINGS_USE_SINGLE_SUBMIT_KEY = 'use-single-submit';

	/**
	 * Success redirect url key.
	 */
	public const SETTINGS_SUCCESS_REDIRECT_URL_KEY = 'general-redirection-success';

	/**
	 * Hide global message on success key.
	 */
	public const SETTINGS_HIDE_GLOBAL_MSG_ON_SUCCESS_KEY = 'hide-global-msg-on-success';

	/**
	 * Hide form on success key.
	 */
	public const SETTINGS_HIDE_FORM_ON_SUCCESS_KEY = 'hide-form-on-success';

	/**
	 * Increment meta key.
	 *
	 * @var string
	 */
	public const INCREMENT_META_KEY = 'es_forms_increment';

	/**
	 * Increment start key.
	 */
	public const SETTINGS_INCREMENT_START_KEY = 'increment-start';

	/**
	 * Increment length key.
	 */
	public const SETTINGS_INCREMENT_LENGTH_KEY = 'increment-length';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
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
			GeneralHelpers::getSpecialConstants('tracking'),
			\array_keys(GeneralHelpers::getSpecialConstants('tracking'))
		);

		$formDetails = GeneralHelpers::getFormDetails($formId);
		$formType = $formDetails[Config::FD_TYPE] ?? '';

		$successRedirectUrl = FiltersOuputMock::getSuccessRedirectUrlFilterValue($formType, $formId);
		$variation = FiltersOuputMock::getVariationFilterValue($formType, $formId, []);
		$trackingEventName = FiltersOuputMock::getTrackingEventNameFilterValue($formType, $formId);
		$trackingAdditionalData = FiltersOuputMock::getTrackingAditionalDataFilterValue($formType, $formId);

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('After form submission', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => SettingsHelpers::getSettingName(self::SETTINGS_SUCCESS_REDIRECT_URL_KEY),
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
									%2$s', 'eightshift-forms'), SettingsOutputHelpers::getPartialFormFieldNames($formDetails[Config::FD_FIELD_NAMES]), $successRedirectUrl['settingsLocal']),
								'inputType' => 'url',
								'inputIsUrl' => true,
								'inputIsDisabled' => $successRedirectUrl['filterUsed'],
								'inputValue' => $successRedirectUrl['dataLocal'],
							],
							[
								'component' => 'textarea',
								'textareaFieldLabel' => \__('Variation', 'eightshift-forms'),
								'textareaIsMonospace' => true,
								'textareaSaveAsJson' => true,
								'textareaName' => SettingsHelpers::getSettingName(self::SETTINGS_VARIATION_KEY),
								// translators: %s will be replaced with forms field name and filter output copy.
								'textareaFieldHelp' => \sprintf(\__('
									Define redirection values that you can use in Result output items.<br/>
									Each key must be in a separate line.
									%s', 'eightshift-forms'), $variation['settingsLocal']),
								'textareaValue' => SettingsHelpers::getSettingValueAsJson(self::SETTINGS_VARIATION_KEY, $formId, 2),
							],
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_VARIATION_SHOULD_APPEND_ON_GLOBAL_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Append on global variations', 'eightshift-forms'),
										'checkboxHelp' => \__('By default form variations will override the global variations. With this option you can append form-specific variations to global.', 'eightshift-forms'),
										'checkboxIsChecked' => SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_VARIATION_SHOULD_APPEND_ON_GLOBAL_KEY, self::SETTINGS_VARIATION_SHOULD_APPEND_ON_GLOBAL_KEY, $formId),
										'checkboxValue' => self::SETTINGS_VARIATION_SHOULD_APPEND_ON_GLOBAL_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									],
								],
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_HIDE_GLOBAL_MSG_ON_SUCCESS_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Hide global message on success', 'eightshift-forms'),
										'checkboxHelp' => \__('Usualy used in combination with single submit for calculators.', 'eightshift-forms'),
										'checkboxIsChecked' => SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_HIDE_GLOBAL_MSG_ON_SUCCESS_KEY, self::SETTINGS_HIDE_GLOBAL_MSG_ON_SUCCESS_KEY, $formId),
										'checkboxValue' => self::SETTINGS_HIDE_GLOBAL_MSG_ON_SUCCESS_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									],
								],
							],
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_HIDE_FORM_ON_SUCCESS_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Hide form on success', 'eightshift-forms'),
										'checkboxIsChecked' => SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_HIDE_FORM_ON_SUCCESS_KEY, self::SETTINGS_HIDE_FORM_ON_SUCCESS_KEY, $formId),
										'checkboxValue' => self::SETTINGS_HIDE_FORM_ON_SUCCESS_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									],
								],
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Tracking', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => SettingsHelpers::getSettingName(self::SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY),
								'inputFieldLabel' => \__('Event name', 'eightshift-forms'),
								// translators: %s will be replaced with th filter output copy.
								'inputFieldHelp' => GeneralHelpers::minifyString(\sprintf(\__('
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
								'textareaName' => SettingsHelpers::getSettingName(self::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_KEY),
								'textareaIsMonospace' => true,
								'textareaSaveAsJson' => true,
								'textareaFieldLabel' => \__('Additional parameters', 'eightshift-forms'),
								// translators: %s will be list example keys.
								'textareaFieldHelp' => GeneralHelpers::minifyString(\sprintf(\__("
									These parameters are always sent to the tracking platform.<br /><br />
									Provide one key-value pair per line, following this format: %1\$s
									<br />
									%2\$s", 'eightshift-forms'), '<code>keyName : keyValue</code>', $trackingAdditionalData['settings']['general'] ?? '')),
								'textareaValue' => SettingsHelpers::getSettingValueAsJson(self::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_KEY, $formId, 2),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'textarea',
								'textareaName' => SettingsHelpers::getSettingName(self::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_SUCCESS_KEY),
								'textareaIsMonospace' => true,
								'textareaSaveAsJson' => true,
								'textareaFieldLabel' => \__('Additional parameters on successful submit', 'eightshift-forms'),
								// translators: %s will be list example keys.
								'textareaFieldHelp' => GeneralHelpers::minifyString(\sprintf(\__("
									These parameters are sent to the tracking platform when a form is submitted successfully.<br /><br />
									Provide one key-value pair per line, following this format: %1\$s
									<br />
									%2\$s
									", 'eightshift-forms'), '<code>keyName : keyValue</code>', $trackingAdditionalData['settings']['success'] ?? '')),
								'textareaValue' => SettingsHelpers::getSettingValueAsJson(self::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_SUCCESS_KEY, $formId, 2),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'textarea',
								'textareaName' => SettingsHelpers::getSettingName(self::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_ERROR_KEY),
								'textareaIsMonospace' => true,
								'textareaSaveAsJson' => true,
								'textareaFieldLabel' => \__('Additional parameters on error', 'eightshift-forms'),
								// translators: %s will be list example keys.
								'textareaFieldHelp' => GeneralHelpers::minifyString(\sprintf(\__("
									These parameters are sent to the tracking platform when a form submission fails.<br /><br />
									Provide one key-value pair per line, following this format: %1\$s
									<br /><br />
									To provide additional data, use these constants:
									<ul>
									%2\$s
									</ul>
									%3\$s
									", 'eightshift-forms'), '<code>keyName : keyValue</code>', \implode('', $specialConstants), $trackingAdditionalData['settings']['error'] ?? '')),
								'textareaValue' => SettingsHelpers::getSettingValueAsJson(self::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_ERROR_KEY, $formId, 2),
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Identification', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => SettingsHelpers::getSettingName(self::SETTINGS_FORM_CUSTOM_NAME_KEY),
								'inputId' => SettingsHelpers::getSettingName(self::SETTINGS_FORM_CUSTOM_NAME_KEY),
								'inputFieldLabel' => \__('Form custom name', 'eightshift-forms'),
								'inputFieldHelp' => \__('Target a form (or a set of forms) and apply changes through filters, in code.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputValue' => SettingsHelpers::getSettingValue(self::SETTINGS_FORM_CUSTOM_NAME_KEY, $formId),
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Single submit', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_USE_SINGLE_SUBMIT_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Use single submit', 'eightshift-forms'),
										'checkboxIsChecked' => SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_USE_SINGLE_SUBMIT_KEY, self::SETTINGS_USE_SINGLE_SUBMIT_KEY, $formId),
										'checkboxValue' => self::SETTINGS_USE_SINGLE_SUBMIT_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									]
								]
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'intro',
								'introSubtitle' => \__('This option may create a large number of request to your server.<br /> Use with caution!', 'eightshift-forms'),
								'introIsHighlighted' => true,
								'introIsHighlightedImportant' => true,
							],
							[
								'component' => 'intro',
								'introSubtitle' => \__('
									By selecting single submit form your form will not wait for the click on the submit button.
									The form will submit data to the server as soon as the user changes are made.
									<br /><br />
									Not all fields are supported with this option.
									<br />
									Supported fields are:
									<ul>
										<li>Input range</li>
										<li>Checkbox</li>
										<li>Radio</li>
										<li>Rating</li>
										<li>Select</li>
									</ul>
								', 'eightshift-forms'),
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Increment', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => SettingsHelpers::getSettingName(self::SETTINGS_INCREMENT_START_KEY),
								'inputId' => SettingsHelpers::getSettingName(self::SETTINGS_INCREMENT_START_KEY),
								'inputFieldLabel' => \__('Increment start number', 'eightshift-forms'),
								'inputFieldHelp' => \__('Set the starting increment number of each successful form submission.', 'eightshift-forms'),
								'inputType' => 'number',
								'inputMin' => 1,
								'inputStep' => 1,
								'inputIsNumber' => true,
								'inputValue' => SettingsHelpers::getSettingValue(self::SETTINGS_INCREMENT_START_KEY, $formId),
							],
							[
								'component' => 'input',
								'inputName' => SettingsHelpers::getSettingName(self::SETTINGS_INCREMENT_LENGTH_KEY),
								'inputId' => SettingsHelpers::getSettingName(self::SETTINGS_INCREMENT_LENGTH_KEY),
								'inputFieldLabel' => \__('Increment length number', 'eightshift-forms'),
								'inputFieldHelp' => \__('Define minimal increment length you want to use. If the number is less than starting number, increment will have leading zeros.', 'eightshift-forms'),
								'inputType' => 'number',
								'inputMin' => 1,
								'inputStep' => 1,
								'inputIsNumber' => true,
								'inputValue' => SettingsHelpers::getSettingValue(self::SETTINGS_INCREMENT_LENGTH_KEY, $formId),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'layout',
								'layoutType' => 'layout-v-stack',
								'layoutContent' => [
									[
										'component' => 'card-inline',
										// translators: %s is the current increment number.
										'cardInlineTitle' => \sprintf(\__('Current increment: %s', 'eightshift-forms'), FormsHelper::getIncrement($formId)),
										'cardInlineRightContent' => [
											[
												'component' => 'submit',
												'submitValue' => \__('Reset', 'eightshift-forms'),
												'submitVariant' => 'ghost',
												'submitAttrs' => [
													UtilsHelper::getStateAttribute('formId') => $formId,
												],
												'additionalClass' => UtilsHelper::getStateSelectorAdmin('incrementReset'),
											],
										],
									],
								],
							],
						],
					],
				]
			],
		];
	}
}
