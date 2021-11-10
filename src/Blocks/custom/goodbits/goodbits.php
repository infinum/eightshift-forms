<?php

/**
 * Template for the Goodbits Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Integrations\Goodbits\Goodbits;
use EightshiftForms\Integrations\Goodbits\SettingsGoodbits;

$manifest = Components::getManifest(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$goodbitsServerSideRender = Components::checkAttr('goodbitsServerSideRender', $attributes, $manifest);
$goodbitsFormPostId = Components::checkAttr('goodbitsFormPostId', $attributes, $manifest);

if ($goodbitsServerSideRender) {
	$goodbitsFormPostId = Helper::encryptor('encrypt', $goodbitsFormPostId);
}

$goodbitsFormPostIdDecoded = Helper::encryptor('decode', $goodbitsFormPostId);

// Check if goodbits data is set and valid.
$isSettingsValid = \apply_filters(
	SettingsGoodbits::FILTER_SETTINGS_IS_VALID_NAME,
	$goodbitsFormPostIdDecoded
);

$goodbitsClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	Components::selector(!$isSettingsValid, $blockClass, '', 'invalid')
]);

// Bailout if settings are not ok but show msg only in editor.
if (!$isSettingsValid && $goodbitsServerSideRender) {
	?>
		<div class="<?php echo esc_attr($goodbitsClass); ?>">
			<?php esc_html_e('Sorry, it looks like your Goodbits settings are not configured correctly. Please go to your form setting and input all required settings.', 'eightshift-forms'); ?>
		</div>
	<?php

	return;
}

// Output form.
echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	Goodbits::FILTER_MAPPER_NAME,
	$goodbitsFormPostId
);
