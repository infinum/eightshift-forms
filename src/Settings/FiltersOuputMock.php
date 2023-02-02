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
	 * Return success redirect url data filter output.
	 *
	 * @param string $type Type of field.
	 * @param string $formId Form ID.
	 *
	 * @return array<string, array<mixed>>
	 */
	public function getSuccessRedirectUrlFilterValue(string $type, string $formId): array
	{
		$settings = '';
		$data = '';
		$filterUsed = false;

		$filterName = Filters::getFilterName(['block', 'form', 'successRedirectUrl']);
		if (\has_filter($filterName)) {
			$data = apply_filters($filterName, $type, $formId);
			$filterUsed = true;
		} else {
			$data = $this->getSettingsValue(SettingsGeneral::SETTINGS_GENERAL_REDIRECTION_SUCCESS_KEY, $formId);
		}

		return [
			'settings' => $this->getSettingsDivWrap($settings, $filterUsed),
			'data' => $data,
			'filterUsed' => $filterUsed,
		];
	}

	/**
	 * Return tracking event name data filter output.
	 *
	 * @param string $type Type of field.
	 * @param string $formId Form ID.
	 *
	 * @return array<string, array<mixed>>
	 */
	public function getTrackingEventNameFilterValue(string $type, string $formId): array
	{
		$settings = '';
		$data = '';
		$filterUsed = false;

		$filterName = Filters::getFilterName(['block', 'form', 'trackingEventName']);
		if (\has_filter($filterName)) {
			$data = apply_filters($filterName, $type, $formId);
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
	 * @param string $type Type of field.
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

			foreach ($data as $key => $value) {
				$settingsDetails[$key] = "{$settings}<ul>";
				foreach ($value as $innerKey => $inner) {
					$settingsDetails[$key] .= "<li><code>{$innerKey}</code> : <code>{$inner}</code></li>";
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

		$prefix = '';
		if ($defaultPrefix && $used) {
			$prefix = \__("This field has a code filter applied to it so this field can't be changed.", 'eightshift-forms');
		}

		return '<div class="is-filter-applied">' . $prefix . $data . '</div>';
	}
}
