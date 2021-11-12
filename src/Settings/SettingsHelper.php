<?php

/**
 * Trait that holds all generic helpers used in classes.
 *
 * @package EightshiftLibs\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings;

use EightshiftForms\Config\Config;
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
	 * @param bool $useCleanKey Is true prefix and locale will be added to the key.
	 *
	 * @return string
	 */
	public function getSettingsValue(string $key, string $formId, bool $useCleanKey = false): string
	{
		global $wpdb;

		if (!$useCleanKey) {
			$key = $this->getSettingsName($key);
		}

		$tableName = "{$wpdb->prefix}" . Config::getDbSettingsName();

		$result = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT meta_value
				FROM %s
				WHERE (meta_key = %s AND post_id = %d)",
				$tableName,
				$key,
				$formId
			),
			ARRAY_A
		);

		return (string) isset($result['meta_value']) ? $result['meta_value'] : '';
	}

	/**
	 * Update settings value
	 *
	 * @param string $key Providing string to append to.
	 * @param string $value Value to store.
	 * @param string $formId Form Id.
	 *
	 * @return void
	 */
	public function updateSettingsValue(string $key, string $value, string $formId): void
	{
		global $wpdb;

		$tableName = "{$wpdb->prefix}" . Config::getDbSettingsName();

		$data = [
			'data' => [
				'post_id' => (int) $formId,
				'meta_key' => (string) $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value' => (string) $value, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			],
			'formats' => [
				'%d',
				'%s',
				'%s',
			],
		];

		$item = $this->getSettingsValue($key, $formId, true);

		if (!$item) {
			$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$tableName,
				$data['data'],
				$data['formats']
			);
		} else {
			$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$tableName,
				$data['data'],
				[
					'post_id' => (int) $formId,
					'meta_key' => (string) $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				],
				$data['formats'],
				[
					'%d',
					'%s',
				]
			);
		}
	}

	/**
	 * Delete settings value
	 *
	 * @param string $key Providing string to append to.
	 * @param string $formId Form Id.
	 *
	 * @return void
	 */
	public function deleteSettingsValue(string $key, string $formId): void
	{
		global $wpdb;

		$tableName = "{$wpdb->prefix}" . Config::getDbSettingsName();

		$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$tableName,
			[
				'post_id' => (int) $formId,
				'meta_key' => (string) $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			],
			[
				'%d',
				'%s',
			]
		);
	}

	/**
	 * Get option value.
	 *
	 * @param string $key Providing string to append to.
	 * @param bool $useCleanKey Is true prefix and locale will be added to the key.
	 *
	 * @return string
	 */
	public function getOptionsValue(string $key, bool $useCleanKey = false): string
	{
		global $wpdb;

		if (!$useCleanKey) {
			$key = $this->getSettingsName($key);
		}

		$tableName = "{$wpdb->prefix}" . Config::getDbOptionsName();

		$result = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT option_value
				FROM %s
				WHERE option_name = %s",
				$tableName,
				$key
			),
			ARRAY_A
		);

		return (string) isset($result['option_value']) ? $result['option_value'] : '';
	}

	/**
	 * Update options value.
	 *
	 * @param string $key Providing string to append to.
	 * @param string $value Value to store.
	 *
	 * @return void
	 */
	public function updateOptionsValue(string $key, string $value): void
	{
		global $wpdb;

		$tableName = "{$wpdb->prefix}" . Config::getDbOptionsName();

		$data = [
			'data' => [
				'option_name' => (string) $key,
				'option_value' => (string) $value,
			],
			'formats' => [
				'%s',
				'%s',
			],
		];

		$item = $this->getOptionsValue($key, true);

		if (!$item) {
			$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$tableName,
				$data['data'],
				$data['formats']
			);
		} else {
			$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$tableName,
				$data['data'],
				[
					'option_name' => (string) $key,
				],
				$data['formats'],
				[
					'%s',
				]
			);
		}
	}

	/**
	 * Delete options value.
	 *
	 * @param string $key Providing string to append to.
	 *
	 * @return void
	 */
	public function deleteOptionsValue(string $key): void
	{
		global $wpdb;

		$tableName = "{$wpdb->prefix}" . Config::getDbOptionsName();

		$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$tableName,
			[
				'option_name' => (string) $key,
			],
			[
				'%s',
			]
		);
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
		$value = $this->getSettingsValue($key, $formId);

		if (!$value) {
			return [];
		}

		return json_decode($value, true);
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
		return $this->getOptionsValue($id) === $key;
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
		return in_array($key, explode(', ', $this->getOptionsValue($id)), true);
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

	/**
	 * Create our custom tables, used on plugin activation.
	 *
	 * @return void
	 */
	public function createDbTables(): void
	{
		global $wpdb;

		$tableNameSettings = "{$wpdb->prefix}" . Config::getDbSettingsName();
		$tableNameOptions = "{$wpdb->prefix}" . Config::getDbOptionsName();

		$sqlSettings = "CREATE TABLE {$tableNameSettings} ( 
			meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
			meta_key VARCHAR(255) NULL DEFAULT NULL,
			meta_value LONGTEXT NULL DEFAULT NULL,
			PRIMARY KEY (meta_id),
			INDEX post_id (post_id),
			INDEX meta_key (meta_key)
		)";

		$sqlOptions = "CREATE TABLE {$tableNameOptions} ( 
			option_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			option_name VARCHAR(255) NULL DEFAULT NULL,
			option_value LONGTEXT NULL DEFAULT NULL,
			PRIMARY KEY (option_id),
			INDEX option_name (option_name)
		)";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		dbDelta([
			$sqlSettings,
			$sqlOptions,
		]);
	}
}
