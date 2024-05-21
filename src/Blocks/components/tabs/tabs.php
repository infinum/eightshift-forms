<?php

/**
 * Template for the Tabs Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$tabsContent = Helpers::checkAttr('tabsContent', $attributes, $manifest);

$tabsClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	UtilsHelper::getStateSelectorAdmin('tabs'),
]);

if (!$tabsContent) {
	return;
}

?>
<div class="<?php echo esc_attr($tabsClass); ?>">
	<?php
	echo $tabsContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
	?>
</div>
