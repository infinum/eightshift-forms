<?php

/**
 * Template for the Field Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$fieldUse = Helpers::checkAttr('fieldUse', $attributes, $manifest);
if (!$fieldUse) {
	return;
}

$fieldContent = Helpers::checkAttr('fieldContent', $attributes, $manifest);
$fieldSkip = Helpers::checkAttr('fieldSkip', $attributes, $manifest);

// Enable option to skip field and just render content.
if ($fieldSkip) {
	echo $fieldContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
	return;
}

$componentClass = $manifest['componentClass'] ?? '';
$additionalFieldClass = $attributes['additionalFieldClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;
$blockName = $attributes['blockName'] ?? '';

$unique = Helpers::getUnique();

$fieldLabel = Helpers::checkAttr('fieldLabel', $attributes, $manifest);
$fieldHideLabel = Helpers::checkAttr('fieldHideLabel', $attributes, $manifest);
$fieldId = Helpers::checkAttr('fieldId', $attributes, $manifest);
$fieldName = Helpers::checkAttr('fieldName', $attributes, $manifest);
$fieldBeforeContent = Helpers::checkAttr('fieldBeforeContent', $attributes, $manifest);
$fieldAfterContent = Helpers::checkAttr('fieldAfterContent', $attributes, $manifest);
$fieldSuffixContent = Helpers::checkAttr('fieldSuffixContent', $attributes, $manifest);
$fieldType = Helpers::checkAttr('fieldType', $attributes, $manifest);
$fieldUseError = Helpers::checkAttr('fieldUseError', $attributes, $manifest);
$fieldUseTooltip = Helpers::checkAttr('fieldUseTooltip', $attributes, $manifest);
$fieldHelp = Helpers::checkAttr('fieldHelp', $attributes, $manifest);
$fieldDisabled = Helpers::checkAttr('fieldDisabled', $attributes, $manifest);
$fieldHidden = Helpers::checkAttr('fieldHidden', $attributes, $manifest);
$fieldStyle = Helpers::checkAttr('fieldStyle', $attributes, $manifest);
$fieldAttrs = Helpers::checkAttr('fieldAttrs', $attributes, $manifest);
$fieldAttrsLabel = Helpers::checkAttr('fieldAttrsLabel', $attributes, $manifest);
$fieldIsRequired = Helpers::checkAttr('fieldIsRequired', $attributes, $manifest);
$fieldConditionalTags = Helpers::checkAttr('fieldConditionalTags', $attributes, $manifest);
$fieldInlineBeforeAfterContent = Helpers::checkAttr('fieldInlineBeforeAfterContent', $attributes, $manifest);
$fieldIsFiftyFiftyHorizontal = Helpers::checkAttr('fieldIsFiftyFiftyHorizontal', $attributes, $manifest);
$fieldTypeCustom = Helpers::checkAttr('fieldTypeCustom', $attributes, $manifest);
$fieldTracking = Helpers::checkAttr('fieldTracking', $attributes, $manifest);
$fieldTypeInternal = Helpers::checkAttr('fieldTypeInternal', $attributes, $manifest);
$fieldIsNoneFormBlock = Helpers::checkAttr('fieldIsNoneFormBlock', $attributes, $manifest);
$fieldTwSelectorsData = Helpers::checkAttr('fieldTwSelectorsData', $attributes, $manifest);

$fieldStyleOutput = [];
$filterName = UtilsHooksHelper::getFilterName(['block', 'field', 'styleClasses']);

if (has_filter($filterName)) {
	$fieldStyleOutputFilter = apply_filters($filterName, $attributes) ?? [];

	if ($fieldStyleOutputFilter) {
		$fieldStyleOutput = $fieldStyleOutputFilter[$blockName] ?? [];
	}
}

if ($fieldStyle && gettype($fieldStyle) === 'array') {
	$fieldStyleOutput = array_map(
		static function ($item) use ($componentClass) {
			return Helpers::selector(true, $componentClass, '', $item);
		},
		$fieldStyle
	);
}

$twClasses = FormsHelper::getTwSelectors($fieldTwSelectorsData, [
	'field',
	$fieldTypeInternal,
	$selectorClass,
]);

$fieldClass = Helpers::classnames([
	FormsHelper::getTwBase($twClasses, 'field', $componentClass),
	FormsHelper::getTwPart($twClasses, $selectorClass, 'field', "{$componentClass}--{$selectorClass}"),
	Helpers::selector($additionalFieldClass, $additionalFieldClass),
	Helpers::selector($fieldDisabled, UtilsHelper::getStateSelector('isDisabled')),
	Helpers::selector($fieldHidden, UtilsHelper::getStateSelector('isHidden')),
	Helpers::selector($fieldIsRequired && $componentClass, $componentClass, '', 'is-required'),
	UtilsHelper::getStateSelector('field'),
	Helpers::selector($fieldIsNoneFormBlock, UtilsHelper::getStateSelector('fieldNoFormsBlock')),
	Helpers::selector($fieldInlineBeforeAfterContent && $componentClass, $componentClass, '', 'inline-before-after-content'),
	Helpers::selector($fieldIsFiftyFiftyHorizontal && $componentClass, $componentClass, '', 'fifty-fifty-horizontal'),
	...$fieldStyleOutput,
]);

$labelClass = Helpers::classnames([
	FormsHelper::getTwPart($twClasses, 'field', 'label', "{$componentClass}__label"),
	FormsHelper::getTwPart($twClasses, $selectorClass, 'field-label'),
	Helpers::selector($fieldIsRequired && $componentClass, $componentClass, 'label', 'is-required'),
]);

$labelInnerClass = Helpers::classnames([
	FormsHelper::getTwPart($twClasses, 'field', 'label-inner', "{$componentClass}__label-inner"),
	FormsHelper::getTwPart($twClasses, $selectorClass, 'field-label-inner'),
]);

$innerClass = Helpers::classnames([
	FormsHelper::getTwPart($twClasses, 'field', 'inner', "{$componentClass}__inner"),
	FormsHelper::getTwPart($twClasses, $selectorClass, 'field-inner'),
]);

$contentClass = Helpers::classnames([
	FormsHelper::getTwPart($twClasses, 'field', 'content', "{$componentClass}__content"),
	FormsHelper::getTwPart($twClasses, $selectorClass, 'field-content'),
]);

$beforeContentClass = Helpers::classnames([
	FormsHelper::getTwPart($twClasses, 'field', 'before-content', "{$componentClass}__before-content"),
	FormsHelper::getTwPart($twClasses, $selectorClass, 'field-before-content'),
]);

$suffixContentClass = Helpers::classnames([
	FormsHelper::getTwPart($twClasses, 'field', 'suffix-content', "{$componentClass}__suffix-content"),
	FormsHelper::getTwPart($twClasses, $selectorClass, 'field-suffix-content'),
]);

$afterContentClass = Helpers::classnames([
	FormsHelper::getTwPart($twClasses, 'field', 'after-content', "{$componentClass}__after-content"),
	FormsHelper::getTwPart($twClasses, $selectorClass, 'field-after-content'),
]);

$helpClass = Helpers::classnames([
	FormsHelper::getTwPart($twClasses, 'field', 'help', "{$componentClass}__help"),
	FormsHelper::getTwPart($twClasses, $selectorClass, 'field-help'),
]);

$contentWrapClass = Helpers::classnames([
	FormsHelper::getTwPart($twClasses, 'field', 'content-wrap', "{$componentClass}__content-wrap"),
	FormsHelper::getTwPart($twClasses, $selectorClass, 'field-content-wrap'),
]);

if ($fieldType === 'fieldset') {
	$fieldTag = 'fieldset';
	$labelTag = 'legend';
	$fieldAttrsLabel['id'] = $fieldId;
} else {
	$fieldTag = 'div';
	$labelTag = 'label';
	$fieldAttrsLabel['for'] = $fieldId;
}

if ($fieldConditionalTags) {
	$fieldAttrs[UtilsHelper::getStateAttribute('conditionalTags')] = $fieldConditionalTags;
}

if ($fieldName) {
	$fieldAttrs[UtilsHelper::getStateAttribute('fieldName')] = $fieldName;
}

if ($fieldTypeInternal) {
	$fieldAttrs[UtilsHelper::getStateAttribute('fieldType')] = $fieldTypeInternal;
}

if ($fieldTypeCustom) {
	$fieldAttrs[UtilsHelper::getStateAttribute('fieldTypeCustom')] = $fieldTypeCustom;
}

if ($fieldTracking) {
	$fieldAttrs[UtilsHelper::getStateAttribute('tracking')] = $fieldTracking;
}

// Additional content filter.
$additionalContent = UtilsGeneralHelper::getBlockAdditionalContentViaFilter('field', $attributes);
?>

<<?php echo esc_attr($fieldTag); ?>
	class="<?php echo esc_attr($fieldClass); ?>"
	data-id="<?php echo esc_attr($unique); ?>"
	<?php echo Helpers::getAttrsOutput($fieldAttrs); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
	?>>

	<?php
	echo Helpers::outputCssVariables($attributes, $manifest, $unique, '', FormsHelper::getProjectSettings());

	echo Helpers::render(
		'debug-field-details',
		[
			'name' => Helpers::checkAttr('fieldName', $attributes, $manifest),
		],
		'components',
		false,
		'utils/partials'
	);

	?>
	<div class="<?php echo esc_attr($innerClass); ?>">
		<?php if ($fieldLabel && !$fieldHideLabel) { ?>
			<<?php echo esc_attr($labelTag); ?>
				class="<?php echo esc_attr($labelClass); ?>"
				<?php
				echo Helpers::getAttrsOutput($fieldAttrsLabel); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
				?>>
				<span class="<?php echo esc_attr($labelInnerClass); ?>">
					<?php echo wp_kses_post($fieldLabel); ?>

					<?php
					if ($fieldUseTooltip) {
						echo Helpers::render(
							'tooltip',
							Helpers::props('tooltip', $attributes, [
								'selectorClass' => $componentClass
							])
						);
					}
					?>
				</span>
			</<?php echo esc_attr($labelTag); ?>>
		<?php } ?>
		<div class="<?php echo esc_attr($contentClass); ?>">
			<?php if ($fieldBeforeContent) { ?>
				<div class="<?php echo esc_attr($beforeContentClass); ?>">
					<?php echo $fieldBeforeContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
					?>
				</div>
			<?php } ?>
			<div class="<?php echo esc_attr($contentWrapClass); ?>">
				<?php echo $fieldContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
				?>

				<?php if ($fieldSuffixContent) { ?>
					<div class="<?php echo esc_attr($suffixContentClass); ?>">
						<?php echo $fieldSuffixContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
						?>
					</div>
				<?php } ?>
			</div>
			<?php if ($fieldAfterContent) { ?>
				<div class="<?php echo esc_attr($afterContentClass); ?>">
					<?php echo $fieldAfterContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
					?>
				</div>
			<?php } ?>
		</div>
		<?php if ($fieldHelp) { ?>
			<div class="<?php echo esc_attr($helpClass); ?>">
				<?php echo $fieldHelp; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
				?>
			</div>
		<?php } ?>
		<?php
		if ($fieldUseError) {
			echo Helpers::render(
				'error',
				Helpers::props('error', $attributes, [
					'errorId' => $fieldId,
					'selectorClass' => $componentClass,
					'additionalClass' => Helpers::classnames([
						FormsHelper::getTwPart($twClasses, 'field', 'error'),
						FormsHelper::getTwPart($twClasses, $selectorClass, 'field-error'),
					]),
				]),
			);
		}
		?>
	</div>

	<?php echo $additionalContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
	?>
</<?php echo esc_attr($fieldTag); ?>>
