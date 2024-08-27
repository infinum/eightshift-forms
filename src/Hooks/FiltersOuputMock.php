<?php

/**
 * Class that holds data for mocking filters output used in seeting for better UX.
 *
 * @package EightshiftForms\Hooks
 */

declare(strict_types=1);

namespace EightshiftForms\Hooks;

use EightshiftForms\General\SettingsGeneral;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsEncryption;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;

/**
 * FiltersOuputMock class.
 */
final class FiltersOuputMock
{
	/**
	 * Return enrichment manual map data filter output.
	 *
	 * @param array<string, mixed> $config getEnrichmentConfig output value.
	 *
	 * @return array<string, mixed>
	 */
	public static function getEnrichmentManualMapFilterValue(array $config): array
	{
		$settings = '';
		$filterUsed = false;
		$settingsFields = [];

		$filterName = UtilsHooksHelper::getFilterName(['enrichment', 'manualMap']);

		if (\has_filter($filterName)) {
			$filterData = \apply_filters($filterName, '');

			// Output map depending on the type.
			if ($filterData) {
				foreach ($filterData as $key => $value) {
					$config['allowed'][] = $key;
					$config['map'][$key] = \array_flip($value);
				}

				$config['allowed'] = \array_unique($config['allowed']);
				foreach ($config['allowed'] as $value) {
					if (!isset(\array_flip(\array_keys($filterData))[$value])) {
						$settingsFields[] = $value;
					}
				}

				$settings .= \__('Additional parameters were provided through code', 'eightshift-forms');
				$settings .= '<ul>';
				foreach ($filterData as $key => $value) {
					$settingsValue = \implode(', ', $value);
					$settings .= "<li><code>{$key}</code> : <code>{$settingsValue}</code></li>";
				}
				$settings .= '</ul>';
				$filterUsed = true;
			}
		}

		$settingsOutput = self::getSettingsDivWrap($settings, $filterUsed, false);

		return [
			'settings' => $settingsOutput,
			'settingsFields' => $settingsFields,
			'config' => $config,
			'filterUsed' => $filterUsed,
		];
	}

