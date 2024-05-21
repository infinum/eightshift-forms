<?php

/**
 * Template for the progress bar component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$progressBarUse = Helpers::checkAttr('progressBarUse', $attributes, $manifest);

if (!$progressBarUse) {
	return;
}

$progressBarSteps = Helpers::checkAttr('progressBarSteps', $attributes, $manifest);

if (!$progressBarSteps) {
	return;
}

$progressBarMultiflowUse = Helpers::checkAttr('progressBarMultiflowUse', $attributes, $manifest);

$progressBarClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($progressBarMultiflowUse, $componentClass, '', 'multiflow'),
	Helpers::selector(!$progressBarMultiflowUse, UtilsHelper::getStateSelector('stepProgressBar')),
	Helpers::selector($progressBarMultiflowUse, UtilsHelper::getStateSelector('stepProgressBarMultiflow')),
	Helpers::selector(!$progressBarMultiflowUse, $componentClass, '', 'multistep'),
	Helpers::selector($additionalClass, $additionalClass),
]);

?>
<div class="<?php echo esc_attr($progressBarClass); ?>">
	<?php
	if (!$progressBarMultiflowUse) {
		echo Helpers::render(
			'multistep',
			[
				'steps' => $progressBarSteps,
				'componentClass' => $componentClass,
				'jsClass' => UtilsHelper::getStateSelector('stepProgressBar'),
				'hideLabels' => Helpers::checkAttr('progressBarHideLabels', $attributes, $manifest),
			],
			'components',
			false,
			"{$manifest['componentName']}/partials"
		);
	} else {
		echo Helpers::render(
			'multiflow',
			[
				'count' => Helpers::checkAttr('progressBarMultiflowInitCount', $attributes, $manifest),
			],
			'components',
			false,
			"{$manifest['componentName']}/partials"
		);
	}
	?>
</div>


