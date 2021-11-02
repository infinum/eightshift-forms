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
			'icon' => '<svg width="30" height="30" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient x1="26.16%" y1="24.981%" x2="76.048%" y2="78.163%" id="a"><stop stop-color="#7FAEF4" offset="0%"/><stop stop-color="#4C8DF1" offset="100%"/></linearGradient><linearGradient x1="106.435%" y1="106.435%" x2="-10.952%" y2="-10.952%" id="b"><stop stop-color="#7FAEF4" offset="0%"/><stop stop-color="#4C8DF1" offset="100%"/></linearGradient><linearGradient x1="40.923%" y1="50.665%" x2="-7.576%" y2="26.721%" id="c"><stop stop-color="#4C8DF1" stop-opacity="0" offset="0%"/><stop stop-color="#466CC8" stop-opacity=".563" offset="56.31%"/><stop stop-color="#4256AC" offset="100%"/></linearGradient><linearGradient x1="67.279%" y1="69.281%" x2="33.33%" y2="49.714%" id="d"><stop stop-color="#4C8DF1" stop-opacity="0" offset="0%"/><stop stop-color="#466CC8" stop-opacity=".563" offset="56.31%"/><stop stop-color="#4256AC" offset="100%"/></linearGradient><linearGradient x1="48.225%" y1="41.869%" x2="78.661%" y2="85.684%" id="e"><stop stop-color="#E3E5E4" offset="0%"/><stop stop-color="#CBD0E4" offset="100%"/></linearGradient><linearGradient x1="69.973%" y1="66.954%" x2="42.81%" y2="-9.267%" id="f"><stop stop-color="#CBD0E4" stop-opacity="0" offset="0%"/><stop stop-color="#6A7D83" offset="100%"/></linearGradient><linearGradient x1="31.683%" y1="29.328%" x2="96.972%" y2="88.968%" id="g"><stop stop-color="#E3E5E4" offset="0%"/><stop stop-color="#CBD0E4" offset="100%"/></linearGradient><linearGradient x1="87.155%" y1="78.007%" x2="-11.926%" y2="-21.546%" id="h"><stop stop-color="#E3E5E4" offset="0%"/><stop stop-color="#CBD0E4" offset="100%"/></linearGradient></defs><g fill-rule="nonzero" fill="none"><g transform="translate(1)"><path d="M25.169 15c0-.441-.026-.877-.076-1.305a1.69 1.69 0 01.57-1.461l1.715-1.505c.582-.51.713-1.365.31-2.026l-1.834-3.011a1.593 1.593 0 00-1.943-.655l-2.137.84a1.687 1.687 0 01-1.56-.18 11.189 11.189 0 00-1.566-.88 1.685 1.685 0 01-.96-1.238l-.395-2.26A1.594 1.594 0 0015.723 0h-3.525c-.775 0-1.437.556-1.57 1.32l-.395 2.26a1.685 1.685 0 01-.96 1.237c-.548.252-1.07.547-1.566.88-.461.311-1.042.382-1.56.18l-2.137-.84a1.594 1.594 0 00-1.943.655L.233 8.702a1.594 1.594 0 00.31 2.027l1.714 1.505c.417.365.635.91.57 1.461a11.267 11.267 0 000 2.61 1.69 1.69 0 01-.57 1.461L.543 19.27a1.594 1.594 0 00-.31 2.027l1.834 3.011a1.593 1.593 0 001.943.655l2.137-.84a1.687 1.687 0 011.56.18c.495.333 1.018.628 1.565.88.504.232.865.692.96 1.238l.396 2.26c.133.763.795 1.084 1.57 1.084h3.525c.774 0 1.436-.321 1.57-1.084l.395-2.26a1.685 1.685 0 01.96-1.238c.547-.252 1.07-.547 1.566-.88a1.687 1.687 0 011.56-.18l2.137.84c.72.283 1.54.006 1.943-.655l1.834-3.01a1.594 1.594 0 00-.31-2.027l-1.715-1.505a1.69 1.69 0 01-.57-1.461c.05-.428.076-.864.076-1.305z" fill="url(#a)"/><circle fill="url(#b)" cx="13.96" cy="15" r="9.162"/></g><path d="M15.723 30c.774 0 1.436-.556 1.57-1.32l.395-2.26a1.685 1.685 0 01.96-1.237c.547-.252 1.07-.547 1.566-.88a1.687 1.687 0 011.56-.18l2.137.84c.72.283 1.54.006 1.943-.655l1.834-3.01a1.594 1.594 0 00-.31-2.027l-1.715-1.505a1.69 1.69 0 01-.57-1.461c.03-.257.05-.516.062-.778L19.508 9.88a7.59 7.59 0 00-1.164-1.027.517.517 0 00-.815.423v4.983c0 .515-.234.996-.626 1.315-.074-.07-6.575-6.56-6.629-6.626-.162-.196-.464-.144-.702.026-1.918 1.372-3.048 3.502-3.044 6.04.004 2.185.816 4.15 2.299 5.524l1.623 1.63.06 3.79c0 .913-.04 1.824.118 2.723A1.594 1.594 0 0012.198 30h3.525z" fill="url(#c)" transform="translate(1)"/><path d="M27.688 21.297l-1.834 3.011a1.595 1.595 0 01-1.943.655l-2.138-.84a1.687 1.687 0 00-1.56.18c-.494.333-1.018.628-1.565.88a1.685 1.685 0 00-.96 1.238l-.395 2.26c-.134.763-.796 1.143-1.57 1.143h-3.526c-.387 0-.745-.14-1.024-.374-.143-.12-.264-.087-.357-.25.234.135.506.212.793.212h3.526c.774 0 1.436-.557 1.57-1.32a15.959 15.959 0 00.236-2.722v-4.304a7.548 7.548 0 003.98-6.654 7.519 7.519 0 00-1.92-5.03c.177.157.346.323.507.498l5.647 5.647c-.012.262-.032.521-.062.778a1.69 1.69 0 00.57 1.461l1.715 1.505c.582.51.713 1.365.31 2.026z" fill="url(#d)" transform="translate(1)"/><path d="M17.293 28.68c.157-.898.236-1.81.236-2.722v-6.233h-7.137v6.233c0 .913.079 1.824.236 2.723.133.763.795 1.319 1.57 1.319h3.525c.774 0 1.436-.556 1.57-1.32z" fill="url(#e)" transform="translate(1)"/><path d="M10.392 21.656l6.918 6.919c.145-.865.219-1.74.219-2.617v-6.233h-7.137v1.931z" fill="url(#f)" transform="translate(1)"/><path d="M21.51 15a7.55 7.55 0 01-7.55 7.55 7.542 7.542 0 01-7.55-7.536 7.54 7.54 0 013.162-6.158.517.517 0 01.82.42v4.983c0 .606.323 1.166.848 1.469l1.872 1.081a1.696 1.696 0 001.697 0l1.872-1.081c.525-.303.848-.863.848-1.47V9.277c0-.42.474-.666.816-.423A7.54 7.54 0 0121.51 15z" fill="url(#g)" transform="translate(1)"/><path d="M8.373 15.636c0 .949.507 1.825 1.328 2.3l2.932 1.692a2.656 2.656 0 002.655 0l2.932-1.692a2.655 2.655 0 001.327-2.3V9.924a7.593 7.593 0 00-1.202-1.07.517.517 0 00-.816.422v4.983c0 .606-.323 1.166-.848 1.469l-1.872 1.081a1.696 1.696 0 01-1.697 0l-1.872-1.081a1.696 1.696 0 01-.849-1.47V9.277a.517.517 0 00-.819-.42c-.437.313-.838.671-1.199 1.067v5.713z" fill="url(#h)" transform="translate(1)"/></g></svg>',
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
				'inputType' => 'email',
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
						'selectOptionIsSelected' => $this->getOptionValue('select-id') === 'select-id1',
					],
					[
						'component' => 'select-option',
						'selectOptionLabel' => 'select-label2',
						'selectOptionValue' => 'select-id2',
						'selectOptionIsSelected' => $this->getOptionValue('select-id') === 'select-id2',
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
						'selectOptionIsSelected' => $this->getOptionValue('select-required-id') === '',
					],
					[
						'component' => 'select-option',
						'selectOptionLabel' => 'select-label1',
						'selectOptionValue' => 'select-id1',
						'selectOptionIsSelected' => $this->getOptionValue('select-required-id') === 'select-id1',
					],
					[
						'component' => 'select-option',
						'selectOptionLabel' => 'select-label2',
						'selectOptionValue' => 'select-id2',
						'selectOptionIsSelected' => $this->getOptionValue('select-required-id') === 'select-id2',
					],
				]
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => __('checkboxes', 'eightshift-forms'),
				'checkboxesFieldHelp' => __('help field checkboxes', 'eightshift-forms'),
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxId' => $this->getSettingsName('checkbox-id1'),
						'checkboxLabel' => __('checkbox label 1', 'eightshift-forms'),
						'checkboxValue' => 'checkbox-id1',
						'checkboxIsChecked' => $this->getOptionValue('checkbox-id1') === 'checkbox-id1',
					],
					[
						'component' => 'checkbox',
						'checkboxId' => $this->getSettingsName('checkbox-required-id2'),
						'checkboxLabel' => __('checkbox required label 2', 'eightshift-forms'),
						'checkboxValue' => 'checkbox-required-id2',
						'checkboxIsChecked' => $this->getOptionValue('checkbox-required-id2') === 'checkbox-required-id2',
						'checkboxIsRequired' => true,
					],
					[
						'component' => 'checkbox',
						'checkboxId' => $this->getSettingsName('checkbox-disabled-id3'),
						'checkboxLabel' => __('checkbox disabled label 3', 'eightshift-forms'),
						'checkboxValue' => 'checkbox-disabled-id3',
						'checkboxIsChecked' => $this->getOptionValue('checkbox-disabled-id3') === 'checkbox-disabled-id3',
						'checkboxIsDisabled' => true,
					],
					[
						'component' => 'checkbox',
						'checkboxId' => $this->getSettingsName('checkbox-readonly-id4'),
						'checkboxLabel' => __('checkbox readonly label 4', 'eightshift-forms'),
						'checkboxValue' => 'checkbox-readonly-id4',
						'checkboxIsReadOnly' => true,
						'checkboxIsChecked' => $this->getOptionValue('checkbox-readonly-id4') === 'checkbox-readonly-id4',
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
						'radioIsChecked' => $this->getOptionValue('radio-id') === 'radio-id1',
					],
					[
						'component' => 'radio',
						'radioLabel' => __('radio disabled label 2', 'eightshift-forms'),
						'radioValue' => 'radio-disabled-id2',
						'radio' => 'radio-disabled-id2',
						'radioIsDisabled' => true,
					],
					[
						'component' => 'radio',
						'radioLabel' => __('radio disabled checked label 3', 'eightshift-forms'),
						'radioValue' => 'radio-disabled-checked-id3',
						'radio' => 'radio-disabled-checked-id3',
						'radioIsDisabled' => true,
						'radioIsChecked' => true,
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
						'radioIsChecked' => $this->getOptionValue('radio-required-id') === 'radio-required-id1',
					],
					[
						'component' => 'radio',
						'radioLabel' => __('radio required label 2', 'eightshift-forms'),
						'radioValue' => 'radio-required-id2',
						'radioIsChecked' => $this->getOptionValue('radio-required-id') === 'radio-required-id2',
					],
				]
			],
		];
	}
}
