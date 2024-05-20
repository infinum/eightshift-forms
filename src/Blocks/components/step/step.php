<?php

/**
 * Template for the Step Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);
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

$stepClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
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

$stepAttrsOutput = '';
if ($stepAttrs) {
	foreach ($stepAttrs as $key => $value) {
		$stepAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

$prevButtonComponent = '';
$nextButtonComponent = '';

?>

<div class="<?php echo esc_attr($stepClass); ?>" <?php echo $stepAttrsOutput; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>>

	<div class="<?php echo esc_attr("{$componentClass}__debug-details"); ?>">
		<?php
		// translators: %s is replaced with the step name.
		echo sprintf(esc_html__('Step name: %s', 'eightshift-forms'), esc_html($stepName));
		?>
	</div>

	<div class="<?php echo esc_attr("{$componentClass}__inner"); ?>">
		<?php echo $stepContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>

		<div class="<?php echo esc_attr("{$componentFieldClass} {$componentClass}__navigation"); ?>">
			<div class="<?php echo esc_attr("{$componentFieldClass} {$componentClass}__navigation-inner"); ?>">
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
							'submitAttrs' => [
								UtilsHelper::getStateAttribute('submitStepDirection') => 'prev',
							],
						]),
						[
							'additionalFieldClass' => Helpers::classnames([
								Helpers::selector($componentFieldClass, $componentFieldClass, '', 'submit-prev'),
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
							'submitAttrs' => [
								UtilsHelper::getStateAttribute('submitStepDirection') => 'next',
							],
						]),
						[
							'additionalFieldClass' => Helpers::classnames([
								Helpers::selector($componentFieldClass, $componentFieldClass, '', 'submit-next'),
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
