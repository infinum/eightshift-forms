<?php

/**
 * Class that holds data for mocking filters output used in seeting for better UX.
 *
 * @package EightshiftForms\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\Settings\SettingsGeneral;

/**
 * FiltersOuputMock trait.
 */
trait FiltersOuputMock
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Return enrichment manual map data filter output.
	 *
	 * @param array<string, mixed> $config getEnrichmentConfig output value.
	 *
	 * @return array<string, mixed>
	 */
	public function getEnrichmentManualMapFilterValue(array $config): array
	{
		$settings = '';
		$data = [
			'config' => $config,
			'allowedFilter' => [],
			'mapFilter' => [],
		];
		$filterUsed = false;

		$filterName = Filters::getFilterName(['enrichment', 'manualMap']);

		if (\has_filter($filterName)) {
			$filterData = \apply_filters($filterName, '');
			$filterUsed = true;

			// Output map depending on the type.
			if ($filterData) {
				foreach ($filterData as $key => $value) {
					$data['config']['allowed'][] = $key;
					$data['config']['map'][$key] = \array_flip($value);
				}
			}

			$settings .= \__('This field has a code filter applied to it, and the following items will be applied to your enrichment data:', 'eightshift-forms');
			$settings .= '<ul>';
			foreach ($filterData as $key => $value) {
				$settingsValue = \implode(', ', $value);

				$settings .= "<li><code>{$key}</code> : <code>{$settingsValue}</code></li>";
			}
			$settings .= '</ul>';
		}

		return [
			'settings' => $this->getSettingsDivWrap($settings, $filterUsed, false),
			'data' => [
				'original' => $config,
				'config' => $data['config'],
			],
			'filterUsed' => $filterUsed,
		];
	}

	/**
	 * Return success redirect variations options data filter output.
	 *
	 * @return array<string, mixed>
	 */
	public function getSuccessRedirectVariationOptionsFilterValue(): array
	{
		$settings = '';
		$data = '';
		$filterData = [];
		$filterUsed = false;

		$filterName = Filters::getFilterName(['block', 'form', 'successRedirectVariationOptions']);
		if (\has_filter($filterName)) {
			$filterData = \apply_filters($filterName, []);

			if ($filterData) {
				$settings .= \__('This field has a code filter applied to it, and the following items will be applied to the output:', 'eightshift-forms');
				$settings .= '<ul>';
				foreach ($filterData as $value) {
					$settings .= "<li><code>{$value[0]}</code> : <code>{$value[1]}</code></li>";
				}
				$settings .= '</ul>';
			}

			$filterUsed = true;
		}

		$data = [
			...$this->getOptionValueGroup(SettingsGeneral::SETTINGS_GENERAL_SUCCESS_REDIRECT_VARIATION_OPTIONS_KEY),
			...$filterData,
		];

		return [
			'settings' => $this->getSettingsDivWrap($settings, $filterUsed, false),
			'data' => $data,
			'filterUsed' => $filterUsed,
		];
	}

	/**
	 * Return success redirect variations data filter output.
	 *
	 * @param string $type Type of integration.
	 * @param string $formId Form ID.
	 *
	 * @return array<string, mixed>
	 */
	public function getSuccessRedirectVariationFilterValue(string $type, string $formId): array
	{
		$settings = '';
		$data = '';
		$filterUsed = false;

		$filterName = Filters::getFilterName(['block', 'form', 'successRedirectVariation']);
		if (\has_filter($filterName)) {
			$data = \apply_filters($filterName, $type, $formId);
			$filterUsed = true;
		} else {
			$data = $this->getSettingsValue(SettingsGeneral::SETTINGS_GENERAL_SUCCESS_REDIRECT_VARIATION_KEY, $formId);
		}

		return [
			'settings' => $this->getSettingsDivWrap($settings, $filterUsed),
			'data' => $data,
			'filterUsed' => $filterUsed,
		];
	}

	/**
	 * Return success redirect url data filter output.
	 *
	 * @param string $type Type of integration.
	 * @param string $formId Form ID.
	 *
	 * @return array<string, mixed>
	 */
	public function getSuccessRedirectUrlFilterValue(string $type, string $formId): array
	{
		$settingsGlobal = '';
		$settingsLocal = '';
		$data = '';
		$dataGlobal = '';
		$dataLocal = '';
		$filterUsedGlobal = false;
		$filterUsedLocal = false;

		// Find global settings per integration or filter data.
		$filterNameGlobal = Filters::getFilterName(['integrations', $type, 'successRedirectUrl']);
		if (\has_filter($filterNameGlobal)) {
			$dataGlobal = \apply_filters($filterNameGlobal, $type);
			$filterUsedGlobal = true;
		} else {
			$dataGlobal = $this->getOptionValue($type . '-' . SettingsGeneral::SETTINGS_GLOBAL_REDIRECT_SUCCESS_KEY);
		}

		// Populate final output.
		$data = $dataGlobal;

		// Find local settings per integration or filter data.
		$filterNameLocal = Filters::getFilterName(['block', 'form', 'successRedirectUrl']);
		if (\has_filter($filterNameLocal)) {
			$dataLocal = \apply_filters($filterNameLocal, $type, $formId);
			$filterUsedLocal = true;
		} else {
			$dataLocal = $this->getSettingsValue(SettingsGeneral::SETTINGS_GENERAL_REDIRECT_SUCCESS_KEY, $formId);
		}

		// If local data exists overrider final output.
		if ($dataLocal) {
			$data = $dataLocal;
		}

		return [
			'data' => $data,

			'settingsGlobal' => $this->getSettingsDivWrap($settingsGlobal, $filterUsedGlobal),
			'dataGlobal' => $dataGlobal,
			'filterUsedGlobal' => $filterUsedGlobal,

			'settingsLocal' => $this->getSettingsDivWrap($settingsLocal, $filterUsedLocal),
			'dataLocal' => $dataLocal,
			'filterUsedLocal' => $filterUsedLocal,
		];
	}

	/**
	 * Return tracking event name data filter output.
	 *
	 * @param string $type Type of integration.
	 * @param string $formId Form ID.
	 *
	 * @return array<string, mixed>
	 */
	public function getTrackingEventNameFilterValue(string $type, string $formId): array
	{
		$settings = '';
		$data = '';
		$filterUsed = false;

		$filterName = Filters::getFilterName(['block', 'form', 'trackingEventName']);
		if (\has_filter($filterName)) {
			$data = \apply_filters($filterName, $type, $formId);
			$filterUsed = true;
		} else {
			$data = $this->getSettingsValue(SettingsGeneral::SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY, $formId);
		}

		return [
			'settings' => $this->getSettingsDivWrap($settings, $filterUsed),
			'data' => $data,
			'filterUsed' => $filterUsed,
		];
	}

	/**
	 * Return tracking additional data filter output.
	 *
	 * @param string $type Type of integration.
	 * @param string $formId Form ID.
	 *
	 * @return array<string, array<mixed>>
	 */
	public function getTrackingAditionalDataFilterValue(string $type, string $formId): array
	{
		$data = [];
		$settings = '';
		$settingsDetails = [];
		$filterUsed = false;

		$filterName = Filters::getFilterName(['block', 'form', 'trackingAdditionalData']);
		$trackingAdditionalData = $this->getSettingsValueGroup(SettingsGeneral::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_KEY, $formId);
		$trackingAdditionalDataSuccess = $this->getSettingsValueGroup(SettingsGeneral::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_SUCCESS_KEY, $formId);
		$trackingAdditionalDataError = $this->getSettingsValueGroup(SettingsGeneral::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_ERROR_KEY, $formId);
		$trackingAdditionalDataFilterValue = \has_filter($filterName) ? \apply_filters($filterName, $type, $formId) : [];

		if ($trackingAdditionalData || $trackingAdditionalDataFilterValue || $trackingAdditionalDataSuccess || $trackingAdditionalDataError) {
			$trackingData = \array_merge_recursive(
				[
					'general' => $trackingAdditionalData
				],
				[
					'success' => $trackingAdditionalDataSuccess
				],
				[
					'error' => $trackingAdditionalDataError
				],
				$trackingAdditionalDataFilterValue,
			);

			foreach ($trackingData as $key => $value) {
				foreach ($value as $inner) {
					$data[$key][$inner[0]] = $inner[1];
				}
			}

			if ($trackingAdditionalDataFilterValue) {
				$filterUsed = true;
			}
		}

		if ($filterUsed) {
			$settings .= \__('This field has a code filter applied to it, and the following items will be applied to the output:', 'eightshift-forms');

			foreach ($trackingAdditionalDataFilterValue as $key => $value) {
				$settingsDetails[$key] = "{$settings}<ul>";
				foreach ($value as $inner) {
					$settingsDetails[$key] .= "<li><code>{$inner[0]}</code> : <code>{$inner[1]}</code></li>";
				}
				$settingsDetails[$key] .= '</ul>';

				$settingsDetails[$key] = $this->getSettingsDivWrap($settingsDetails[$key], true, false);
			}
		}

		return [
			'settings' => $settingsDetails,
			'data' => $data,
			'filterUsed' => $filterUsed,
		];
	}

	/**
	 * Wrap output date with div for styling.
	 *
	 * @param string $data Data to wrap.
	 * @param bool $used If false dont wrap.
	 * @param bool $defaultPrefix Add defeult copy prefix.
	 *
	 * @return string
	 */
	private function getSettingsDivWrap(string $data, bool $used = false, bool $defaultPrefix = true): string
	{
		if (!$used) {
			return $data;
		}

		$prefix = $defaultPrefix ? \__("This field has a code filter applied to it so this field can't be changed.", 'eightshift-forms') : '';

		return '<div class="is-filter-applied">' . $prefix . $data . '</div>';
	}
}
