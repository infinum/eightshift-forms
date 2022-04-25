<?php

/**
 * Template for the Greenhouse Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Integrations\Greenhouse\Greenhouse;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;

$manifest = Components::getManifest(__DIR__);
$manifestInvalid = Components::getManifest(dirname(__DIR__, 2) . '/components/invalid');

$blockClass = $attributes['blockClass'] ?? '';
$invalidClass = $manifestInvalid['componentClass'] ?? '';

$greenhouseFormServerSideRender = Components::checkAttr('greenhouseFormServerSideRender', $attributes, $manifest);
$greenhouseFormPostId = Components::checkAttr('greenhouseFormPostId', $attributes, $manifest);
$greenhouseFormDataTypeSelector = Components::checkAttr('greenhouseFormDataTypeSelector', $attributes, $manifest);

// Check if Greenhouse data is set and valid.
$isSettingsValid = apply_filters(SettingsGreenhouse::FILTER_SETTINGS_IS_VALID_NAME, $greenhouseFormPostId);

$greenhouseClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	Components::selector(!$isSettingsValid, $invalidClass),
]);

// Bailout if settings are not ok but show msg only in editor.
if (!$isSettingsValid && $greenhouseFormServerSideRender) {
	?>
		<div class="<?php echo esc_attr($greenhouseClass); ?>">
			<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M4.71991 1.60974C3.11997 2.29956 2 3.89096 2 5.74394C2 7.70327 3.25221 9.37013 5 9.98788V17C5 17.8284 5.67157 18.5 6.5 18.5C7.32843 18.5 8 17.8284 8 17V9.98788C9.74779 9.37013 11 7.70327 11 5.74394C11 3.78461 9.74779 2.11775 8 1.5V5.74394C8 6.57237 7.32843 7.24394 6.5 7.24394C5.67157 7.24394 5 6.57237 5 5.74394V1.5C4.90514 1.53353 4.81173 1.57015 4.71991 1.60974Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
				<path d="M13 13V16C13 17.3807 14.1193 18.5 15.5 18.5V18.5C16.8807 18.5 18 17.3807 18 16V13M13 13V10.5H14M13 13H18M18 13V10.5H17M14 10.5V5.5L13.5 3.5L14 1.5H17L17.5 3.5L17 5.5V10.5M14 10.5H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
			<br />
			<b><?php esc_html_e('The Greenhouse integration is not configured correctly.', 'eightshift-forms'); ?></b>
			<br />
			<?php esc_html_e('Check the form settings and try again.', 'eightshift-forms'); ?>
		</div>
	<?php

	return;
}

// Output form.
echo apply_filters( // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
	Greenhouse::FILTER_MAPPER_NAME,
	$greenhouseFormPostId,
	[
		'formDataTypeSelector' => $greenhouseFormDataTypeSelector,
		'ssr' => $greenhouseFormServerSideRender,
	]
);
