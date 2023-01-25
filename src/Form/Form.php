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
use EightshiftForms\Settings\Settings\SettingsBlocks;
use EightshiftForms\Settings\Settings\SettingsGeneral;
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
		$blockName = $attributes['blockName'] ?? '';
		$postId = $attributes["{$prefix}PostId"] ?? '';

		if (!$prefix || !$blockName || !$postId) {
			return $attributes;
		}

		// Change form type depending if it is mailer empty.
		if ($blockName === SettingsMailer::SETTINGS_TYPE_KEY && isset($attributes["{$prefix}Action"])) {
			$attributes["{$prefix}Type"] = SettingsMailer::SETTINGS_TYPE_CUSTOM_KEY;
		}

		// Additional props from settings.
		return \array_merge($attributes, $this->getFormAdditionalPropsFromSettings($postId, $blockName, $prefix));
	}

		/**
	 * Return Integration form additional props from settings.
	 *
	 * @param string $formId Form ID.
	 * @param string $type Form Type.
	 * @param string $prefix Attribute prefix key.
	 *
	 * @return array<string, mixed>
	 */
	protected function getFormAdditionalPropsFromSettings(string $formId, string $type, string $prefix): array
	{
		$formAdditionalProps = [];

		// Tracking event name.
		$formAdditionalProps["{$prefix}TrackingEventName"] = '';
		$filterName = Filters::getBlockFilterName('form', 'trackingEventName');
		if (has_filter($filterName)) {
			$formAdditionalProps["{$prefix}TrackingEventName"] = \apply_filters($filterName, $type, $formId);
		} else {
			$formAdditionalProps["{$prefix}TrackingEventName"] = $this->getSettingsValue(SettingsGeneral::SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY, $formId);
		}

		// Success redirect url.
		$formAdditionalProps["{$prefix}SuccessRedirect"] = '';
		$filterName = Filters::getBlockFilterName('form', 'successRedirectUrl');
		if (has_filter($filterName)) {
			$formAdditionalProps["{$prefix}SuccessRedirect"] = \apply_filters($filterName, $type, $formId);
		} else {
			$formAdditionalProps["{$prefix}SuccessRedirect"] = $this->getSettingsValue(SettingsGeneral::SETTINGS_GENERAL_REDIRECTION_SUCCESS_KEY, $formId);
		}

		// Phone sync with country block.
		$formAdditionalProps["{$prefix}PhoneSync"] = '';
		$filterName = Filters::getBlockFilterName('form', 'phoneSync');
		if (has_filter($filterName)) {
			$formAdditionalProps["{$prefix}PhoneSync"] = \apply_filters($filterName, $type, $formId);
		} else {
			$formAdditionalProps["{$prefix}PhoneSync"] = $this->isCheckboxOptionChecked(SettingsBlocks::SETTINGS_BLOCK_PHONE_SYNC_KEY, SettingsBlocks::SETTINGS_BLOCK_PHONE_SYNC_KEY);
		}

		return $formAdditionalProps;
	}
}
