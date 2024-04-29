<?php

/**
 * Template for the progress bar component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifestByDir(__DIR__);

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
	Components::selector(!$progressBarMultiflowUse, UtilsHelper::getStateSelector('stepProgressBar')),
	Components::selector($progressBarMultiflowUse, UtilsHelper::getStateSelector('stepProgressBarMultiflow')),
	Components::selector(!$progressBarMultiflowUse, $componentClass, '', 'multistep'),
	Components::selector($additionalClass, $additionalClass),
]);

?>
<div class="<?php echo esc_attr($progressBarClass); ?>">
	<?php
	if (!$progressBarMultiflowUse) {
		echo Components::render(
			'multistep',
			[
				'steps' => $progressBarSteps,
				'componentClass' => $componentClass,
				'jsClass' => UtilsHelper::getStateSelector('stepProgressBar'),
				'hideLabels' => Components::checkAttr('progressBarHideLabels', $attributes, $manifest),
			],
			'components',
			false,
			"{$manifest['componentName']}/partials"
		);
	} else {
		echo Components::render(
			'multiflow',
			[
				'count' => Components::checkAttr('progressBarMultiflowInitCount', $attributes, $manifest),
			],
			'components',
			false,
			"{$manifest['componentName']}/partials"
		);
	}
	?>
</div>


