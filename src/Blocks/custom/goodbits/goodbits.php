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
$manifestInvalid = Components::getManifest(dirname(__DIR__, 2) . '/components/invalid');

$blockClass = $attributes['blockClass'] ?? '';
$invalidClass = $manifestInvalid['componentClass'] ?? '';

$goodbitsFormServerSideRender = Components::checkAttr('goodbitsFormServerSideRender', $attributes, $manifest);
$goodbitsFormPostId = Components::checkAttr('goodbitsFormPostId', $attributes, $manifest);
$goodbitsFormTypeSelector = Components::checkAttr('goodbitsFormTypeSelector', $attributes, $manifest);

if ($goodbitsFormServerSideRender && !$goodbitsFormTypeSelector) {
	$goodbitsFormPostId = Helper::encryptor('encrypt', $goodbitsFormPostId);
}

$goodbitsFormPostIdDecoded = Helper::encryptor('decrypt', $goodbitsFormPostId);

// Check if goodbits data is set and valid.
$isSettingsValid = \apply_filters(
	SettingsGoodbits::FILTER_SETTINGS_IS_VALID_NAME,
	$goodbitsFormPostIdDecoded
);

$goodbitsClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	Components::selector(!$isSettingsValid, $invalidClass),
]);

// Bailout if settings are not ok but show msg only in editor.
if (!$isSettingsValid && $goodbitsFormServerSideRender) {
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
	$goodbitsFormPostId,
	[
		'formTypeSelector' => $goodbitsFormTypeSelector,
		'ssr' => $goodbitsFormServerSideRender,
	]
);
