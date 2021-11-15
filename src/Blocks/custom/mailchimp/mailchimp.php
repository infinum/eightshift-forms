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

$manifest = Components::getManifest(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$mailchimpServerSideRender = Components::checkAttr('mailchimpServerSideRender', $attributes, $manifest);
$mailchimpFormPostId = Components::checkAttr('mailchimpFormPostId', $attributes, $manifest);

if ($mailchimpServerSideRender) {
	$mailchimpFormPostId = Helper::encryptor('encrypt', $mailchimpFormPostId);
}

$mailchimpFormPostIdDecoded = Helper::encryptor('decrypt', $mailchimpFormPostId);

// Check if mailchimp data is set and valid.
$isSettingsValid = \apply_filters(
	SettingsMailchimp::FILTER_SETTINGS_IS_VALID_NAME,
	$mailchimpFormPostIdDecoded
);

$mailchimpClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	Components::selector(!$isSettingsValid, $blockClass, '', 'invalid')
]);

// Bailout if settings are not ok but show msg only in editor.
if (!$isSettingsValid && $mailchimpServerSideRender) {
	?>
		<div class="<?php echo esc_attr($mailchimpClass); ?>">
			<?php esc_html_e('Sorry, it looks like your Mailchimp settings are not configured correctly. Please go to your form setting and input all required settings.', 'eightshift-forms'); ?>
		</div>
	<?php

	return;
}

// Output form.
echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	Mailchimp::FILTER_MAPPER_NAME,
	$mailchimpFormPostId
);
