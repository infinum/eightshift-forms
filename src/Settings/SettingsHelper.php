<?php

/**
 * Trait that holds all generic helpers used in classes.
 *
 * @package EightshiftLibs\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings;

use EightshiftForms\Helpers\Helper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;
use EightshiftForms\Settings\Settings\SettingsDashboard;

/**
 * SettingsHelper trait.
 */
trait SettingsHelper
{
	/**
	 * Integration field style.
	 *
	 * @var string
	 */
	private $integrationFieldStyle = 'field-style';

	/**
	 * Integration field order.
	 *
	 * @var string
	 */
	private $integrationFieldOrder = 'order';

	/**
	 * Integration field use.
	 *
	 * @var string
	 */
	private $integrationFieldUse = 'use';

	/**
	 * Integration field file info label.
	 *
	 * @var string
	 */
	private $integrationFieldFileInfoLabel = 'file-info-label';

	/**
	 * Integration field label.
	 *
	 * @var string
	 */
	private $integrationFieldLabel = 'label';

	/**
	 * Get settings value.
	 *
	 * @param string $key Providing string to append to.
	 * @param string $formId Form Id.
	 *
	 * @return string
	 */
	public function getSettingsValue(string $key, string $formId): string
	{
		return (string) \get_post_meta((int) $formId, $this->getSettingsName($key), true);
	}

	/**
	 * Get settings value array.
	 *
	 * @param string $key Providing string to append to.
	 * @param string $formId Form Id.
	 *
	 * @return array<string, mixed>
	 */
	public function getSettingsValueGroup(string $key, string $formId): array
	{
		$value = \get_post_meta((int) $formId, $this->getSettingsName($key), true);
		if (!$value) {
			return [];
		}

		return $value;
	}

	/**
	 * Get option value.
	 *
	 * @param string $key Providing string to append to.
	 *
	 * @return string
	 */
	public function getOptionValue(string $key): string
	{
		return (string) \get_option($this->getSettingsName($key), false);
	}

	/**
	 * Get option value array.
	 *
	 * @param string $key Providing string to append to.
	 *
	 * @return array<string, mixed>
	 */
	public function getOptionValueGroup(string $key): array
	{
		$value = \get_option($this->getSettingsName($key), false);
		if (!$value) {
			return [];
		}

		return $value;
	}

	/**
	 * Get option checkbox multiple value.
	 *
	 * @param string $key Providing string to append to.
	 *
	 * @return array<int, string>
	 */
	public function getOptionCheckboxValues(string $key): array
	{
		$value = $this->getOptionValue($key);

		if (!$value) {
			return [];
		};

		return \explode(', ', $value);
	}

	/**
	 * Determine if settings is checked (used for radio, and select box).
	 *
	 * @param string $key Key to find.
	 * @param string $id Checkboxes ID.
	 * @param string $formId Form Id.
	 *
	 * @return bool
	 */
	public function isCheckedSettings(string $key, string $id, string $formId): bool
	{
		return $this->getSettingsValue($id, $formId) === $key;
	}

	/**
	 * Determine if global is checked (used for radio, and select box).
	 *
	 * @param string $key Key to find.
	 * @param string $id Checkboxes ID.
	 *
	 * @return bool
	 */
	public function isCheckedOption(string $key, string $id): bool
	{
		return $this->getOptionValue($id) === $key;
	}

	/**
	 * Determine if checkbox settings is checked (used for checkbox).
	 *
	 * @param string $key Key to find.
	 * @param string $id Checkboxes ID.
	 * @param string $formId Form Id.
	 *
	 * @return bool
	 */
	public function isCheckboxSettingsChecked(string $key, string $id, string $formId): bool
	{
		return \in_array($key, \explode(', ', $this->getSettingsValue($id, $formId)), true);
	}

	/**
	 * Determine if checkbox global is checked (used for checkbox).
	 *
	 * @param string $key Key to find.
	 * @param string $id Checkboxes ID.
	 *
	 * @return bool
	 */
	public function isCheckboxOptionChecked(string $key, string $id): bool
	{
		return \in_array($key, \explode(', ', $this->getOptionValue($id)), true);
	}

	/**
	 * Get string name with locale.
	 *
	 * @param string $string Providing string to append to.
	 *
	 * @return string
	 */
	public function getSettingsName(string $string): string
	{
		return "es-forms-{$string}-" . $this->getLocale();
	}

