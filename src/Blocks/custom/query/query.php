<?php

/**
 * Template for the Query Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\BlockQuery;
use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$block = apply_filters(
	BlockQuery::FILTER_BLOCK_QUERY_COMPONENT_NAME,
	$attributes
);

$queryClass = Components::classnames([
	Components::selector($blockClass, $blockClass),
	Components::selector(!$block, $blockClass, '', 'invalid')
]);

if (!$block) {
	?>
	<div class="<?php echo esc_attr($queryClass); ?>">
		<?php esc_html_e('Sorry, it looks like your query block not configured correctly. In order for this block to work you must provide data using our filters. Please check our documentation for details.', 'eightshift-forms'); ?>
	</div>
<?php }

// Output form.
echo $block; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
