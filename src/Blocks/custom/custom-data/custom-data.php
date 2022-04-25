<?php

/**
 * Template for the Custom Data Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\BlockCustomData;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

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
		<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
			<ellipse cx="10" cy="4.13824" rx="7.25" ry="2.63824" stroke="currentColor" stroke-width="1.5"/>
			<path d="M17.25 8.19708C17.25 9.65414 14.0041 10.8353 10 10.8353C5.99594 10.8353 2.75 9.65414 2.75 8.19708" stroke="currentColor" stroke-width="1.5"/>
			<path d="M17.25 12.1544C17.25 13.6115 14.0041 14.7927 10 14.7927C5.99594 14.7927 2.75 13.6115 2.75 12.1544" stroke="currentColor" stroke-width="1.5"/>
			<path d="M17.25 3.93524V16.1117C17.25 17.5688 14.0041 18.7499 10 18.7499C5.99594 18.7499 2.75 17.5688 2.75 16.1117V3.93524" stroke="currentColor" stroke-width="1.5"/>
		</svg>
		<br />
		<b><?php esc_html_e('Custom data is not configured correctly.', 'eightshift-forms'); ?></b>
		<br />
		<?php esc_html_e('For this block to work, data needs to be provided through filters. Check the documentation for more details.', 'eightshift-forms'); ?>
	</div>
<?php }

// Output form.
echo $block; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
