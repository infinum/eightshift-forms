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

$mailchimpServerSideRender = Components::checkAttr('mailchimpServerSideRender', $attributes, $manifest);
$mailchimpFormPostId = Components::checkAttr('mailchimpFormPostId', $attributes, $manifest);

if ($mailchimpServerSideRender) {
	$mailchimpFormPostId = Helper::encryptor('encrypt', $mailchimpFormPostId);
}

$mailchimpFormPostIdDecoded = Helper::encryptor('decode', $mailchimpFormPostId);

// Check if mailchimp data is set and valid.
$isSettingsValid = \apply_filters(SettingsMailchimp::FILTER_SETTINGS_IS_VALID_NAME, $mailchimpFormPostIdDecoded);

// Bailout if settings are not ok.
if (!$isSettingsValid) {
	return;
}

echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	Mailchimp::FILTER_MAPPER_NAME,
	[
		'formPostId' => $mailchimpFormPostId,
		'formType' => SettingsMailchimp::SETTINGS_TYPE_KEY,
		'formTrackingEventName' => \apply_filters(
			SettingsAll::FILTER_BLOCK_SETTING_VALUE_NAME,
			SettingsGeneral::SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY,
			$mailchimpFormPostIdDecoded
		),
		'formSuccessRedirect' => \apply_filters(
			SettingsAll::FILTER_BLOCK_SETTING_VALUE_NAME,
			SettingsGeneral::SETTINGS_GENERAL_REDIRECTION_SUCCESS_KEY,
			$mailchimpFormPostIdDecoded
		),
	]
);
