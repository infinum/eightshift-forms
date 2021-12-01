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

$blockClass = $attributes['blockClass'] ?? '';
$invalidClass = $manifestInvalid['componentClass'] ?? '';

$customDataServerSideRender = Components::checkAttr('customDataServerSideRender', $attributes, $manifest);

$block = apply_filters(
	BlockCustomData::FILTER_BLOCK_CUSTOM_DATA_COMPONENT_NAME,
	$attributes
);

$customDataClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	Components::selector($invalidClass, $invalidClass),
]);

if (!$block && !$customDataServerSideRender) {
	return;
}

if (!$block) {
	?>
	<div class="<?php echo esc_attr($customDataClass); ?>">
		<?php esc_html_e('Sorry, it looks like your Custom data block is not configured correctly. In order for the block to work, you must provide data using filters. Check the documentation for details.', 'eightshift-forms'); ?>

	</div>
<?php }

// Output form.
echo $block; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
