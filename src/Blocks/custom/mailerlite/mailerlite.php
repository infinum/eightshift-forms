<?php

/**
 * Template for the Mailerlite Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Integrations\Mailerlite\Mailerlite;
use EightshiftForms\Integrations\Mailerlite\SettingsMailerlite;

$manifest = Components::getManifest(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$mailerliteServerSideRender = Components::checkAttr('mailerliteServerSideRender', $attributes, $manifest);
$mailerliteFormPostId = Components::checkAttr('mailerliteFormPostId', $attributes, $manifest);

if ($mailerliteServerSideRender) {
	$mailerliteFormPostId = Helper::encryptor('encrypt', $mailerliteFormPostId);
}

$mailerliteFormPostIdDecoded = Helper::encryptor('decode', $mailerliteFormPostId);

// Check if mailerlite data is set and valid.
$isSettingsValid = \apply_filters(
	SettingsMailerlite::FILTER_SETTINGS_IS_VALID_NAME,
	$mailerliteFormPostIdDecoded
);

$mailerliteClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	Components::selector(!$isSettingsValid, $blockClass, '', 'invalid')
]);

// Bailout if settings are not ok.
if ($isSettingsValid) {
	echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		Mailerlite::FILTER_MAPPER_NAME,
		$mailerliteFormPostId
	);
} else { ?>
	<div class="<?php echo esc_attr($mailerliteClass); ?>">
		<?php esc_html_e('Sorry, it looks like your Mailerlite settings are not configured correctly. Please contact your admin.', 'eightshift-forms'); ?>
	</div>
		<?php
}
