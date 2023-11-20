<?php

/**
 * Template for the Step Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Hooks\Filters;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);
$manifestField = Components::getComponent('field');
$manifestCustomFormAttrs = Components::getSettings()['customFormAttrs'];

$componentClass = $manifest['componentClass'] ?? '';
$componentJsClass = $manifest['componentJsClass'] ?? '';
$componentJsTriggerClass = $manifest['componentJsTriggerClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;
$componentFieldClass = $manifestField['componentClass'] ?? '';

$stepName = Components::checkAttr('stepName', $attributes, $manifest);
$stepContent = Components::checkAttr('stepContent', $attributes, $manifest);
$stepSubmit = Components::checkAttr('stepSubmit', $attributes, $manifest);
$stepPrevLabel = Components::checkAttr('stepPrevLabel', $attributes, $manifest);
$stepNextLabel = Components::checkAttr('stepNextLabel', $attributes, $manifest);

$stepClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($componentJsClass, $componentJsClass),
]);

if (!$stepContent) {
	return;
}

$stepAttrs = [];

if ($stepName) {
	$stepAttrs[$manifestCustomFormAttrs['stepId']] = esc_attr($stepName);
}

$stepAttrsOutput = '';
if ($stepAttrs) {
	foreach ($stepAttrs as $key => $value) {
		$stepAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

$jsSelector = Components::selector($componentJsTriggerClass, $componentJsTriggerClass);

$prevButtonComponent = '';
$nextButtonComponent = '';

?>

<div class="<?php echo esc_attr($stepClass); ?>" <?php echo $stepAttrsOutput; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>>
	<div class="<?php echo esc_attr("{$componentClass}__inner"); ?>">
		<?php echo $stepContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>

		<div class="<?php echo esc_attr("{$componentFieldClass} {$componentClass}__navigation"); ?>">
			<div class="<?php echo esc_attr("{$componentFieldClass} {$componentClass}__navigation-inner"); ?>">
				<?php

				$filterNameComponentPrev = Filters::getFilterName(['block', 'step', 'component_prev']);

				if (has_filter($filterNameComponentPrev)) {
					$prevButtonComponent = apply_filters($filterNameComponentPrev, [
						'value' => esc_html($stepPrevLabel ?: __('Previous', 'eightshift-forms')), // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
						'jsSelector' => $jsSelector,
						'attributes' => $attributes,
					]);
				}

				echo Components::render(
					'submit',
					array_merge(
						Components::props('submit', $attributes, [
							'submitFieldHideLabel' => true,
							'submitValue' => esc_html($stepPrevLabel ?: __('Previous', 'eightshift-forms')), // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
							'submitButtonComponent' => $prevButtonComponent,
							'submitAttrs' => [
								$manifestCustomFormAttrs['submitStepDirection'] => 'prev',
							],
						]),
						[
							'additionalFieldClass' => Components::classnames([
								Components::selector($componentFieldClass, $componentFieldClass, '', 'submit-prev'),
							]),
							'additionalClass' => $jsSelector,
						]
					)
				);

				$filterNameComponentNext = Filters::getFilterName(['block', 'step', 'component_next']);

				if (has_filter($filterNameComponentNext)) {
					$nextButtonComponent = apply_filters($filterNameComponentNext, [
						'value' => esc_html($stepNextLabel ?: __('Next', 'eightshift-forms')), // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
						'jsSelector' => $jsSelector,
						'attributes' => $attributes,
					]);
				}

				echo Components::render(
					'submit',
					array_merge(
						Components::props('submit', $attributes, [
							'submitFieldHideLabel' => true,
							'submitValue' => esc_html($stepNextLabel ?: __('Next', 'eightshift-forms')), // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
							'submitButtonComponent' => $nextButtonComponent,
							'submitAttrs' => [
								$manifestCustomFormAttrs['submitStepDirection'] => 'next',
							],
						]),
						[
							'additionalFieldClass' => Components::classnames([
								Components::selector($componentFieldClass, $componentFieldClass, '', 'submit-next'),
							]),
							'additionalClass' => $jsSelector,
						]
					)
				);

				echo $stepSubmit; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
				?>
			</div>
		</div>
	</div>
</div>
