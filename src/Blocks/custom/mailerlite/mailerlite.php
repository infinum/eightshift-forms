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
$manifestInvalid = Components::getManifest(dirname(__DIR__, 2) . '/components/invalid');

$blockClass = $attributes['blockClass'] ?? '';
$invalidClass = $manifestInvalid['componentClass'] ?? '';

$mailerliteFormServerSideRender = Components::checkAttr('mailerliteFormServerSideRender', $attributes, $manifest);
$mailerliteFormPostId = Components::checkAttr('mailerliteFormPostId', $attributes, $manifest);
$mailerliteFormTypeSelector = Components::checkAttr('mailerliteFormTypeSelector', $attributes, $manifest);

if ($mailerliteFormServerSideRender && !$mailerliteFormTypeSelector) {
	$mailerliteFormPostId = Helper::encryptor('encrypt', $mailerliteFormPostId);
}

$mailerliteFormPostIdDecoded = Helper::encryptor('decrypt', $mailerliteFormPostId);

// Check if mailerlite data is set and valid.
$isSettingsValid = \apply_filters(
	SettingsMailerlite::FILTER_SETTINGS_IS_VALID_NAME,
	$mailerliteFormPostIdDecoded
);

$mailerliteClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	Components::selector(!$isSettingsValid, $invalidClass),
]);

// Bailout if settings are not ok but show msg only in editor.
if (!$isSettingsValid && $mailerliteFormServerSideRender) {
	?>
		<div class="<?php echo esc_attr($mailerliteClass); ?>">
			<?php esc_html_e('Sorry, it looks like your Mailerlite settings are not configured correctly. Please go to your form setting and input all required settings.', 'eightshift-forms'); ?>
		</div>
	<?php

	return;
}

// Output form.
echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	Mailerlite::FILTER_MAPPER_NAME,
	$mailerliteFormPostId,
	[
		'formTypeSelector' => $mailerliteFormTypeSelector,
		'ssr' => $mailerliteFormServerSideRender,
	]
);
