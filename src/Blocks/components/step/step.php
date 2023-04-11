<?php

/**
 * Template for the Step Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$stepClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
]);

$stepName = Components::checkAttr('stepName', $attributes, $manifest);
$stepContent = Components::checkAttr('stepContent', $attributes, $manifest);

if (!$stepContent) {
	return;
}

?>

<div class="<?php echo esc_attr($stepClass); ?>">
	<?php echo $stepContent; ?>
</div>
