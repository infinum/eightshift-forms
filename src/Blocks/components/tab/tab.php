<?php

/**
 * Template for the Tab Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);
$manifestTabs = Components::getComponent('tabs');

$componentClass = $manifest['componentClass'] ?? '';
$componentJsTabClass = $manifestTabs['componentJsTabClass'] ?? '';

$tabLabel = Components::checkAttr('tabLabel', $attributes, $manifest);
$tabContent = Components::checkAttr('tabContent', $attributes, $manifest);
$tabFull = Components::checkAttr('tabFull', $attributes, $manifest);

$tabLabelClass = Components::classnames([
	Components::selector($componentClass, $componentClass, 'label'),
	Components::selector($componentJsTabClass, $componentJsTabClass),
]);

$tabContentClass = Components::classnames([
	Components::selector($componentClass, $componentClass, 'content'),
	Components::selector($tabFull, $componentClass, 'content', 'full'),
]);

if (!$tabLabel || !$tabContent) {
	return;
}

?>

<button type="button" class="<?php echo esc_attr($tabLabelClass); ?>" data-hash="<?php echo rawurlencode($tabLabel); ?>">
	<?php echo esc_html($tabLabel); ?>
</button>

<div class="<?php echo esc_attr($tabContentClass); ?>">
	<?php echo $tabContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
</div>
