<?php

/**
 * General Settings class.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\FiltersOuputMock;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsGeneral class.
 */
class SettingsGeneral implements SettingInterface, SettingGlobalInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Use general helper trait.
	 */
	use FiltersOuputMock;

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
			Filters::getSpecialConstants('tracking'),
			\array_keys(Filters::getSpecialConstants('tracking'))
		);

		$formType = Helper::getFormDetailsById($formId)['type'] ?? '';

		$successRedirectUrl = $this->getSuccessRedirectUrlFilterValue($formType, $formId);
		$successRedirectVariation = $this->getSuccessRedirectVariationFilterValue($formType, $formId);
		$successRedirectVariationOptions = $this->getSuccessRedirectVariationOptionsFilterValue();
		$trackingEventName = $this->getTrackingEventNameFilterValue($formType, $formId);
		$trackingAdditionalData = $this->getTrackingAditionalDataFilterValue($formType, $formId);

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Submit', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_GENERAL_REDIRECT_SUCCESS_KEY),
								'inputFieldLabel' => \__('After submit redirect URL', 'eightshift-forms'),
								// translators: %s will be replaced with forms field name and filter output copy.
								'inputFieldHelp' => \sprintf(\__('
									If URL is provided, after a successful submission the user is redirected to the provided URL and the success message will <strong>not</strong> show.<br />
									Data from the form can be used in the form of template tags (<code>{field-name}</code>).<br />
									If some tags are missing or you don\'t see any tags above, check that the <code>name</code> on the form field is set in the Form editor.<br />
									These tags are detected from the form:
									<br />
									%1$s %2$s', 'eightshift-forms'), Helper::getFormFieldNames($formId), $successRedirectUrl['settingsLocal']),
								'inputType' => 'url',
								'inputIsUrl' => true,
								'inputIsDisabled' => $successRedirectUrl['filterUsedLocal'],
								'inputValue' => $successRedirectUrl['dataLocal'],
							],
							[
								'component' => 'select',
								'selectFieldLabel' => \__('After submit redirect variation', 'eightshift-forms'),
								'selectName' => $this->getSettingsName(self::SETTINGS_GENERAL_SUCCESS_REDIRECT_VARIATION_KEY),
								'selectPlaceholder' => \__('Select option', 'eightshift-forms'),
								'selectIsDisabled' => $successRedirectVariation['filterUsed'],
								// translators: %s will be replaced with forms field name and filter output copy.
								'selectFieldHelp' => \sprintf(\__('
									This attributes we will be added to your redirect url as a parameter so you can provide tnx page variations based on the key.<br/>
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
								'inputName' => $this->getSettingsName(self::SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY),
								'inputFieldLabel' => \__('Tracking event name', 'eightshift-forms'),
								// translators: %s will be replaced with th filter output copy.
								'inputFieldHelp' => Helper::minifyString(\sprintf(\__("
									Used when pushing data to Google Tag Manager, if nothing is provided GTM event will not be sent. %s", 'eightshift-forms'), $trackingEventName['settings'])),
								'inputType' => 'text',
								'inputIsDisabled' => $trackingEventName['filterUsed'],
								'inputValue' => $trackingEventName['data'],
							],
							[
								'component' => 'textarea',
								'textareaName' => $this->getSettingsName(self::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_KEY),
								'textareaIsMonospace' => true,
								'textareaSaveAsJson' => true,
								'textareaFieldLabel' => \__('Tacking additional parameters', 'eightshift-forms'),
								// translators: %s will be list example keys.
								'textareaFieldHelp' => Helper::minifyString(\sprintf(\__("
									This attributes we will send to the tracking software every time.<br/>
									One key value pair should be provided per line, in the following format:<br />
									Here are some examples:
									<ul>
									%1\$s
									</ul>
									%2\$s", 'eightshift-forms'), '<li><code>testKey : keyValue</code></li>', $trackingAdditionalData['settings']['general'] ?? '')),
								'textareaValue' => $this->getSettingsValueAsJson(self::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_KEY, $formId, 2),
							],
							[
								'component' => 'textarea',
								'textareaName' => $this->getSettingsName(self::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_SUCCESS_KEY),
								'textareaIsMonospace' => true,
								'textareaSaveAsJson' => true,
								'textareaFieldLabel' => \__('Tacking additional parameters on success', 'eightshift-forms'),
								// translators: %s will be list example keys.
								'textareaFieldHelp' => Helper::minifyString(\sprintf(\__("
									This attributes we will send to the tracking software on form success.<br />
									One key value pair should be provided per line, in the following format:<br />
									Here are some examples:
									<ul>
									%1\$s
									</ul>
									%2\$s
									", 'eightshift-forms'), '<li><code>testKey : keyValue</code></li>', $trackingAdditionalData['settings']['success'] ?? '')),
								'textareaValue' => $this->getSettingsValueAsJson(self::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_SUCCESS_KEY, $formId, 2),
							],
							[
								'component' => 'textarea',
								'textareaName' => $this->getSettingsName(self::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_ERROR_KEY),
								'textareaIsMonospace' => true,
								'textareaSaveAsJson' => true,
								'textareaFieldLabel' => \__('Tacking additional parameters on error', 'eightshift-forms'),
								// translators: %s will be list example keys.
								'textareaFieldHelp' => Helper::minifyString(\sprintf(\__("
									This attributes we will send to the tracking software on form error.<br />
									One key value pair should be provided per line, in the following format:<br />
									Here are some examples:
									<ul>
									%1\$s
									</ul>
									<br />
									In this setting you can use special contants to output dynamic data:
									<ul>
									%2\$s
									</ul>
									%3\$s
									", 'eightshift-forms'), '<li><code>testKey : keyValue</code></li>', \implode('', $specialConstants), $trackingAdditionalData['settings']['error'] ?? '')),
								'textareaValue' => $this->getSettingsValueAsJson(self::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_ERROR_KEY, $formId, 2),
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Identification', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_GENERAL_FORM_CUSTOM_NAME_KEY),
								'inputId' => $this->getSettingsName(self::SETTINGS_GENERAL_FORM_CUSTOM_NAME_KEY),
								'inputFieldLabel' => \__('Form custom name', 'eightshift-forms'),
								'inputFieldHelp' => \__('Provide your form with a custom, maybe unique, name that your developer can reference using filters and apply changes only to this form. If you want to provide modifications to multiple forms, you can use the same name on various forms.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputValue' => $this->getSettingsValue(self::SETTINGS_GENERAL_FORM_CUSTOM_NAME_KEY, $formId),
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
		$successRedirectVariationOptions = $this->getSuccessRedirectVariationOptionsFilterValue();

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Submit', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'textarea',
								'textareaName' => $this->getSettingsName(self::SETTINGS_GENERAL_SUCCESS_REDIRECT_VARIATION_OPTIONS_KEY),
								'textareaIsMonospace' => true,
								'selectIsDisabled' => $successRedirectVariationOptions['filterUsed'],
								'textareaSaveAsJson' => true,
								'textareaFieldLabel' => \__('After submit redirect varitations', 'eightshift-forms'),
								// translators: %s will be replaced with local validation patterns.
								'textareaFieldHelp' => Helper::minifyString(\sprintf(\__("
									This attributes we will create dropdown options for your forms tnx page that you can use to provide variations to the users.<br/>
									One key value pair should be provided per line, in the following format:<br />
									Here are some examples:
									<ul>
									%1\$s
									</ul>
									%2\$s", 'eightshift-forms'), '<li><code>slug : label</code></li>', $successRedirectVariationOptions['settings'])),
								'textareaValue' => $this->getOptionValueAsJson(self::SETTINGS_GENERAL_SUCCESS_REDIRECT_VARIATION_OPTIONS_KEY, 2),
							],
						],
					],
				],
			],
		];
	}
}