	/**
	 * Set locale depending on default locale or hook override.
	 *
	 * @return string
	 */
	public function getLocale(): string
	{
		$locale = \get_locale();
		$filterName = Filters::getGeneralFilterName('setLocale');

		if (\has_filter($filterName)) {
			$locale = \apply_filters($filterName, $locale);
		}

		return $locale;
	}

	/**
	 * Get Integration forms Fields details (used to set field width, order, etc for rensponsive)
	 *
	 * @param string $key Key to save in db.
	 * @param string $type Form type.
	 * @param array<int, array<string, mixed>> $formFields All form fields got from helper.
	 * @param string $formId Form ID.
	 * @param array<int, string> $additionalLabel Additional label to show.
	 *
	 * @return array<string, array<int, mixed>>
	 */
	public function getIntegrationFieldsDetails(string $key, string $type, array $formFields, string $formId, array $additionalLabel = []): array
	{
		$additionalLabel = \array_flip($additionalLabel);

		// Find project breakpoints.
		$breakpoints = \array_flip(Components::getSettingsGlobalVariablesBreakpoints());

		// Loop form fields.
		$fields = [];
		$hiddenFields = [];

		$fieldsValues = $this->getSettingsValueGroup($key, $formId);
		$disabledEdit = false;

		// Filter for editable option and data.
		$formViewDetailsFilterName = Filters::getIntegrationFilterName($type, 'fieldsSettings');
		$formViewDetailsIsEditableFilterName = Filters::getIntegrationFilterName($type, 'fieldsSettingsIsEditable');

		if (\has_filter($formViewDetailsIsEditableFilterName)) {
			$disabledEdit = true;
			$fieldsValues = [];
		}

		if (!$fieldsValues && \has_filter($formViewDetailsFilterName)) {
			$fieldsValues = $this->prepareFormViewDetails(\apply_filters($formViewDetailsFilterName, $formFields, $formId) ?? []);
		}

		$filterName = Filters::getBlockFilterName('field', 'styleOptions');
		$fieldStyle = \apply_filters($filterName, []);

		foreach ($formFields as $fieldKey => $field) {
			if (!$field) {
				continue;
			}

			$fieldDetails = $this->getFormFieldDetailsWithoutComponentName($field);

			$component = $fieldDetails['component'];
			$id = $fieldDetails['id'];
			$required = $fieldDetails['required'];
			$label = $fieldDetails['label'];

			if ($type === SettingsGreenhouse::SETTINGS_TYPE_KEY && ($id === 'resume_text' || $id === 'cover_letter_text')) {
				$label = "{$label} Text";
			}

			$inputType = $fieldDetails['inputType'];
			if ($inputType === 'hidden') {
				$hiddenFields[] = $label;
				continue;
			}

			$fieldsOutput = [
				[
					'component' => 'group',
					'groupLabel' => \ucfirst($label),
					'groupSaveOneField' => true,
					'groupStyle' => 'integration-inner',
					'groupContent' => [],
					'groupBeforeContent' => $this->getAppliedFilterOutput($formViewDetailsFilterName),
				]
			];

			// Breakpoints.
			$i = 0;
			foreach ($breakpoints as $breakpoint) {
				$item = [
					'component' => 'input',
					'inputId' => "{$id}---{$breakpoint}",
					// translators: %s is replaced with the breakpoint name.
					'inputFieldLabel' => \sprintf(\esc_html__('Width (%s)', 'eightshift-forms'), $breakpoint),
					'inputType' => 'number',
					'inputValue' => $fieldsValues["{$id}---{$breakpoint}"] ?? '',
					'inputMin' => 0,
					'inputMax' => 12,
					'inputStep' => 1,
					'inputIsDisabled' => $disabledEdit,
					'inputPlaceholder' => \__('auto', 'eightshift-forms'),
					'inputFieldUseTooltip' => true,
					// translators: %s is replaced with the breakpoint name.
					'inputFieldTooltipContent' => \sprintf(\esc_html__('Define field width for %s breakpoint.', 'eightshift-forms'), $breakpoint),
					'inputAttrs' => [
						'data-integration-field-type' => $breakpoint,
					],
				];

				$fieldsOutput[0]['groupContent'][] = $item;

				$i++;
			}

			// Order.
			$fieldsOutput[0]['groupContent'][] = [
				'component' => 'input',
				'inputId' => "{$id}---{$this->integrationFieldOrder}",
				'inputFieldLabel' => \__('Order', 'eightshift-forms'),
				'inputType' => 'hidden',
				'inputValue' => $fieldsValues["{$id}---{$this->integrationFieldOrder}"] ?? $fieldKey + 1,
				'inputIsDisabled' => $disabledEdit,
				'inputAttrs' => [
					'data-integration-field-type' => $this->integrationFieldOrder,
				],
			];

			// Use.
			$toggleValue = $fieldsValues["{$id}---{$this->integrationFieldUse}"] ?? '';
			$toggleDisabled = $required;

			// Changes for resume and cover specific to Greenhouse.
			if ($type === SettingsGreenhouse::SETTINGS_TYPE_KEY && ($id === 'resume_text' || $id === 'cover_letter_text')) {
				$toggleDisabled = false;
			}

			$fieldsOutput[0]['groupContent'][] = [
				'component' => 'select',
				'selectId' => "{$id}---{$this->integrationFieldUse}",
				'selectFieldLabel' => \__('Visibility', 'eightshift-forms'),
				'selectValue' => $toggleValue,
				'selectIsDisabled' => $toggleDisabled || $disabledEdit,
				'selectFieldUseTooltip' => true,
				'selectFieldTooltipContent' => \__('Define if field is going to be used or not by default.', 'eightshift-forms'),
				'selectOptions' => [
					[
						'component' => 'select-option',
						'selectOptionLabel' => \__('Visible', 'eightshift-forms'),
						'selectOptionValue' => 'true',
						'selectOptionIsSelected' => $toggleValue === 'true',
					],
					[
						'component' => 'select-option',
						'selectOptionLabel' => \__('Hidden', 'eightshift-forms'),
						'selectOptionValue' => 'false',
						'selectOptionIsSelected' => $toggleValue === 'false',
					]
				],
				'selectAttrs' => [
					'data-integration-field-type' => $this->integrationFieldUse,
				],
			];

			// Changes for file type.
			if ($component === 'file') {
				$fileInfoLabelValue = $fieldsValues["{$id}---{$this->integrationFieldFileInfoLabel}"] ?? '';

				$fieldsOutput[0]['groupContent'][] = [
					'component' => 'select',
					'selectId' => "{$id}---{$this->integrationFieldFileInfoLabel}",
					'selectFieldLabel' => \__('Field label', 'eightshift-forms'),
					'selectValue' => $fileInfoLabelValue,
					'selectIsDisabled' => $disabledEdit,
					'selectFieldUseTooltip' => true,
					'selectFieldTooltipContent' => \__('Define if field file label is going to be default visible or hidden.', 'eightshift-forms'),
					'selectOptions' => [
						[
							'component' => 'select-option',
							'selectOptionLabel' => \__('Hidden', 'eightshift-forms'),
							'selectOptionValue' => 'false',
							'selectOptionIsSelected' => $fileInfoLabelValue === 'false',
						],
						[
							'component' => 'select-option',
							'selectOptionLabel' => \__('Visible', 'eightshift-forms'),
							'selectOptionValue' => 'true',
							'selectOptionIsSelected' => $fileInfoLabelValue === 'true',
						]
						],
						'selectAttrs' => [
							'data-integration-field-type' => $this->integrationFieldFileInfoLabel,
						],
				];
			}

			// Field style.
			if ($fieldStyle && isset($fieldStyle[$component])) {
				$fieldStyleValue = $fieldsValues["{$id}---{$this->integrationFieldStyle}"] ?? '';

				$fieldsOutput[0]['groupContent'][] = [
					'component' => 'select',
					'selectId' => "{$id}---{$this->integrationFieldStyle}",
					'selectFieldLabel' => \__('Style', 'eightshift-forms'),
					'selectValue' => $fieldStyleValue,
					'selectIsDisabled' => $disabledEdit,
					'selectFieldUseTooltip' => true,
					'selectFieldTooltipContent' => \__('Define different style for this field.', 'eightshift-forms'),
					'selectOptions' => \array_map(
						static function ($item) use ($fieldStyleValue) {
							return [
								'component' => 'select-option',
								'selectOptionLabel' => $item['label'],
								'selectOptionValue' => $item['value'],
								'selectOptionIsSelected' => $fieldStyleValue === $item['value'],
							];
						},
						$fieldStyle[$component]
					),
					'selectAttrs' => [
						'data-integration-field-type' => $this->integrationFieldStyle,
					],
				];
			}

			// Submit label.
			if (isset($additionalLabel[$id]) || $component === 'submit') {
				$fieldsOutput[0]['groupContent'][] = [
					'component' => 'input',
					'inputId' => "{$id}---{$this->integrationFieldLabel}",
					'inputFieldLabel' => \__('Label', 'eightshift-forms'),
					'inputValue' => $fieldsValues["{$id}---{$this->integrationFieldLabel}"] ?? '',
					'inputIsDisabled' => $disabledEdit,
					'selectFieldUseTooltip' => true,
					'selectFieldTooltipContent' => \__('Define field label value.', 'eightshift-forms'),
					'selectAttrs' => [
						'data-integration-field-type' => $this->integrationFieldLabel,
					],
				];
			}

			$fields = \array_merge($fields, $fieldsOutput);
		}

		\usort($fields, [$this, 'sortFields']);

		return [
			'fields' => $fields,
			'hiddenFields' => $hiddenFields,
		];
	}

