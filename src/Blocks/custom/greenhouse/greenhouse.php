<?php

/**
 * Template for the Greenhouse Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Integrations\Greenhouse\Greenhouse;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;

$manifest = Components::getManifest(__DIR__);
$manifestInvalid = Components::getManifest(dirname(__DIR__, 2) . '/components/invalid');

$blockClass = $attributes['blockClass'] ?? '';
$invalidClass = $manifestInvalid['componentClass'] ?? '';

$greenhouseFormServerSideRender = Components::checkAttr('greenhouseFormServerSideRender', $attributes, $manifest);
$greenhouseFormPostId = Components::checkAttr('greenhouseFormPostId', $attributes, $manifest);
$greenhouseFormTypeSelector = Components::checkAttr('greenhouseFormTypeSelector', $attributes, $manifest);

if ($greenhouseFormServerSideRender && !$greenhouseFormTypeSelector) {
	$greenhouseFormPostId = Helper::encryptor('encrypt', $greenhouseFormPostId);
}

$greenhouseFormPostIdDecoded = Helper::encryptor('decrypt', $greenhouseFormPostId);

// Check if Greenhouse data is set and valid.
$isSettingsValid = \apply_filters(SettingsGreenhouse::FILTER_SETTINGS_IS_VALID_NAME, $greenhouseFormPostIdDecoded);

$greenhouseClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	Components::selector(!$isSettingsValid, $invalidClass),
]);

// Bailout if settings are not ok but show msg only in editor.
if (!$isSettingsValid && $greenhouseFormServerSideRender) {
	?>
		<div class="<?php echo esc_attr($greenhouseClass); ?>">
			<?php esc_html_e('Sorry, it looks like your Greenhouse settings are not configured correctly. Please go to your form setting and input all required settings.', 'eightshift-forms'); ?>
		</div>
	<?php

	return;
}

// Output form.
echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	Greenhouse::FILTER_MAPPER_NAME,
	$greenhouseFormPostId,
	[
		'formTypeSelector' => $greenhouseFormTypeSelector,
		'ssr' => $greenhouseFormServerSideRender,
	]
);
