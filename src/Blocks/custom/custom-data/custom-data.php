<?php

/**
 * Template for the Custom Data Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\BlockCustomData;
use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$block = apply_filters(
	BlockCustomData::FILTER_BLOCK_CUSTOM_DATA_COMPONENT_NAME,
	$attributes
);

$customDataClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	Components::selector(!$block, $blockClass, '', 'invalid')
]);

if (!$block) {
	?>
	<div class="<?php echo esc_attr($customDataClass); ?>">
		<?php esc_html_e('Sorry, it looks like your custom data block not configured correctly. In order for this block to work you must provide data using our filters. Please check our documentation for details.', 'eightshift-forms'); ?>
	</div>
<?php }

// Output form.
echo $block; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
