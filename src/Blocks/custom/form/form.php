<?php

/**
 * Template for the Form Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Mailer\SettingsMailer;
use EightshiftForms\Settings\Settings\SettingsAll;
use EightshiftForms\Settings\Settings\SettingsGeneral;

$manifest = Components::getManifest(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$formFormPostId = Components::checkAttr('formFormPostId', $attributes, $manifest);
$formFormPostIdDecoded = Helper::encryptor('decode', $formFormPostId);

// Check if mailer data is set and valid.
$isSettingsValid = \apply_filters(SettingsMailer::FILTER_SETTINGS_IS_VALID_NAME, $formFormPostIdDecoded);

// Bailout if settings are not ok.
if (!$isSettingsValid) {
	return;
}

$formClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
]);

?>

<div class="<?php echo esc_attr($formClass); ?>">
	<?php
	echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'form',
		Components::props('form', $attributes, [
			'formContent' => $innerBlockContent,
			'formPostId' => $formFormPostId,
			'formType' => SettingsMailer::SETTINGS_TYPE_KEY,
			'formTrackingEventName' => \apply_filters(
				SettingsAll::FILTER_BLOCK_SETTING_VALUE_NAME,
				SettingsGeneral::SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY,
				$formFormPostIdDecoded
			),
			'formSuccessRedirect' => \apply_filters(
				SettingsAll::FILTER_BLOCK_SETTING_VALUE_NAME,
				SettingsGeneral::SETTINGS_GENERAL_REDIRECTION_SUCCESS_KEY,
				$formFormPostIdDecoded
			),
		])
	);
	?>
</div>
