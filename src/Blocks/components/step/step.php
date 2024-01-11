<?php

/**
 * Template for the Step Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);
$manifestField = Components::getComponent('field');

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;
$componentFieldClass = $manifestField['componentClass'] ?? '';

$stepName = Components::checkAttr('stepName', $attributes, $manifest);
$stepContent = Components::checkAttr('stepContent', $attributes, $manifest);
$stepSubmit = Components::checkAttr('stepSubmit', $attributes, $manifest);
$stepPrevLabel = Components::checkAttr('stepPrevLabel', $attributes, $manifest);
$stepNextLabel = Components::checkAttr('stepNextLabel', $attributes, $manifest);
$stepIsActive = Components::checkAttr('stepIsActive', $attributes, $manifest);

$stepClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	UtilsHelper::getStateSelector('step'),
	Components::selector($stepIsActive, UtilsHelper::getStateSelector('isActive')),
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

<div class="<?php echo esc_attr($stepClass); ?>" <?php echo $stepAttrsOutput; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>>
	<div class="<?php echo esc_attr("{$componentClass}__inner"); ?>">
		<?php echo $stepContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>

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

				echo Components::render(
					'submit',
					array_merge(
						Components::props('submit', $attributes, [
							'submitValue' => esc_html($stepPrevLabel ?: __('Previous', 'eightshift-forms')), // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
							'submitButtonComponent' => $prevButtonComponent,
							'submitAttrs' => [
								UtilsHelper::getStateAttribute('submitStepDirection') => 'prev',
							],
						]),
						[
							'additionalFieldClass' => Components::classnames([
								Components::selector($componentFieldClass, $componentFieldClass, '', 'submit-prev'),
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

				echo Components::render(
					'submit',
					array_merge(
						Components::props('submit', $attributes, [
							'submitValue' => esc_html($stepNextLabel ?: __('Next', 'eightshift-forms')), // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
							'submitButtonComponent' => $nextButtonComponent,
							'submitAttrs' => [
								UtilsHelper::getStateAttribute('submitStepDirection') => 'next',
							],
						]),
						[
							'additionalFieldClass' => Components::classnames([
								Components::selector($componentFieldClass, $componentFieldClass, '', 'submit-next'),
							]),
							'additionalClass' => UtilsHelper::getStateSelector('stepSubmit'),
						]
					)
				);

				echo $stepSubmit; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
				?>
			</div>
		</div>
	</div>
</div>