	/**
	 * Build integration fields value output with full component array.
	 *
	 * @param array<string, mixed> $dbSettingsValue Field to search in settings.
	 * @param array<int, array<string, mixed>> $formFields Full form components array.
	 * @param string $type Form type.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getIntegrationFieldsValue(array $dbSettingsValue, array $formFields, string $type): array
	{
		// Provide project default if nothing is set in the DB.
		$formViewDetailsFilterName = Filters::getIntegrationFilterName($type, 'fieldsSettings');
		$formViewDetailsIsEditableFilterName = Filters::getIntegrationFilterName($type, 'fieldsSettingsIsEditable');

		if (\has_filter($formViewDetailsIsEditableFilterName)) {
			$dbSettingsValue = [];
		}

		if (!$dbSettingsValue && \has_filter($formViewDetailsFilterName)) {
			$dbSettingsValue = $this->prepareFormViewDetails(\apply_filters($formViewDetailsFilterName, $formFields) ?? []);
		}

		if (!$dbSettingsValue) {
			return $formFields;
		}

		// Get value saved in the db and loop all fields inside.
		$dbSettingsValuePrepared = [];
		foreach ($dbSettingsValue as $key => $value) {
			$item = \explode('---', $key);

			if (isset($item[0]) && isset($item[1])) {
				$dbSettingsValuePrepared[$item[0]][$item[1]] = $value;
			}
		}

		// Bailout if notihing is saved in the db.
		if (!$dbSettingsValuePrepared) {
			return $formFields;
		}

		// Loop each component and populate new values.
		foreach ($formFields as $key => $value) {
			if (!$value) {
				continue;
			}

			$fieldDetails = $this->getFormFieldDetailsWithoutComponentName($value);

			// Find field component name.
			$component = $fieldDetails['component'];

			// Find field id.
			$id = $fieldDetails['id'];

			// Find field type.
			$fieldType = $value["{$component}Type"] ?? '';

			// Find field label.
			$label = $fieldDetails['fieldLabel'];

			// Get saved values in relation with current field.
			$dbSettingsValuePreparedItem = $dbSettingsValuePrepared[$id] ?? [];
			if (!$dbSettingsValuePreparedItem) {
				continue;
			}

			// Iterate saved values and populate new fields.
			foreach ($dbSettingsValuePreparedItem as $itemKey => $itemValue) {
				switch ($itemKey) {
					case $this->integrationFieldStyle:
						$filterName = Filters::getBlockFilterName('field', 'styleOptions');
						$fieldStyle = \apply_filters($filterName, []);

						// If we want to provide.
						if (isset($fieldStyle[$component])) {
							$selectedItem = \array_filter(
								$fieldStyle[$component],
								static function ($item) use ($itemValue) {
									return $item['value'] === $itemValue;
								}
							);

							$selectedItem = \reset($selectedItem);

							if ($selectedItem) {
								$formFields[$key]["{$component}UseCustom"] = isset($selectedItem['useCustom']) ? (bool) $selectedItem['useCustom'] : true;
							}
						}

						$formFields[$key]["{$component}FieldStyle"] = $itemValue;
						break;
					case $this->integrationFieldOrder:
						$formFields[$key]["{$component}FieldOrder"] = $itemValue;
						break;
					case $this->integrationFieldUse:
						if ($fieldType !== 'hidden') {
							$formFields[$key]["{$component}FieldUse"] = \filter_var($itemValue, \FILTER_VALIDATE_BOOLEAN);
						}
						break;
					case $this->integrationFieldFileInfoLabel:
						if ($itemValue === 'true') {
							$formFields[$key]["{$component}FieldHideLabel"] = true;
							$formFields[$key]["{$component}CustomInfoText"] = $label;
						}
						break;
					case $this->integrationFieldLabel:
						if (!empty($itemValue)) {
							if ($component === 'submit') {
								$formFields[$key]["{$component}Value"] = $itemValue;
							} else {
								$formFields[$key]["{$component}FieldLabel"] = $itemValue;
							}
						}
						break;
					default:
						// Uppercase for output.
						$breakpointLabel = \ucfirst($itemKey);

						$formFields[$key]["{$component}FieldWidth{$breakpointLabel}"] = $itemValue;
						break;
				}
			}
		}

		return $formFields;
	}

	/**
	 * Get Integration forms Conditional tags details.
	 *
	 * @param string $key Key to save in db.
	 * @param array<int, array<string, mixed>> $formFields All form fields got from helper.
	 * @param string $formId Form ID.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getConditionalTagsFieldsDetails(string $key, array $formFields, string $formId): array
	{
		// Loop form fields.
		$fields = [];

		// Prepare all fields used in the component selector.
		$conditionalLogicRepeaterFields = \array_values(\array_filter(\array_map(
			function ($item) {
				$fieldDetails = $this->getFormFieldDetailsWithoutComponentName($item);

				if ($fieldDetails['inputType'] === 'hidden' || $fieldDetails['component'] === 'submit') {
					return false;
				}

				return [
					'label' => $fieldDetails['label'],
					'value' => $fieldDetails['id'],
				];
			},
			$formFields
		)));

		$inputValues = $this->getGroupDataWithoutKeyPrefix($this->getSettingsValueGroup($key, $formId));
		$componentValues = $this->getConditionalLogicRepeaterValue($inputValues);

		foreach ($formFields as $field) {
			if (!$field) {
				continue;
			}

			$fieldDetails = $this->getFormFieldDetailsWithoutComponentName($field);

			if ($fieldDetails['inputType'] === 'hidden' || $fieldDetails['component'] === 'submit') {
				continue;
			}

			$id = $fieldDetails['id'];
			$name = $fieldDetails['name'];
			$label = $fieldDetails['label'];

			$fields[] = [
				'component' => 'group',
				'groupLabel' => \ucfirst($label),
				'groupSaveOneField' => true,
				'groupContent' => [
					[
						'component' => 'conditional-logic-repeater',
						'groupLabel' => \ucfirst($label),
						'conditionalLogicRepeaterFieldLabel' => '',
						'conditionalLogicRepeaterName' => $name,
						'conditionalLogicRepeaterId' => $id,
						'conditionalLogicRepeaterFields' => $conditionalLogicRepeaterFields,
						'conditionalLogicRepeaterValue' => $componentValues[$id] ?? ['enabled' => false],
						'conditionalLogicRepeaterInputValue' => $inputValues[$id] ?? [],
					]
				],
			];
		}

		return $fields;
	}

	/**
	 * Convert nested array from filter to data in db.
	 *
	 * @param array<string, mixed> $data Data provided by filter for group.
	 *
	 * @return array<string, mixed>
	 */
	private function prepareFormViewDetails(array $data): array
	{
		$output = [];

		if (!$data) {
			return $output;
		}

		foreach ($data as $key => $values) {
			if (!$values) {
				continue;
			}

			foreach ($values as $itemKey => $itemValue) {
				if (\is_bool($itemValue)) {
					$itemValue = \wp_json_encode($itemValue);
				}

				$output["{$key}---{$itemKey}"] = $itemValue;
			}
		}

		return $output;
	}