	/**
	 * Return variations data filter output.
	 *
	 * @param string $type Type of integration.
	 * @param string $formId Form ID.
	 * @param array<string, mixed> $formDetails Form details.
	 *
	 * @return array<string, mixed>
	 */
	public static function getVariationFilterValue(string $type, string $formId, array $formDetails): array
	{
		$data = [];
		$shouldAppend = UtilsSettingsHelper::isSettingCheckboxChecked(SettingsGeneral::SETTINGS_VARIATION_SHOULD_APPEND_ON_GLOBAL_KEY, SettingsGeneral::SETTINGS_VARIATION_SHOULD_APPEND_ON_GLOBAL_KEY, $formId);

		// Find global settings per integration.
		$data = UtilsSettingsHelper::getOptionValueGroup($type . '-' . SettingsGeneral::SETTINGS_VARIATION_KEY);
		if ($data) {
			$data = \array_column($data, 1, 0);
		}

		$filterUsed = false;

		// Find local settings for form.
		$dataLocal = UtilsSettingsHelper::getSettingValueGroup(SettingsGeneral::SETTINGS_VARIATION_KEY, $formId);
		if ($dataLocal) {
			$dataLocal = \array_column($dataLocal, 1, 0);
			$data = $shouldAppend ? \array_merge($data, $dataLocal) : $dataLocal;
		}

		// Get data from forms block.
		$secureData = $formDetails[UtilsConfig::FD_SECURE_DATA] ?? [];
		if ($secureData) {
			$secureData = \json_decode(UtilsEncryption::decryptor($formDetails[UtilsConfig::FD_SECURE_DATA]) ?: '', true)['v'] ?? [];

			if ($secureData) {
				$secureData = \array_column($secureData, 1, 0);
				$data = $shouldAppend ? \array_merge($data, $secureData) : $secureData;
			}
		}

		// Find local settings per integration or filter data.
		$filterNameLocal = UtilsHooksHelper::getFilterName(['block', 'form', 'variation']);
		if (\has_filter($filterNameLocal)) {
			$dataFilter = \apply_filters($filterNameLocal, [], $formDetails, $formId);

			if ($dataFilter) {
				$data = $shouldAppend ? \array_merge($data, $dataFilter) : $dataFilter;
				$filterUsed = true;
			}
		}

		return [
			'data' => $data,
			'settingsGlobal' => self::getSettingsDivWrap(''),
			'settingsLocal' => self::getSettingsDivWrap('', $filterUsed),
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
	public static function getSuccessRedirectUrlFilterValue(string $type, string $formId): array
	{
		$data = '';
		$filterUsed = false;

		// Find global settings per integration.
		$dataGlobal = UtilsSettingsHelper::getOptionValue($type . '-' . SettingsGeneral::SETTINGS_GLOBAL_REDIRECT_SUCCESS_KEY);

		// Populate final output.
		$data = $dataGlobal;

		// Find local settings for form.
		$dataLocal = UtilsSettingsHelper::getSettingValue(SettingsGeneral::SETTINGS_SUCCESS_REDIRECT_URL_KEY, $formId);

		if ($dataLocal) {
			$data = $dataLocal;
		}

		// Find local settings per integration or filter data.
		$filterNameLocal = UtilsHooksHelper::getFilterName(['block', 'form', 'successRedirectUrl']);
		if (\has_filter($filterNameLocal)) {
			$dataFilter = \apply_filters($filterNameLocal, $type, $formId) ?? '';

			if ($dataFilter) {
				$data = $dataFilter;
				$dataLocal = $dataFilter;
				$filterUsed = true;
			}
		}

		return [
			'data' => $data,

			'settingsGlobal' => self::getSettingsDivWrap(''),
			'dataGlobal' => $dataGlobal,

			'settingsLocal' => self::getSettingsDivWrap('', $filterUsed),
			'dataLocal' => $dataLocal,
			'filterUsed' => $filterUsed,
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
	public static function getTrackingEventNameFilterValue(string $type, string $formId): array
	{
		$filterUsed = false;

		$data = UtilsSettingsHelper::getSettingValue(SettingsGeneral::SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY, $formId);

		$filterName = UtilsHooksHelper::getFilterName(['block', 'form', 'trackingEventName']);
		if (\has_filter($filterName)) {
			$filterData = \apply_filters($filterName, $type, $formId);

			if ($filterData) {
				$data = $filterData;
				$filterUsed = true;
			}
		}

		return [
			'settings' => self::getSettingsDivWrap('', $filterUsed),
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
	public static function getTrackingAditionalDataFilterValue(string $type, string $formId): array
	{
		$data = [];
		$settings = '';
		$settingsDetails = [];
		$filterUsed = false;

		$filterName = UtilsHooksHelper::getFilterName(['block', 'form', 'trackingAdditionalData']);
		$trackingAdditionalData = UtilsSettingsHelper::getSettingValueGroup(SettingsGeneral::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_KEY, $formId);
		$trackingAdditionalDataSuccess = UtilsSettingsHelper::getSettingValueGroup(SettingsGeneral::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_SUCCESS_KEY, $formId);
		$trackingAdditionalDataError = UtilsSettingsHelper::getSettingValueGroup(SettingsGeneral::SETTINGS_GENERAL_TRACKING_ADDITIONAL_DATA_ERROR_KEY, $formId);
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
			$settings .= '<details>';
			$settings .= '<summary>' . \__('Additional parameters were provided through code', 'eightshift-forms') . '</summary>';

			foreach ($trackingAdditionalDataFilterValue as $key => $value) {
				$settingsDetails[$key] = "{$settings}<ul>";
				foreach ($value as $inner) {
					$settingsDetails[$key] .= "<li><code>{$inner[0]}</code> : <code>{$inner[1]}</code></li>";
				}
				$settingsDetails[$key] .= '</ul>';

				$settingsDetails[$key] = self::getSettingsDivWrap($settingsDetails[$key], true, false);
			}

			$settings .= '</details>';
		}

		return [
			'settings' => $settingsDetails,
			'data' => $data,
			'filterUsed' => $filterUsed,
		];
	}

	/**
	 * Return Tailwind selectors data filter output.
	 *
	 * @param string $selector Selector to get data for.
	 * @param array<string, string> $attributes The block attributes.
	 * @param string $sufix Sufix to add to the selector.
	 *
	 * @return string
	 */
	public static function getTwSelectors(string $selector, array $attributes, string $sufix = ''): string
	{
		$output = [
			$selector,
			$sufix,
		];

		$filterName = UtilsHooksHelper::getFilterName(['blocks', 'tailwindSelectors']);
		if (\has_filter($filterName)) {
			$filterData = \apply_filters($filterName, [], $attributes);

			if (isset($filterData[$selector]) && \is_string($filterData[$selector])) {
				$output[] = $filterData[$selector];
			}
		}

		return \implode(' ', \array_filter($output));
	}

	// --------------------------------------------------
	// Private helper methods
	// --------------------------------------------------

	/**
	 * Wrap output date with div for styling.
	 *
	 * @param string $data Data to wrap.
	 * @param bool $used If false dont wrap.
	 * @param bool $defaultPrefix Add default copy prefix.
	 *
	 * @return string
	 */
	private static function getSettingsDivWrap(string $data, bool $used = false, bool $defaultPrefix = true): string
	{
		if (!$used) {
			return $data;
		}

		$prefix = $defaultPrefix ? \__('Value set in code', 'eightshift-forms') : '';

		if (empty($prefix)) {
			return '<br /><div class="is-filter-applied">' . $data . '</div>';
		}

		if (empty($data)) {
			return '<div class="is-filter-applied">' . $prefix . '</div>';
		}

		return '<br /><br /><details class="is-filter-applied"><summary>' . $prefix . '</summary>' . $data . '</details>';
	}
}
