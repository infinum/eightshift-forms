<?php

/**
 * Test Settings class.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsTest class.
 */
class SettingsTest implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_test';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_test';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'test';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_SIDEBAR_NAME, [$this, 'getSettingsSidebar']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
	}

	/**
	 * Get Settings sidebar data.
	 *
	 * @return array<string, mixed>
	 */
	public function getSettingsSidebar(): array
	{
		return [
			'label' => __('Test', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.988 7.75v-5c0-1-.315-1.5-1.037-1.5-.72 0-1.217.5-1.217 1.5v8L5.372 7.9c-.58-.767-1.111-.925-1.664-.41-.553.513-.643 1.25-.063 2.016l.934 1.244c.45.5.587 1.104 1.352 4.5.45 2 2.664 3.432 4.959 3.5 3.113.092 5.86-2.545 5.86-6v-4c0-1-.316-1.5-1.037-1.5-.721 0-1.217.5-1.217 1.5m-4.508-1c0-1 .496-1.5 1.217-1.5.722 0 1.037 1 1.037 2m-2.254-.5v1.5m2.254-1c0-1 .496-1.5 1.217-1.5.722 0 1.037 1 1.037 2m-2.254-.5v1.5m2.254-1v2" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
		];
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
		return [];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		return [
			[
				'component' => 'input',
				'inputId' => $this->getSettingsName('input-id'),
				'inputFieldLabel' => __('input label', 'eightshift-forms'),
				'inputFieldHelp' => __('help field input', 'eightshift-forms'),
				'inputType' => 'text',
				'inputValue' => $this->getOptionValue('input-id'),
			],
			[
				'component' => 'input',
				'inputId' => $this->getSettingsName('input-email-id'),
				'inputFieldLabel' => __('input email label', 'eightshift-forms'),
				'inputFieldHelp' => __('help field input email', 'eightshift-forms'),
				'inputType' => 'text',
				'inputValue' => $this->getOptionValue('input-email-id'),
				'inputIsEmail' => true,
			],
			[
				'component' => 'input',
				'inputId' => $this->getSettingsName('input-number-id'),
				'inputFieldLabel' => __('input number label', 'eightshift-forms'),
				'inputFieldHelp' => __('help field input number', 'eightshift-forms'),
				'inputType' => 'number',
				'inputValue' => $this->getOptionValue('input-number-id'),
			],
			[
				'component' => 'input',
				'inputId' => $this->getSettingsName('input-url-id'),
				'inputFieldLabel' => __('input url label', 'eightshift-forms'),
				'inputFieldHelp' => __('help field input url', 'eightshift-forms'),
				'inputType' => 'text',
				'inputValue' => $this->getOptionValue('input-url-id'),
				'inputIsUrl' => true,
			],
			[
				'component' => 'input',
				'inputId' => $this->getSettingsName('input-hidden-id'),
				'inputFieldLabel' => __('input hidden label', 'eightshift-forms'),
				'inputFieldHelp' => __('help field input hidden', 'eightshift-forms'),
				'inputType' => 'hidden',
				'inputValue' => $this->getOptionValue('input-hidden-id'),
			],
			[
				'component' => 'input',
				'inputId' => $this->getSettingsName('input-required-id'),
				'inputFieldLabel' => __('input required label', 'eightshift-forms'),
				'inputFieldHelp' => __('help field input required', 'eightshift-forms'),
				'inputType' => 'text',
				'inputValue' => $this->getOptionValue('input-required-id'),
				'inputIsRequired' => true,
			],
			[
				'component' => 'textarea',
				'textareaId' => $this->getSettingsName('textarea-id'),
				'textareaFieldLabel' => __('textarea label', 'eightshift-forms'),
				'textareaFieldHelp' => __('help field textarea', 'eightshift-forms'),
				'textareaValue' => $this->getOptionValue('textarea-id'),
			],
			[
				'component' => 'textarea',
				'textareaId' => $this->getSettingsName('textarea-required-id'),
				'textareaFieldLabel' => __('textarea required label', 'eightshift-forms'),
				'textareaFieldHelp' => __('help field textarea required', 'eightshift-forms'),
				'textareaValue' => $this->getOptionValue('textarea-required-id'),
				'textareaIsRequired' => true,
			],
			[
				'component' => 'select',
				'selectId' => $this->getSettingsName('select-id'),
				'selectFieldLabel' => __('select label', 'eightshift-forms'),
				'selectFieldHelp' => __('help field select', 'eightshift-forms'),
				'selectValue' => $this->getOptionValue('select-id'),
				'selectOptions' => [
					[
						'component' => 'select-option',
						'selectOptionLabel' => 'select-label1',
						'selectOptionValue' => 'select-id1',
						'selectOptionIsSelected' => $this->isCheckedOption('select-id1', 'select-id'),
					],
					[
						'component' => 'select-option',
						'selectOptionLabel' => 'select-label2',
						'selectOptionValue' => 'select-id2',
						'selectOptionIsSelected' => $this->isCheckedOption('select-id2', 'select-id'),
					],
				]
			],
			[
				'component' => 'select',
				'selectId' => $this->getSettingsName('select-required-id'),
				'selectFieldLabel' => __('select required label', 'eightshift-forms'),
				'selectFieldHelp' => __('help field select required', 'eightshift-forms'),
				'selectValue' => $this->getOptionValue('select-required-id'),
				'selectIsRequired' => true,
				'selectOptions' => [
					[
						'component' => 'select-option',
						'selectOptionLabel' => '---',
						'selectOptionValue' => '',
						'selectOptionIsSelected' => $this->isCheckedOption('select-required-id1', 'select-required-id'),
					],
					[
						'component' => 'select-option',
						'selectOptionLabel' => 'select-label1',
						'selectOptionValue' => 'select-id1',
						'selectOptionIsSelected' => $this->isCheckedOption('select-id1', 'select-required-id'),
					],
					[
						'component' => 'select-option',
						'selectOptionLabel' => 'select-label2',
						'selectOptionValue' => 'select-id2',
						'selectOptionIsSelected' => $this->isCheckedOption('select-id2', 'select-required-id'),
					],
				]
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => __('checkboxes', 'eightshift-forms'),
				'checkboxesFieldHelp' => __('help field checkboxes', 'eightshift-forms'),
				'checkboxesId' => $this->getSettingsName('checkbox-id'),
				'checkboxesName' => $this->getSettingsName('checkbox-name'),
				'checkboxesIsRequired' => true,
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('checkbox label 1', 'eightshift-forms'),
						'checkboxValue' => 'checkbox-id1',
						'checkboxIsChecked' => $this->isCheckboxOptionChecked('checkbox-id1', 'checkbox-id'),
						'checkboxSingleSubmit' => true,
					],
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('checkbox required label 2', 'eightshift-forms'),
						'checkboxValue' => 'checkbox-required-id2',
						'checkboxIsChecked' => $this->isCheckboxOptionChecked('checkbox-required-id2', 'checkbox-id'),
					],
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('checkbox disabled label 3', 'eightshift-forms'),
						'checkboxValue' => 'checkbox-disabled-id3',
						'checkboxIsChecked' => $this->isCheckboxOptionChecked('checkbox-disabled-id3', 'checkbox-id'),
						'checkboxIsDisabled' => true,
					],
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('checkbox readonly label 4', 'eightshift-forms'),
						'checkboxValue' => 'checkbox-readonly-id4',
						'checkboxIsReadOnly' => true,
						'checkboxIsChecked' => $this->isCheckboxOptionChecked('checkbox-readonly-id4', 'checkbox-id'),
					],
				]
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => __('checkboxes count', 'eightshift-forms'),
				'checkboxesFieldHelp' => __('help field checkboxes', 'eightshift-forms'),
				'checkboxesId' => $this->getSettingsName('checkbox-count-id'),
				'checkboxesName' => $this->getSettingsName('checkbox-count-name'),
				'checkboxesIsRequired' => true,
				'checkboxesIsRequiredCount' => 2,
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('checkbox label 1', 'eightshift-forms'),
						'checkboxValue' => 'checkbox-id1',
						'checkboxIsChecked' => $this->isCheckboxOptionChecked('checkbox-required-id1', 'checkbox-count-id'),
					],
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('checkbox required label 2', 'eightshift-forms'),
						'checkboxValue' => 'checkbox-required-id2',
						'checkboxIsChecked' => $this->isCheckboxOptionChecked('checkbox-required-id2', 'checkbox-count-id'),
					],
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('checkbox disabled label 3', 'eightshift-forms'),
						'checkboxValue' => 'checkbox-disabled-id3',
						'checkboxIsChecked' => $this->isCheckboxOptionChecked('checkbox-required-id3', 'checkbox-count-id'),
					],
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('checkbox readonly label 4', 'eightshift-forms'),
						'checkboxValue' => 'checkbox-readonly-id4',
						'checkboxIsChecked' => $this->isCheckboxOptionChecked('checkbox-required-id4', 'checkbox-count-id'),
					],
				]
			],
			[
				'component' => 'radios',
				'radiosFieldLabel' => __('radios', 'eightshift-forms'),
				'radiosFieldHelp' => __('help field radios', 'eightshift-forms'),
				'radiosId' => $this->getSettingsName('radio-id'),
				'radiosName' => $this->getSettingsName('radio-name'),
				'radiosContent' => [
					[
						'component' => 'radio',
						'radioLabel' => __('radio label 1', 'eightshift-forms'),
						'radioValue' => 'radio-id1',
						'radioIsChecked' => $this->isCheckedOption('radio-id1', 'radio-id'),
					],
					[
						'component' => 'radio',
						'radioLabel' => __('radio disabled label 2', 'eightshift-forms'),
						'radioValue' => 'radio-disabled-id2',
						'radioIsChecked' => $this->isCheckedOption('radio-disabled-id2', 'radio-id'),
						'radioIsDisabled' => true,
					],
					[
						'component' => 'radio',
						'radioLabel' => __('radio disabled checked label 3', 'eightshift-forms'),
						'radioValue' => 'radio-disabled-checked-id3',
						'radioIsChecked' => $this->isCheckedOption('radio-disabled-checked-id3', 'radio-id'),
						'radioIsDisabled' => true,
					],
				]
			],
			[
				'component' => 'radios',
				'radiosFieldLabel' => __('radios required', 'eightshift-forms'),
				'radiosFieldHelp' => __('help field radios required', 'eightshift-forms'),
				'radiosId' => $this->getSettingsName('radio-required-id'),
				'radiosName' => $this->getSettingsName('radio-required-name'),
				'radiosIsRequired' => true,
				'radiosContent' => [
					[
						'component' => 'radio',
						'radioLabel' => __('radio required label 1', 'eightshift-forms'),
						'radioValue' => 'radio-required-id1',
						'radioIsChecked' => $this->isCheckedOption('radio-required-id1', 'radio-required-id'),
					],
					[
						'component' => 'radio',
						'radioLabel' => __('radio required label 2', 'eightshift-forms'),
						'radioValue' => 'radio-required-id2',
						'radioIsChecked' => $this->isCheckedOption('radio-required-id2', 'radio-required-id'),
					],
				]
			],
		];
	}
}
