<?php

/**
 * Template for the Tabs Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$componentJsClass = $manifest['componentJsClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$tabsContent = Components::checkAttr('tabsContent', $attributes, $manifest);
$tabsIsFirst = Components::checkAttr('tabsIsFirst', $attributes, $manifest);

$tabsClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($tabsIsFirst && $componentClass, $componentClass, '', 'first'),
	Components::selector($componentJsClass, $componentJsClass),
	Components::selector($additionalClass, $additionalClass),
]);

if (!$tabsContent) {
	return;
}

?>
<div class="<?php echo esc_attr($tabsClass); ?>">
	<?php
		echo $tabsContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
	?>
</div>
