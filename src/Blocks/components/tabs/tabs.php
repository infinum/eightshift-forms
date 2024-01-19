<?php

/**
 * Template for the Tabs Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$tabsContent = Components::checkAttr('tabsContent', $attributes, $manifest);

$tabsClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	UtilsHelper::getStateSelectorAdmin('tabs'),
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
