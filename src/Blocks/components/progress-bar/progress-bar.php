<?php

/**
 * Template for the progress bar component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Helper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$progressBarUse = Components::checkAttr('progressBarUse', $attributes, $manifest);

if (!$progressBarUse) {
	return;
}

$progressBarSteps = Components::checkAttr('progressBarSteps', $attributes, $manifest);

if (!$progressBarSteps) {
	return;
}

$progressBarMultiflowUse = Components::checkAttr('progressBarMultiflowUse', $attributes, $manifest);

$progressBarClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($progressBarMultiflowUse, $componentClass, '', 'multiflow'),
	Components::selector(!$progressBarMultiflowUse, Helper::getStateSelector('stepProgressBar')),
	Components::selector($progressBarMultiflowUse, Helper::getStateSelector('stepProgressBarMultiflow')),
	Components::selector(!$progressBarMultiflowUse, $componentClass, '', 'multistep'),
	Components::selector($additionalClass, $additionalClass),
]);

?>
<div class="<?php echo esc_attr($progressBarClass); ?>">
	<?php
	if (!$progressBarMultiflowUse) {
		echo Components::renderPartial('component', $manifest['componentName'], 'multistep', [  // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
			'steps' => $progressBarSteps,
			'componentClass' => $componentClass,
			'jsClass' => Helper::getStateSelector('stepProgressBar'),
			'hideLabels' => Components::checkAttr('progressBarHideLabels', $attributes, $manifest),
		]);
	} else {
		echo Components::renderPartial('component', $manifest['componentName'], 'multiflow', [  // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
			'count' => Components::checkAttr('progressBarMultiflowInitCount', $attributes, $manifest),
		]);
	}
	?>
</div>


