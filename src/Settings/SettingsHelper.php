<?php

/**
 * Trait that holds all generic helpers used in classes.
 *
 * @package EightshiftLibs\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\Settings\SettingsDashboard;

/**
 * SettingsHelper trait.
 */
trait SettingsHelper
{
	/**
	 * Get settings value.
	 *
	 * @param string $key Key to find in db settings.
	 * @param string $formId Form Id.
	 *
	 * @return string
	 */
	public function getSettingsValue(string $key, string $formId): string
	{
		return (string) \get_post_meta((int) $formId, $this->getSettingsName($key), true);
	}

	/**
	 * Get option value string saved as json array - used for textarea with : delimiter.
	 *
	 * @param string $key Providing string to append to.
	 * @param string $formId Form Id.
	 * @param int $useNumber Number of items to use.
	 *
	 * @return string
	 */
	public function getSettingsValueAsJson(string $key, string $formId, int $useNumber = 2): string
	{
		$values = $this->getSettingsValueGroup($key, $formId);
		if (!$values) {
			return '';
		}

		return $this->getSavedValueAsJson($values, $useNumber);
	}

	/**
	 * Get settings value with fallback.
	 *
	 * @param string $key Key to find in db settings.
	 * @param string $optionKey Key to find in db options.
	 * @param string $fallback Fallback value.
	 * @param string $formId Form Id.
	 *
	 * @return string
	 */
	public function getSettingsValueWithFallback(string $key, string $optionKey, string $fallback, string $formId): string
	{
		$value = $this->getSettingsValue($key, $formId);

		if (!$value) {
			return $this->getOptionValue($optionKey);
		}

		return $value;
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
	 * Get option value array.
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
	 * Get option value with fallback.
	 *
	 * @param string $key Key to find in db settings.
	 * @param string $fallback Fallback value.
	 *
	 * @return string
	 */
	public function getOptionValueWithFallback(string $key, string $fallback): string
	{
		$value = $this->getOptionValue($key);

		if (!$value) {
			return $fallback;
		}

		return $value;
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
	 * Get option value string saved as json array - used for textarea with : delimiter.
	 *
	 * @param string $key Providing string to append to.
	 * @param int $useNumber Number of items to use.
	 *
	 * @return string
	 */
	public function getOptionValueAsJson(string $key, int $useNumber = 2): string
	{
		$values = $this->getOptionValueGroup($key);
		if (!$values) {
			return '';
		}

		return $this->getSavedValueAsJson($values, $useNumber);
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

		return \explode(AbstractBaseRoute::DELIMITER, $value);
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
		return \in_array($key, \explode(AbstractBaseRoute::DELIMITER, $this->getSettingsValue($id, $formId)), true);
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
		return \in_array($key, \explode(AbstractBaseRoute::DELIMITER, $this->getOptionValue($id)), true);
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
		$filterName = Filters::getFilterName(['general', 'setLocale']);

		if (\has_filter($filterName)) {
			$locale = \apply_filters($filterName, $locale);
		}

		return $locale;
	}

	/**
	 * Get saved value string saved as json array - used for textarea with : delimiter.
	 *
	 * @param array<string, mixed> $values Values provided from settings.
	 * @param int $useNumber Number of items to use.
	 *
	 * @return string
	 */
	public function getSavedValueAsJson(array $values, int $useNumber = 2): string
	{
		$output = [];
		$i = 1;
		foreach ($values as $value) {
			if (!$value) {
				continue;
			}

			$value = \array_filter(
				$value,
				static function ($item) use ($useNumber) {
					return $item <= $useNumber - 1;
				},
				\ARRAY_FILTER_USE_KEY
			);

			// Remove keys that are note set properly.
			if (\count($value) < $useNumber) {
				continue;
			}

			$output[] = \implode(' : ', $value);

			$i++;
		}

		return \implode(\PHP_EOL, $output);
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
	 * Applied Global constant settings output.
	 *
	 * @param string $name Variable name.
	 *
	 * @return string
	 */
	private function getAppliedGlobalConstantOutput(string $name): string
	{
		// translators: %s replaces global variable name.
		return '<span class="is-filter-applied">' . \sprintf(\__('Enabled with a global variable <code>%s</code>', 'eightshift-forms'), $name) . '</span>';
	}

	/**
	 * Get all active integration on specific form.
	 *
	 * @param string $id Form Id.
	 *
	 * @return array<string, string>
	 */
	private function getActiveIntegrationIcons(string $id): array
	{
		$integrationDetails = Helper::getFormDetailsById($id);

		if (!$integrationDetails) {
			return [];
		}

		$type = $integrationDetails['typeFilter'];
		$useFilter = Filters::ALL[$type]['use'] ?? '';

		return [
			'label' => $integrationDetails['label'],
			'icon' => $integrationDetails['icon'],
			'value' => $type,
			'isActive' => $useFilter ? $this->isCheckboxOptionChecked($useFilter, $useFilter) : false,
			'isValid' => $integrationDetails['isValid'],
			'isApiValid' => $integrationDetails['isApiValid'],
		];
	}

	/**
	 * Get global variable copy output.
	 *
	 * @param string $variableName Variable name to output.
	 * @param bool $usedVariable Is global variable used.
	 *
	 * @return string
	 */
	private function getGlobalVariableOutput(string $variableName, bool $usedVariable = false): string
	{
		// translators: %s will be replaced with global variable name.
		$output = \sprintf(\__('
			<details class="is-filter-applied">
				<summary>Available global variables</summary>
				<ul>
					<li>%s</li>
				</ul>
				<br />
				This field value can also be set using a global variable via code.
			</details>', 'eightshift-forms'), $variableName);

		if ($usedVariable) {
			$output = '<span class="is-filter-applied">' . \__('This field value is set with a global variable via code.', 'eightshift-forms') . '</span>';
		}

		return $output;
	}

	/**
	 * Get response tags output copy.
	 *
	 * @param string $formFieldTags Response tags to output.
	 *
	 * @return string
	 */
	public static function getFieldTagsOutput(string $formFieldTags): string
	{
		if (!$formFieldTags) {
			return '';
		}

		// translators: %s will be replaced with form field names.
		return \sprintf(\__('
			Use template tags to use submitted form data (e.g. <code>{field-name}</code>)
			<details class="is-filter-applied">
				<summary>Available tags</summary>
				<ul>
					%s
				</ul>
				<br />
				Tag missing? Make sure its field has a <b>Name</b> set!
			</details>', 'eightshift-forms'), $formFieldTags);
	}

	/**
	 * Get response tags copy output.
	 *
	 * @param string $formResponseTags Response tags to output.
	 *
	 * @return string
	 */
	public static function getResponseTagsOutput(string $formResponseTags): string
	{
		if (!$formResponseTags) {
			return '';
		}

		// translators: %s will be replaced with integration response tags.
		return \sprintf(\__('
			<details class="is-filter-applied">
				<summary>Response tags</summary>
				<ul>
					%s
				</ul>
				<br />
				Use response tags to populate the content with the data that the integration sends back.
			</details>', 'eightshift-forms'), $formResponseTags);
	}

	/**
	 * Get all field names from the form.
	 *
	 * @param array<int, string> $fieldNames Form field IDs.
	 *
	 * @return string
	 */
	public static function getFormFieldNames(array $fieldNames): string
	{
		$output = [];

		// Populate output.
		foreach ($fieldNames as $item) {
			$output[] = "<li><code>{" . $item . "}</code></li>";
		}

		return \implode("\n", $output);
	}

	/**
	 * Get all field names from the form.
	 *
	 * @param string $formType Form type to check.
	 *
	 * @return string
	 */
	public static function getFormResponseTags(string $formType): string
	{
		$tags = Filters::ALL[$formType]['emailTemplateTags'] ?? [];

		if ($tags) {
			return self::getFormFieldNames($tags);
		}

		return '';
	}
}
