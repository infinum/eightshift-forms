<?php

/**
 * Template for the Mailchimp Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Integrations\Mailchimp\MailchimpMapper;
use EightshiftForms\Mailer\SettingsMailer;
use EightshiftForms\Settings\Settings\SettingsAll;
use EightshiftForms\Settings\Settings\SettingsGeneral;

$manifest = Components::getManifest(__DIR__);

$formPostId = Components::checkAttr('formPostId', $attributes, $manifest);
$formPostIdDecoded = Helper::encryptor('decode', $formPostId);

echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	MailchimpMapper::FILTER_MAPPER_NAME,
	[
		'formPostId' => $formPostId,
		'formType' => SettingsMailer::SETTINGS_TYPE_KEY,
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
