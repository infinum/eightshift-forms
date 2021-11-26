<?php

/**
 * Trait that holds all generic helpers used in classes.
 *
 * @package EightshiftLibs\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings;

use EightshiftForms\Helpers\Components;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;

/**
 * SettingsHelper trait.
 */
trait SettingsHelper
{
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
		return in_array($key, explode(', ', $this->getSettingsValue($id, $formId)), true);
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
		return in_array($key, explode(', ', $this->getOptionValue($id)), true);
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
		$locale = get_locale();

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
		$additionalLabel = array_flip($additionalLabel);
		$globalManifest = Components::getManifest(dirname(__DIR__, 1) . '/Blocks');

		// Find project breakpoints.
		$breakpoints = array_flip($globalManifest['globalVariables']['breakpoints']);

		// Loop form fields.
		$fields = [];

		$fieldsValues = $this->getSettingsValueGroup($key, $formId);
		$fieldsKey = $this->getSettingsName($key);
		$totalFields = count($formFields);

		foreach ($formFields as $fieldKey => $field) {
			if (!$field) {
				continue;
			}

			$component = $field['component'] ?? '';

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
					'groupLabel' => ucfirst($label),
					'groupName' => $fieldsKey,
					'groupIsInner' => true,
					'groupContent' => [],
				]
			];

			// Breakpoints.
			$i = 0;
			foreach ($breakpoints as $breakpoint) {
				$item = [
					'component' => 'input',
					'inputId' => "{$id}---{$breakpoint}",
					'inputFieldLabel' => ucfirst($breakpoint),
					'inputType' => 'number',
					'inputValue' => $fieldsValues["{$id}---{$breakpoint}"] ?? '',
					'inputMin' => 0,
					'inputMax' => 12,
					'inputStep' => 1,
				];

				$fieldsOutput[0]['groupContent'][] = $item;

				$i++;
			}

			// Order.
			$fieldsOutput[0]['groupContent'][] = [
				'component' => 'input',
				'inputId' => "{$id}---order",
				'inputFieldLabel' => __('Order', 'eightshift-forms'),
				'inputType' => 'number',
				'inputValue' => $fieldsValues["{$id}---order"] ?? $fieldKey + 1,
				'inputMin' => 1,
				'inputMax' => $totalFields,
				'inputStep' => 1,
			];

			// Use.
			$toggleValue = $fieldsValues["{$id}---use"] ?? '';
			$toggleDisabled = $required;

			if ($type === SettingsGreenhouse::SETTINGS_TYPE_KEY && ($id === 'resume_text' || $id === 'cover_letter_text')) {
				$toggleDisabled = false;
			}

			$fieldsOutput[0]['groupContent'][] = [
				'component' => 'select',
				'selectId' => "{$id}---use",
				'selectFieldLabel' => __('Show/Hide', 'eightshift-forms'),
				'selectValue' => $toggleValue,
				'selectIsDisabled' => $toggleDisabled,
				'selectOptions' => [
					[
						'component' => 'select-option',
						'selectOptionLabel' => __('Show', 'eightshift-forms'),
						'selectOptionValue' => 'true',
						'selectOptionIsSelected' => $toggleValue === 'true',
					],
					[
						'component' => 'select-option',
						'selectOptionLabel' => __('Hide', 'eightshift-forms'),
						'selectOptionValue' => 'false',
						'selectOptionIsSelected' => $toggleValue === 'false',
					]
				]
			];

			if ($component === 'file') {
				$fileInfoLabelValue = $fieldsValues["{$id}---file-info-label"] ?? '';

				$fieldsOutput[0]['groupContent'][] = [
					'component' => 'select',
					'selectId' => "{$id}---file-info-label",
					'selectFieldLabel' => __('Label as infobox text', 'eightshift-forms'),
					'selectValue' => $fileInfoLabelValue,
					'selectOptions' => [
						[
							'component' => 'select-option',
							'selectOptionLabel' => __('Not use', 'eightshift-forms'),
							'selectOptionValue' => 'false',
							'selectOptionIsSelected' => $fileInfoLabelValue === 'false',
						],
						[
							'component' => 'select-option',
							'selectOptionLabel' => __('Use', 'eightshift-forms'),
							'selectOptionValue' => 'true',
							'selectOptionIsSelected' => $fileInfoLabelValue === 'true',
						]
					]
				];
			}

			// Submit label.
			if (isset($additionalLabel[$id]) || $component === 'submit') {
				$fieldsOutput[0]['groupContent'][] = [
					'component' => 'input',
					'inputId' => "{$id}---label",
					'inputFieldLabel' => __('Label', 'eightshift-forms'),
					'inputValue' => $fieldsValues["{$id}---label"] ?? '',
				];
			}

			$fields = array_merge($fields, $fieldsOutput);
		}

		return $fields;
	}

	/**
	 * Build integration fields value output with full component array.
	 *
	 * @param array<string, mixed> $dbSettingsValue Field to search in settings.
	 * @param array<int, array<string, mixed>> $formFields Full form components array.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getIntegrationFieldsValue(array $dbSettingsValue, array $formFields): array
	{
		if (!$dbSettingsValue) {
			return $formFields;
		}

		// Get value saved in the db and loop all fields inside.
		$dbSettingsValuePrepared = [];
		foreach ($dbSettingsValue as $key => $value) {
			$item = explode('---', $key);

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
			$component = $value['component'] ?? '';

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
					case 'order':
						$formFields[$key]["{$component}FieldOrder"] = $itemValue;
						break;
					case 'use':
						$formFields[$key]["{$component}FieldUse"] = filter_var($itemValue, FILTER_VALIDATE_BOOLEAN);
						break;
					case 'file-info-label':
						if ($itemValue === 'true') {
							$formFields[$key]["{$component}CustomInfoButtonTextUse"] = false;
							$formFields[$key]["{$component}FieldHideLabel"] = true;
							$formFields[$key]["{$component}CustomInfoText"] = $label;
						}
						break;
					case 'label':
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
						$breakpointLabel = ucfirst($itemKey);

						$formFields[$key]["{$component}FieldWidth{$breakpointLabel}"] = $itemValue;
						break;
				}
			}
		}

		return $formFields;
	}
}