	/**
	 * Sort fields by order value
	 *
	 * @param array <mixed> $a First key to find.
	 * @param array <mixed> $b First key to find.
	 *
	 * @return bool
	 */
	private function sortFields($a, $b): bool
	{
		/**
		 * The "Order" field immediately follows the breakpoint configuration, so we can use
		 * the number of breakpoints to determine the offset.
		 */

		$index = \count(Components::getSettingsGlobalVariablesBreakpoints());
		return $a['groupContent'][$index]['inputValue'] > $b['groupContent'][$index]['inputValue'];
	}

	/**
	 * Return Form field details but without component name in array key.
	 *
	 * @param array<string, mixed> $field Form field array.
	 *
	 * @return array<string, mixed>
	 */
	private function getFormFieldDetailsWithoutComponentName(array $field): array
	{
		$component = $field['component'] ? Components::kebabToCamelCase($field['component']) : '';

		return [
			'component' => $component,
			'id' => $field["{$component}Id"] ?? '',
			'name' => $field["{$component}Name"] ?? '',
			'label' => $field["{$component}FieldLabel"] ?? $field["{$component}Name"] ?? '',
			'fieldLabel' => $field["{$component}FieldLabel"] ?? '',
			'inputType' => $field["{$component}Type"] ?? '',
			'required' => $field["{$component}IsRequired"] ?? false,
		];
	}

