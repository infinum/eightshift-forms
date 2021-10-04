<?php

/**
 * Template for the Mailchimp Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Integrations\Mailchimp\Mailchimp;
use EightshiftForms\Integrations\Mailchimp\SettingsMailchimp;
use EightshiftForms\Settings\Settings\SettingsAll;
use EightshiftForms\Settings\Settings\SettingsGeneral;

$manifest = Components::getManifest(__DIR__);

$formPostId = Components::checkAttr('formPostId', $attributes, $manifest);
$formPostIdDecoded = Helper::encryptor('decode', $formPostId);

// Check if mailchimp data is set and valid.
$isSettingsValid = \apply_filters(SettingsMailchimp::FILTER_SETTINGS_IS_VALID_NAME, $formPostIdDecoded);

// Bailout if settings are not ok.
if (!$isSettingsValid) {
	return;
}

echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	Mailchimp::FILTER_MAPPER_NAME,
	[
		'formPostId' => $formPostId,
		'formType' => SettingsMailchimp::SETTINGS_TYPE_KEY,
		'formTrackingEventName' => \apply_filters(
			SettingsAll::FILTER_BLOCK_SETTING_VALUE_NAME,
			SettingsGeneral::SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY,
			$formPostIdDecoded
		),
		'formSuccessRedirect' => \apply_filters(
			SettingsAll::FILTER_BLOCK_SETTING_VALUE_NAME,
			SettingsGeneral::SETTINGS_GENERAL_REDIRECTION_SUCCESS_KEY,
			$formPostIdDecoded
		),
	]
);