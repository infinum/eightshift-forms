<?php

/**
 * Class that holds all filter used in the component and blocks regarding form.
 *
 * @package EightshiftLibs\Foirm
 */

declare(strict_types=1);

namespace EightshiftForms\Form;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Mailer\SettingsMailer;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Form class.
 */
class Form implements ServiceInterface
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
		\add_filter(self::FILTER_FORM_SETTINGS_OPTIONS_NAME, [$this, 'getFormSettingsOptions']);
	}

	/**
	 * Create array of additonal form options
	 *
	 * @param array<string, string|int> $formAdditionalProps Additional props to pass to form.
	 *
	 * @return array
	 */
	public function getFormSettingsOptions(string $formId): array
	{
		$output = [];

		// Get post ID prop.
		$output['formPostId'] = $formId;

		$formIdDecoded = (string) Helper::encryptor($formId, 'decrypt');

		// Get form type.
		$output['formType'] = SettingsMailer::SETTINGS_TYPE_KEY;

		// Reset form on success.
		$output['formResetOnSuccess'] = !Variables::isDevelopMode();

		// Disable scroll to field on error.
		$output['formDisableScrollToFieldOnError'] = $this->isCheckboxOptionChecked(
			SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR,
			SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
		);

		// Disable scroll to global message on success.
		$output['formDisableScrollToGlobalMessageOnSuccess'] = $this->isCheckboxOptionChecked(
			SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS,
			SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
		);

		// Tracking event name.
		$output['formTrackingEventName'] = $this->getSettingsValue(
			SettingsGeneral::SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY,
			$formIdDecoded
		);

		// Success redirect url.
		$output['formSuccessRedirect'] = $this->getSettingsValue(
			SettingsGeneral::SETTINGS_GENERAL_REDIRECTION_SUCCESS_KEY,
			$formIdDecoded
		);

		return $output;
	}

}
