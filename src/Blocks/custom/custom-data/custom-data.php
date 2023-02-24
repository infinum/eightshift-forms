<?php

/**
 * Template for the Custom Data Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\BlockCustomData;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);
$manifestInvalid = Components::getComponent('invalid');
$manifestUtils = Components::getComponent('utils');

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
		<?php echo $manifestUtils['icons']['database']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
		<br />
		<b><?php esc_html_e('Custom data is not configured correctly.', 'eightshift-forms'); ?></b>
		<br />
		<?php esc_html_e('For this block to work, data needs to be provided through filters. Check the documentation for more details.', 'eightshift-forms'); ?>
	</div>
<?php }

// Output form.
echo $block; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
