<?php

/**
 * Template for the Greenhouse Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\Greenhouse\Greenhouse;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;
use EightshiftForms\Settings\Settings;
use EightshiftForms\Settings\Settings\SettingsAll;
use EightshiftForms\Settings\Settings\SettingsGeneral;

$manifest = Components::getManifest(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$greenhouseServerSideRender = Components::checkAttr('greenhouseServerSideRender', $attributes, $manifest);
$greenhouseFormPostId = Components::checkAttr('greenhouseFormPostId', $attributes, $manifest);

if ($greenhouseServerSideRender) {
	$greenhouseFormPostId = Helper::encryptor('encrypt', $greenhouseFormPostId);
}

$greenhouseFormPostIdDecoded = Helper::encryptor('decode', $greenhouseFormPostId);

// Check if Greenhouse data is set and valid.
$isSettingsValid = \apply_filters(SettingsGreenhouse::FILTER_SETTINGS_IS_VALID_NAME, $greenhouseFormPostIdDecoded);

$greenhouseClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	Components::selector(!$isSettingsValid, $blockClass, '', 'invalid')
]);

// Bailout if settings are not ok.
if ($isSettingsValid) {
	echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		Greenhouse::FILTER_MAPPER_NAME,
		[
			'formPostId' => $greenhouseFormPostId,
			'formType' => SettingsGreenhouse::SETTINGS_TYPE_KEY,
			'formResetOnSuccess' => (bool) !Variables::isDevelopMode(),
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
				$greenhouseFormPostIdDecoded
			),
			'formSuccessRedirect' => \apply_filters(
				SettingsAll::FILTER_BLOCK_SETTING_VALUE_NAME,
				SettingsGeneral::SETTINGS_GENERAL_REDIRECTION_SUCCESS_KEY,
				$greenhouseFormPostIdDecoded
			),
		]
	);
} else { ?>
	<div class="<?php echo esc_attr($greenhouseClass); ?>">
		<?php esc_html_e('Sorry, it looks like your Greenhose settings are not configured correctly. Please contact your admin.', 'eightshift-forms'); ?>
	</div>
		<?php
}