	/**
	 * Prepare group data to have keys without the prefix
	 *
	 * @param array<string, mixed> $data Data to check.
	 *
	 * @return array<string, mixed>
	 */
	private function getGroupDataWithoutKeyPrefix(array $data): array
	{
		$output = [];
		foreach ($data as $key => $value) {
			$key = \explode('---', $key);

			if (!isset($key[1])) {
				continue;
			}

			if (!$value) {
				continue;
			}

			$output[$key[1]] = $value;
		}

		return $output;
	}

	/**
	 * Convert conditional logic repeater database values to values for the component.
	 *
	 * @param array<string, mixed> $fieldsValues Field values got from DB.
	 *
	 * @return array<string, mixed>
	 */
	private function getConditionalLogicRepeaterValue(array $fieldsValues): array
	{
		return \array_map(
			static function ($item) {
				$item = \json_decode($item);
				return [
					'enabled' => true,
					'behavior' => $item[0] ?? '',
					'logic' => $item[1] ?? '',
					'conditions' => \array_map(
						static function ($inner) {
							return [
								'field' => $inner[0] ?? '',
								'comparison' => $inner[1] ?? '',
								'value' => $inner[2] ?? '',
							];
						},
						$item[2] ?? []
					),
				];
			},
			$fieldsValues
		);
	}

