<?php

/**
 * Template for the Custom Data Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\BlockCustomData;
use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);
$manifestInvalid = Components::getManifest(dirname(__DIR__, 2) . '/components/invalid');
$manifestOverlay = Components::getManifest(dirname(__DIR__, 2) . '/components/overlay');

$blockClass = $attributes['blockClass'] ?? '';
$invalidClass = $manifestInvalid['componentClass'] ?? '';
$overlayClass = $manifestOverlay['componentClass'] ?? '';

$block = apply_filters(
	BlockCustomData::FILTER_BLOCK_CUSTOM_DATA_COMPONENT_NAME,
	$attributes
);

$customDataClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	Components::selector(!$isSettingsValid, $invalidClass),
	Components::selector($overlayClass, $overlayClass),
]);

if (!$block) {
	?>
	<div class="<?php echo esc_attr($customDataClass); ?>">
		<?php esc_html_e('Sorry, it looks like your Custom data block is not configured correctly. In order for the block to work, you must provide data using filters. Check the documentation for details.', 'eightshift-forms'); ?>

	</div>
<?php }

// Output form.
echo $block; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
