<?php

/**
 * Template for the Step Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifestField = Helpers::getComponent('field');

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;
$componentFieldClass = $manifestField['componentClass'] ?? '';

$stepName = Helpers::checkAttr('stepName', $attributes, $manifest);
$stepContent = Helpers::checkAttr('stepContent', $attributes, $manifest);
$stepSubmit = Helpers::checkAttr('stepSubmit', $attributes, $manifest);
$stepPrevLabel = Helpers::checkAttr('stepPrevLabel', $attributes, $manifest);
$stepNextLabel = Helpers::checkAttr('stepNextLabel', $attributes, $manifest);
$stepIsActive = Helpers::checkAttr('stepIsActive', $attributes, $manifest);
$stepTwSelectorsData = Helpers::checkAttr('stepTwSelectorsData', $attributes, $manifest);

$twClasses = FormsHelper::getTwSelectors($stepTwSelectorsData, ['step']);

$stepClass = Helpers::classnames([
	FormsHelper::getTwBase($twClasses, 'step', $componentClass),
	UtilsHelper::getStateSelector('step'),
	Helpers::selector($stepIsActive, UtilsHelper::getStateSelector('isActive')),
]);

if (!$stepContent) {
	return;
}

$stepAttrs = [];

if ($stepName) {
	$stepAttrs[UtilsHelper::getStateAttribute('stepId')] = esc_attr($stepName);
}

$prevButtonComponent = '';
$nextButtonComponent = '';

?>

<div
	class="<?php echo esc_attr($stepClass); ?>"
	<?php echo Helpers::getAttrsOutput($stepAttrs); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
	?>>

	<div class="<?php echo esc_attr(FormsHelper::getTwPart($twClasses, 'step', 'debug-details', "{$componentClass}__debug-details")); ?>">
		<?php
		// translators: %s is replaced with the step name.
		echo sprintf(esc_html__('Step name: %s', 'eightshift-forms'), esc_html($stepName));
		?>
	</div>

	<div class="<?php echo esc_attr(FormsHelper::getTwPart($twClasses, 'step', 'inner', "{$componentClass}__inner")); ?>">
		<?php echo $stepContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
		?>

		<div class="<?php echo esc_attr(FormsHelper::getTwPart($twClasses, 'step', 'navigation', "{$componentClass}__navigation {$componentFieldClass}")); ?>">
			<div class="<?php echo esc_attr(FormsHelper::getTwPart($twClasses, 'step', 'navigation-inner', "{$componentClass}__navigation-inner")); ?>">
				<?php

				$filterNameComponentPrev = UtilsHooksHelper::getFilterName(['block', 'step', 'component_prev']);

				if (has_filter($filterNameComponentPrev)) {
					$prevButtonComponent = apply_filters($filterNameComponentPrev, [
						'value' => esc_html($stepPrevLabel ?: __('Previous', 'eightshift-forms')), // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
						'jsSelector' => UtilsHelper::getStateSelector('stepSubmit'),
						'attributes' => $attributes,
					]);
				}

				echo Helpers::render(
					'submit',
					array_merge(
						Helpers::props('submit', $attributes, [
							'submitValue' => esc_html($stepPrevLabel ?: __('Previous', 'eightshift-forms')), // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
							'submitButtonComponent' => $prevButtonComponent,
							'submitButtonTwParent' => 'step-navigation-prev',
							'submitAttrs' => [
								UtilsHelper::getStateAttribute('submitStepDirection') => 'prev',
							],
						]),
						[
							'additionalFieldClass' => Helpers::classnames([
								FormsHelper::getTwPart($twClasses, 'step', 'navigation-prev', "{$componentFieldClass}--submit-prev"),
							]),
							'additionalClass' => UtilsHelper::getStateSelector('stepSubmit'),
						]
					)
				);

				$filterNameComponentNext = UtilsHooksHelper::getFilterName(['block', 'step', 'component_next']);

				if (has_filter($filterNameComponentNext)) {
					$nextButtonComponent = apply_filters($filterNameComponentNext, [
						'value' => esc_html($stepNextLabel ?: __('Next', 'eightshift-forms')), // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
						'jsSelector' => UtilsHelper::getStateSelector('stepSubmit'),
						'attributes' => $attributes,
					]);
				}

				echo Helpers::render(
					'submit',
					array_merge(
						Helpers::props('submit', $attributes, [
							'submitValue' => esc_html($stepNextLabel ?: __('Next', 'eightshift-forms')), // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
							'submitButtonComponent' => $nextButtonComponent,
							'submitButtonTwParent' => 'step-navigation-next',
							'submitAttrs' => [
								UtilsHelper::getStateAttribute('submitStepDirection') => 'next',
							],
						]),
						[
							'additionalFieldClass' => Helpers::classnames([
								FormsHelper::getTwPart($twClasses, 'step', 'navigation-next', "{$componentFieldClass}--submit-next"),
							]),
							'additionalClass' => UtilsHelper::getStateSelector('stepSubmit'),
						]
					)
				);

				echo $stepSubmit; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
				?>
			</div>
		</div>
	</div>
</div>