	/**
	 * No active feature settings output.
	 *
	 * @param string $type Settings/Integration type.
	 *
	 * @return array<string, string>
	 */
	private function getIntroOutput(string $type): array
	{
		return [
			'component' => 'intro',
			'introTitle' => Filters::getSettingsLabels($type),
			'introSubtitle' => Filters::getSettingsLabels($type, 'desc'),
		];
	}

	/**
	 * No active feature settings output.
	 *
	 * @return array<int, array<string, string>>
	 */
	private function getNoActiveFeatureOutput(): array
	{
		return [
			[
				'component' => 'highlighted-content',
				'highlightedContentTitle' => \__('Feature not active', 'eightshift-forms'),
				// translators: %s will be replaced with the global settings url.
				'highlightedContentSubtitle' => \sprintf(\__('Oh no it looks like this feature is not active, please go to your <a href="%s">dashboard</a> and activate it.', 'eightshift-forms'), Helper::getSettingsGlobalPageUrl(SettingsDashboard::SETTINGS_TYPE_KEY)),
				'highlightedContentIcon' => 'tools',
			],
		];
	}

	/**
	 * No active feature settings output.
	 *
	 * @param string $type Settings/Integration type.
	 *
	 * @return array<int, array<string, string>>
	 */
	private function getNoValidGlobalConfigOutput(string $type): array
	{
		$label = Filters::getSettingsLabels($type);

		return [
			[
				'component' => 'highlighted-content',
				'highlightedContentTitle' => \__('Some config required', 'eightshift-forms'),
				// translators: %s will be replaced with the global settings url.
				'highlightedContentSubtitle' => \sprintf(\__('Before using %1$s you need to configure it in <a href="%2$s" target="_blank" rel="noopener noreferrer">global settings</a>.', 'eightshift-forms'), $label, Helper::getSettingsGlobalPageUrl($type)),
				'highlightedContentIcon' => 'tools',
			],
		];
	}

