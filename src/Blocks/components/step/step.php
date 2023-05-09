<?php

/**
 * Template for the Step Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);
$manifestField = Components::getComponent('field');

$componentClass = $manifest['componentClass'] ?? '';
$componentJsClass = $manifest['componentJsClass'] ?? '';
$componentJsTriggerClass = $manifest['componentJsTriggerClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;
$componentFieldClass = $manifestField['componentClass'] ?? '';

$stepId = Components::checkAttr('stepId', $attributes, $manifest);
$stepContent = Components::checkAttr('stepContent', $attributes, $manifest);
$stepTotal = Components::checkAttr('stepTotal', $attributes, $manifest);

$stepClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($stepId === 0, 'is-active'),
	Components::selector($componentJsClass, $componentJsClass),
]);

if (!$stepContent) {
	return;
}

?>

<div class="<?php echo esc_attr($stepClass); ?>" data-step-id="step-<?php echo esc_attr($stepId); ?>">
	<div class="<?php echo esc_attr("{$componentClass}__inner"); ?>">
		<?php echo $stepContent; ?>

		<?php
		if ($stepId !== 0) {
			echo Components::render(
				'submit',
				array_merge(
					Components::props('submit', $attributes, [
						'submitFieldHideLabel' => true,
						'submitValue' => esc_html__('Previous', 'eightshift-form'),
						'submitAttrs' => [
							'data-step-direction' => 'prev',
						],
					]),
					[
						'additionalFieldClass' => Components::classnames([
							Components::selector($componentFieldClass, $componentFieldClass, '', 'submit-next'),
						]),
						'additionalClass' => Components::classnames([
							Components::selector($componentJsTriggerClass, $componentJsTriggerClass),
						]),
					]
				)
			);
		}
		?>

		<?php
		if ($stepId < $stepTotal) {
			echo Components::render(
				'submit',
				array_merge(
					Components::props('submit', $attributes, [
						'submitFieldHideLabel' => true,
						'submitValue' => esc_html__('Next', 'eightshift-form'),
						'submitAttrs' => [
							'data-step-direction' => 'next',
						],
					]),
					[
						'additionalFieldClass' => Components::classnames([
							Components::selector($componentFieldClass, $componentFieldClass, '', 'submit-next'),
						]),
						'additionalClass' => Components::classnames([
							Components::selector($componentJsTriggerClass, $componentJsTriggerClass),
						]),
					]
				)
			);
		}
		?>
	</div>
</div>
