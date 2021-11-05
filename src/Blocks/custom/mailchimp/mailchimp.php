<?php

/**
 * Template for the Mailchimp Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\Mailchimp\Mailchimp;
use EightshiftForms\Integrations\Mailchimp\SettingsMailchimp;
use EightshiftForms\Settings\Settings;
use EightshiftForms\Settings\Settings\SettingsAll;
use EightshiftForms\Settings\Settings\SettingsGeneral;

$manifest = Components::getManifest(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$mailchimpServerSideRender = Components::checkAttr('mailchimpServerSideRender', $attributes, $manifest);
$mailchimpFormPostId = Components::checkAttr('mailchimpFormPostId', $attributes, $manifest);

if ($mailchimpServerSideRender) {
	$mailchimpFormPostId = Helper::encryptor('encrypt', $mailchimpFormPostId);
}

$mailchimpFormPostIdDecoded = Helper::encryptor('decode', $mailchimpFormPostId);

// Check if mailchimp data is set and valid.
$isSettingsValid = \apply_filters(SettingsMailchimp::FILTER_SETTINGS_IS_VALID_NAME, $mailchimpFormPostIdDecoded);

$mailchimpClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	Components::selector(!$isSettingsValid, $blockClass, '', 'invalid')
]);

// Bailout if settings are not ok.
if ($isSettingsValid) {
	echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		Mailchimp::FILTER_MAPPER_NAME,
		[
			'formPostId' => $mailchimpFormPostId,
			'formType' => SettingsMailchimp::SETTINGS_TYPE_KEY,
			'formResetOnSuccess' => !Variables::isDevelopMode(),
			'formDisableScrollToFieldOnError' => (bool) \apply_filters(
				Settings::FILTER_IS_CHECKBOX_OPTION_CHECKED_NAME,
				SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR,
				SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
			),
			'formDisableScrollToGlobalMessageOnSuccess' => (bool) \apply_filters(
				Settings::FILTER_IS_CHECKBOX_OPTION_CHECKED_NAME,
				SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS,
				SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
			),
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
} else { ?>
	<div class="<?php echo esc_attr($mailchimpClass); ?>">
		<?php esc_html_e('Sorry, it looks like your Mailchimp settings are not configured correctly. Please contact your admin.', 'eightshift-forms'); ?>
	</div>
		<?php
}