	/**
	 * No active feature settings output.
	 *
	 * @param string $type Settings/Integration type.
	 *
	 * @return array<int, array<string, string>>
	 */
	private function getNoIntegrationFetchDataOutput(string $type): array
	{
		$label = Filters::getSettingsLabels($type);

		return [
			[
				'component' => 'highlighted-content',
				'highlightedContentTitle' => \__('Something went wrong', 'eightshift-forms'),
				// translators: %s will be replaced with links.
				'highlightedContentSubtitle' => \sprintf(\__('
					We are sorry but we couldn\'t get any data from the external source. <br />
					Please go to %1$s <a href="%2$s" target="_blank" rel="noopener noreferrer">global settings</a> and check your API key.', 'eightshift-forms'), $label, Helper::getSettingsGlobalPageUrl($type)),
				'highlightedContentIcon' => 'error',
			],
		];
	}

	/**
	 * Applied filter settings output.
	 *
	 * @param string $name Filter name.
	 *
	 * @return string
	 */
	private function getAppliedFilterOutput(string $name): string
	{
		if (!\has_filter($name)) {
			return '';
		}

		$svg = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.157 16.801 8.673 2.522c.562-1.068 2.092-1.068 2.654 0l7.516 14.28A1.5 1.5 0 0 1 17.515 19H2.486a1.5 1.5 0 0 1-1.328-2.199z" stroke="currentColor" stroke-width="1.5" fill="none"></path><path d="M10 7.5v5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"></path><circle cx="10" cy="15.25" r="1" fill="currentColor"></circle></svg>';

		return '<br /> <span class="is-filter-applied">' . $svg . \__('This field has a code filter applied or a constant global set. Please be aware the filter output may override the output of this setting field.', 'eightshift-forms') . '</span>';
	}

	/**
	 * Applied Global Contant settings output.
	 *
	 * @param string $name Variable name.
	 *
	 * @return string
	 */
	private function getAppliedGlobalConstantOutput(string $name): string
	{
		// translators: %s replaces global variable name.
		return \sprintf(\__('Global variable "%s" is active.', 'eightshift-forms'), $name);
	}

	/**
	 * Output array - conditional tags.
	 *
	 * @param string $formId Form ID.
	 * @param array<int, array<string, mixed>> $formFields Items from cache data.
	 * @param string $key Settings key used.
	 *
	 * @return array<string, array<int, array<string, array<int, array<string, mixed>>|string>>|string>
	 */
	private function getOutputConditionalTags(string $formId, array $formFields, string $key): array
	{
		return [
			'component' => 'tab',
			'tabLabel' => \__('Conditional logic', 'eightshift-forms'),
			'tabContent' => [
				[
					'component' => 'intro',
					'introSubtitle' => \__('In these setting, you can provide conditional tags for fields and their relationships. <br />', 'eightshift-forms'),
				],
				[
					'component' => 'group',
					'groupId' => $this->getSettingsName($key),
					'groupStyle' => 'default-listing-divider',
					'groupContent' => $this->getConditionalTagsFieldsDetails(
						$key,
						$formFields,
						$formId
					),
				],
			],
		];
	}

	/**
	 * Output array - integration fields.
	 *
	 * @param string $formId Form ID.
	 * @param array<int, array<string, mixed>> $formFields Items from cache data.
	 * @param string $settingsType Settings type used.
	 * @param string $key Settings key used.
	 * @param array<int, string> $additional Additional settins key to add to the integration output.
	 *
	 * @return array<string, array<int, array<string, mixed>>|string>
	 */
	private function getOutputIntegrationFields(
		string $formId,
		array $formFields,
		string $settingsType,
		string $key,
		array $additional = []
	): array {
		$beforeContent = '';

		$filterName = Filters::getIntegrationFilterName($settingsType, 'adminFieldsSettings');
		if (\has_filter($filterName)) {
			$beforeContent = \apply_filters($filterName, '') ?? '';
		}

		$sortingButton = Components::render('sorting');

		$formViewDetailsIsEditableFilterName = Filters::getIntegrationFilterName($settingsType, 'fieldsSettingsIsEditable');
		if (\has_filter($formViewDetailsIsEditableFilterName)) {
			$sortingButton = \__('This integration sorting and editing is disabled because of the active filter in your project!', 'eightshift-forms');
		}

		$integration = $this->getIntegrationFieldsDetails(
			$key,
			$settingsType,
			$formFields,
			$formId,
			$additional
		);

		$hiddenFields = '';
		if ($integration['hiddenFields']) {
			$hiddenFields .= \__('<br />You have some additional hidden fields defined in the form. These fields will also be added to the frontend form and sent via API:', 'eightshift-forms');
			$hiddenFields .= '<ul>';
			foreach ($integration['hiddenFields'] as $hidden) {
				$hiddenFields .= "<li>{$hidden}</li>";
			}
			$hiddenFields .= '</ul>';
		}

		return [
			'component' => 'tab',
			'tabLabel' => \__('Integration fields', 'eightshift-forms'),
			'tabContent' => [
				[
					'component' => 'intro',
					// translators: %s replaces the button or string.
					'introSubtitle' => \sprintf(\__('
						In these setting, you can provide additional configuration for all the integration fields. <br />
						If you want to change the fields order click on the button below. Please remember to save the new order, by clicking click on the "save settings" button at the bottom of the page. <br /><br />
						%1$s %2$s', 'eightshift-forms'), $sortingButton, $hiddenFields),
				],
				[
					'component' => 'group',
					'groupId' => $this->getSettingsName($key),
					'groupBeforeContent' => $beforeContent,
					'additionalGroupClass' => Components::getComponent('sorting')['componentCombinedClass'],
					'groupStyle' => 'default-listing-divider',
					'groupContent' => $integration['fields'],
				],
			],
		];
	}

	/**
	 * Output array - form selection.
	 *
	 * @param string $formId Form ID.
	 * @param array<string, mixed> $items Items from cache data.
	 * @param string $selectedFormId Selected form id.
	 * @param string $settingsType Settings type used.
	 * @param string $key Settings key used.
	 *
	 * @return array<int, array<string, array<int|string, array<string, mixed>>|bool|string>>
	 */
	private function getOutputFormSelection(
		string $formId,
		array $items,
		string $selectedFormId,
		string $settingsType,
		string $key
	): array {
		$manifestForm = Components::getComponent('form');

		$lastUpdatedTime = $items[ClientInterface::TRANSIENT_STORED_TIME]['title'] ?? '';
		unset($items[ClientInterface::TRANSIENT_STORED_TIME]);

		return [
			[
				'component' => 'select',
				'selectName' => $this->getSettingsName($key),
				'selectId' => $this->getSettingsName($key),
				'selectFieldLabel' => \__('Selected integration form', 'eightshift-forms'),
				// translators: %1$s will be replaced with js selector, %2$s will be replaced with the cache type, %3$s will be replaced with latest update time.
				'selectFieldHelp' => \sprintf(\__('If a form isn\'t showing up or is missing some items, try <a href="#" class="%1$s" data-type="%2$s">clearing the cache</a>. Last updated: %3$s.', 'eightshift-forms'), $manifestForm['componentCacheJsClass'], $settingsType, $lastUpdatedTime),
				'selectOptions' => \array_merge(
					[
						[
							'component' => 'select-option',
							'selectOptionLabel' => '',
							'selectOptionValue' => '',
						]
					],
					\array_map(
						function ($option) use ($formId, $key) {
							return [
								'component' => 'select-option',
								'selectOptionLabel' => $option['title'] ?? '',
								'selectOptionValue' => $option['id'] ?? '',
								'selectOptionIsSelected' => $this->isCheckedSettings($option['id'], $key, $formId),
							];
						},
						$items
					)
				),
				'selectIsRequired' => true,
				'selectValue' => $selectedFormId,
				'selectSingleSubmit' => true,
			],
		];
	}

	/**
	 * Output array - form selection additional.
	 *
	 * @param string $formId Form ID.
	 * @param array<string, mixed> $items Items from cache data.
	 * @param string $selectedFormId Selected form id.
	 * @param string $key Settings key used.
	 *
	 * @return array<int, array<string, array<int|string, array<string, mixed>>|bool|string>>
	 */
	private function getOutputFormSelectionAdditional(
		string $formId,
		array $items,
		string $selectedFormId,
		string $key
	): array {
		return [
			[
				'component' => 'select',
				'selectName' => $this->getSettingsName($key),
				'selectId' => $this->getSettingsName($key),
				'selectFieldLabel' => \__('Selected integration sub form', 'eightshift-forms'),
				'selectOptions' => \array_merge(
					[
						[
							'component' => 'select-option',
							'selectOptionLabel' => '',
							'selectOptionValue' => '',
						]
					],
					\array_map(
						function ($option) use ($formId, $key) {
							return [
								'component' => 'select-option',
								'selectOptionLabel' => $option['title'] ?? '',
								'selectOptionValue' => $option['id'] ?? '',
								'selectOptionIsSelected' => $this->isCheckedSettings($option['id'], $key, $formId),
							];
						},
						$items
					)
				),
				'selectIsRequired' => true,
				'selectValue' => $selectedFormId,
				'selectSingleSubmit' => true,
			],
		];
	}
}
