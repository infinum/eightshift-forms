<?php

/**
 * Template for the HubSpot Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\Hubspot\Hubspot;
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
use EightshiftForms\Settings\Settings;
use EightshiftForms\Settings\Settings\SettingsAll;
use EightshiftForms\Settings\Settings\SettingsGeneral;

$manifest = Components::getManifest(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$hubspotServerSideRender = Components::checkAttr('hubspotServerSideRender', $attributes, $manifest);
$hubspotFormPostId = Components::checkAttr('hubspotFormPostId', $attributes, $manifest);

if ($hubspotServerSideRender) {
	$hubspotFormPostId = Helper::encryptor('encrypt', $hubspotFormPostId);
}

$hubspotFormPostIdDecoded = Helper::encryptor('decode', $hubspotFormPostId);

// Check if hubspot data is set and valid.
$isSettingsValid = \apply_filters(SettingsHubspot::FILTER_SETTINGS_IS_VALID_NAME, $hubspotFormPostIdDecoded);

$hubspotClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	Components::selector(!$isSettingsValid, $blockClass, '', 'invalid')
]);

// Bailout if settings are not ok.
if ($isSettingsValid) {
	echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		Hubspot::FILTER_MAPPER_NAME,
		[
			'formPostId' => $hubspotFormPostId,
			'formType' => SettingsHubspot::SETTINGS_TYPE_KEY,
			'formResetOnSuccess' => !Variables::isDevelopMode(),
			'formDisableScrollToFieldOnError' => (bool) \apply_filters(
				Settings::FILTER_SETTINGS_OPTION_VALUE_NAME,
				SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR_KEY
			),
			'formDisableScrollToGlobalMessageOnSuccess' => (bool) \apply_filters(
				Settings::FILTER_SETTINGS_OPTION_VALUE_NAME,
				SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MSG_ON_SUCCESS_KEY
			),
			'formTrackingEventName' => \apply_filters(
				SettingsAll::FILTER_BLOCK_SETTING_VALUE_NAME,
				SettingsGeneral::SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY,
				$hubspotFormPostIdDecoded
			),
			'formSuccessRedirect' => \apply_filters(
				SettingsAll::FILTER_BLOCK_SETTING_VALUE_NAME,
				SettingsGeneral::SETTINGS_GENERAL_REDIRECTION_SUCCESS_KEY,
				$hubspotFormPostIdDecoded
			),
		]
	);
} else { ?>
	<div class="<?php echo esc_attr($hubspotClass); ?>">
		<?php esc_html_e('Sorry, it looks like your HubSpot settings are not configured correctly. Please contact your admin.', 'eightshift-forms'); ?>
	</div>
		<?php
}
