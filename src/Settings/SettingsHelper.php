<?php

/**
 * Trait that holds all generic helpers used in classes.
 *
 * @package EightshiftLibs\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings;

use EightshiftForms\Helpers\Components;

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
	 * Determin if settings is checked (used for radio, and select box).
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
	 * Determin if global is checked (used for radio, and select box).
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
	 * Determin if checkbox settings is checked (used for checkbox).
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
	 * Determin if checkbox global is checked (used for checkbox).
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
	 * Get Integration forms Fields details (used to set field width for rensponsive)
	 *
	 * @param string $key Key to save in db.
	 * @param array<int, array<string, mixed>> $formFields All form fields got from helper.
	 * @param string $formId Form ID.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getIntegrationFieldsDetails(string $key, array $formFields, string $formId): array
	{
		$globalManifest = Components::getManifest(dirname(__DIR__, 1) . '/Blocks');

		// Find project breakpoints.
		$breakpoints = array_flip($globalManifest['globalVariables']['breakpoints']);

		// Loop form fields.
		$fields = [];

		$fieldsValues = $this->getSettingsValueGroup($key, $formId);
		$fieldsKey = $this->getSettingsName($key);

		foreach ($formFields as $field) {
			$component = $field['component'];

			// Skip submit.
			if ($component === 'submit') {
				continue;
			}

			$id = $field["{$component}Id"] ?? '';
			$label = $field["{$component}FieldLabel"] ?? $field["{$component}Name"] ?? '';
			$fieldsOutput = [
				[
					'component' => 'group',
					'groupLabel' => $label,
					'groupName' => $fieldsKey,
					'groupIsInner' => true,
					'groupContent' => [],
				]
			];

			// Loop breakpoints and output inputs.
			$i = 0;
			foreach ($breakpoints as $breakpoint) {
				$item = [
					'component' => 'input',
					'inputId' => "{$id}---{$breakpoint}",
					'inputFieldLabel' => ucfirst($breakpoint),
					'inputType' => 'number',
					'inputValue' => $fieldsValues["{$id}---{$breakpoint}"] ?? '',
				];

				$fieldsOutput[0]['groupContent'][] = $item;

				$i++;
			}

			$fields = array_merge($fields, $fieldsOutput);
		}

		return $fields;
	}

	/**
	 * Build integration fields value output with full component array.
	 *
	 * @param array<string, mixed> $fields Field to search settings in.
	 * @param array<string, mixed> $fullField Fill component array.
	 *
	 * @return array<string, mixed>
	 */
	public function getIntegrationFieldsValue(array $fields, array $fullField): array
	{
		$output = $fullField;

		foreach ($fields as $fieldKey => $fieldValue) {
			$item = explode('---', $fieldKey);

			if ($fullField["{$fullField['component']}Id"] !== $item[0]) {
				continue;
			}

			$breakpoint = ucfirst($item[1]);

			$output["{$fullField['component']}FieldWidth{$breakpoint}"] = $fieldValue;
		}

		return $output;
	}
}
