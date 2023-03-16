<?php

/**
 * Class that holds all filter used in the component and blocks regarding form.
 *
 * @package EightshiftLibs\Form
 */

declare(strict_types=1);

namespace EightshiftForms\Form;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Settings\FiltersOuputMock;
use EightshiftForms\Settings\Settings\SettingsBlocks;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Form class.
 */
class Form extends AbstractFormBuilder implements ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Use filters mock helper trait.
	 */
	use FiltersOuputMock;

	/**
	 * Filter settings option value key.
	 */
	public const FILTER_FORM_SETTINGS_OPTIONS_NAME = 'es_forms_form_settings_options';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_FORM_SETTINGS_OPTIONS_NAME, [$this, 'updateFormAttributesBeforeOutput']);
	}

	/**
	 * Modify original attributes before final output in form.
	 *
	 * @param array<string, mixed> $attributes Attributes to update.
	 *
	 * @return array<string, mixed>
	 */
	public function updateFormAttributesBeforeOutput(array $attributes): array
	{
		$prefix = $attributes['prefix'] ?? '';
		$type = $attributes['blockName'] ?? '';
		$formId = $attributes["{$prefix}PostId"] ?? '';

		if (!$prefix || !$type || !$formId) {
			return $attributes;
		}

		// Change form type depending if it is mailer empty.
		if ($type === SettingsMailer::SETTINGS_TYPE_KEY && isset($attributes["{$prefix}Action"])) {
			$attributes["{$prefix}Type"] = SettingsMailer::SETTINGS_TYPE_CUSTOM_KEY;
		}

		// Tracking event name.
		$trackingEventName = $this->getTrackingEventNameFilterValue($type, $formId)['data'];
		if ($trackingEventName) {
			$attributes["{$prefix}TrackingEventName"] = $trackingEventName;
		}

		// Provide additional data to tracking attr.
		$trackingAdditionalData = $this->getTrackingAditionalDataFilterValue($type, $formId)['data'];
		if ($trackingAdditionalData) {
			$attributes["{$prefix}TrackingAdditionalData"] = \wp_json_encode($trackingAdditionalData);
		}

		// Success redirect url.
		$successRedirectUrl = $this->getSuccessRedirectUrlFilterValue($type, $formId)['data'];
		if ($successRedirectUrl) {
			$attributes["{$prefix}SuccessRedirect"] = $successRedirectUrl;
		}

		// Success redirect variation.
		if (!$attributes["{$prefix}SuccessRedirectVariation"]) {
			$successRedirectUrl = $this->getSuccessRedirectVariationFilterValue($type, $formId)['data'];

			if ($successRedirectUrl) {
				$attributes["{$prefix}SuccessRedirectVariation"] = $successRedirectUrl;
			}
		}

		// Phone sync with country block.
		$attributes["{$prefix}PhoneSync"] = '';
		$filterName = Filters::getFilterName(['block', 'form', 'phoneSync']);
		if (\has_filter($filterName)) {
			$attributes["{$prefix}PhoneSync"] = \apply_filters($filterName, $type, $formId);
		} else {
			$attributes["{$prefix}PhoneSync"] = !$this->isCheckboxSettingsChecked(SettingsBlocks::SETTINGS_BLOCK_PHONE_DISABLE_SYNC_KEY, SettingsBlocks::SETTINGS_BLOCK_PHONE_DISABLE_SYNC_KEY, $formId);
		}

		$attributes["{$prefix}PhoneDisablePicker"] = $this->isCheckboxOptionChecked(SettingsBlocks::SETTINGS_BLOCK_PHONE_DISABLE_PICKER_KEY, SettingsBlocks::SETTINGS_BLOCK_PHONE_DISABLE_PICKER_KEY);

		return $attributes;
	}
}
