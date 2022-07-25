<?php

/**
 * Trait that holds all generic helpers used in classes.
 *
 * @package EightshiftLibs\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings;

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;

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
	 * Set locale depending ond default locale or hook override.
	 *
	 * @return string
	 */
	public function getLocale(): string
	{
		$locale = \get_locale();

		if (\has_filter('es_forms_set_locale')) {
			$locale = \apply_filters('es_forms_set_locale', $locale);
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
	 * @return array<int, array<string, mixed>>
	 */
	public function getIntegrationFieldsDetails(string $key, string $type, array $formFields, string $formId, array $additionalLabel = []): array
	{
		$additionalLabel = \array_flip($additionalLabel);

		// Find project breakpoints.
		$breakpoints = \array_flip(Components::getSettingsGlobalVariablesBreakpoints());

		// Loop form fields.
		$fields = [];

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

		$totalFields = \count($formFields);

		$filterName = Filters::getBlockFilterName('field', 'styleOptions');
		$fieldStyle = \apply_filters($filterName, []);

		foreach ($formFields as $fieldKey => $field) {
			if (!$field) {
				continue;
			}

			$component = $field['component'] ? Components::kebabToCamelCase($field['component']) : '';

			$inputType = $field["{$component}Type"] ?? '';
			if ($inputType === 'hidden') {
				continue;
			}

			$id = $field["{$component}Id"] ?? '';
			$required = $field["{$component}IsRequired"] ?? false;
			$label = $field["{$component}FieldLabel"] ?? $field["{$component}Name"] ?? '';

			if ($type === SettingsGreenhouse::SETTINGS_TYPE_KEY && ($id === 'resume_text' || $id === 'cover_letter_text')) {
				$label = "{$label} Text";
			}

			$fieldsOutput = [
				[
					'component' => 'group',
					'groupLabel' => \ucfirst($label),
					'groupSaveOneField' => true,
					'groupStyle' => 'integration-inner',
					'groupContent' => [],
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
				];

				$fieldsOutput[0]['groupContent'][] = $item;

				$i++;
			}

			// Order.
			$fieldsOutput[0]['groupContent'][] = [
				'component' => 'input',
				'inputId' => "{$id}---{$this->integrationFieldOrder}",
				'inputFieldLabel' => \__('Order', 'eightshift-forms'),
				'inputType' => 'number',
				'inputValue' => $fieldsValues["{$id}---{$this->integrationFieldOrder}"] ?? $fieldKey + 1,
				'inputMin' => 1,
				'inputMax' => $totalFields,
				'inputStep' => 1,
				'inputIsDisabled' => $disabledEdit,
				'inputPlaceholder' => \__('auto', 'eightshift-forms'),
				'inputFieldUseTooltip' => true,
				'inputFieldTooltipContent' => \__('Define field order that is going to be used.', 'eightshift-forms'),
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
				]
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
					]
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
				];
			}

			$fields = \array_merge($fields, $fieldsOutput);
		}

		\usort($fields, [$this, 'sortFields']);

		return $fields;
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

			// Find field component name.
			$component = $value['component'] ? Components::kebabToCamelCase($value['component']) : '';

			// Find field id.
			$id = $value["{$component}Id"] ?? '';

			// Find field label.
			$label = $value["{$component}FieldLabel"] ?? '';

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
						$formFields[$key]["{$component}FieldUse"] = \filter_var($itemValue, \FILTER_VALIDATE_BOOLEAN);
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
		$index = count(Components::getSettingsGlobalVariablesBreakpoints());
		return $a['groupContent'][$index]['inputValue'] > $b['groupContent'][$index]['inputValue'];
	}
}
