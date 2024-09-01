<?php

/**
 * Template for the progress bar component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
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
$progressBarTwSelectorsData = Helpers::checkAttr('progressBarTwSelectorsData', $attributes, $manifest);

$twClasses = FormsHelper::getTwSelectors($progressBarTwSelectorsData, ['progress-bar'], $attributes);

$progressBarClass = Helpers::classnames([
	FormsHelper::getTwBase($twClasses, 'progress-bar', $componentClass),
	$progressBarMultiflowUse ? FormsHelper::getTwPart($twClasses, 'progress-bar', 'multiflow', "{$componentClass}--multiflow") : FormsHelper::getTwPart($twClasses, 'progress-bar', 'multistep', "{$componentClass}--multistep"),
	Helpers::selector($progressBarMultiflowUse, $componentClass, '', 'multiflow'),
	Helpers::selector(!$progressBarMultiflowUse, UtilsHelper::getStateSelector('stepProgressBar')),
	Helpers::selector($progressBarMultiflowUse, UtilsHelper::getStateSelector('stepProgressBarMultiflow')),
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
				'twClasses' => $twClasses,
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
				'twClasses' => $twClasses,
			],
			'components',
			false,
			"{$manifest['componentName']}/partials"
		);
	}
	?>
</div>


