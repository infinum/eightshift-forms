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

$stepName = Components::checkAttr('stepName', $attributes, $manifest);
$stepContent = Components::checkAttr('stepContent', $attributes, $manifest);

$stepClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($componentJsClass, $componentJsClass),
]);

if (!$stepContent) {
	return;
}

?>

<div class="<?php echo esc_attr($stepClass); ?>" data-step-id="<?php echo esc_attr($stepName); ?>">
	<div class="<?php echo esc_attr("{$componentClass}__inner"); ?>">
		<?php echo $stepContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>

		<div class="<?php echo esc_attr("{$componentFieldClass} {$componentClass}__navigation"); ?>">
			<?php
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
			?>
		</div>
	</div>
</div>
