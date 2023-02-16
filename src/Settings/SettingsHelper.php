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
}
