<?php

/**
 * Template for the radio Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentName = $manifest['componentName'] ?? '';
$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$radioValue = Helpers::checkAttr('radioValue', $attributes, $manifest);
if (!$radioValue) {
	return;
}

$radioLabel = Helpers::checkAttr('radioLabel', $attributes, $manifest);
$radioHideLabel = Helpers::checkAttr('radioHideLabel', $attributes, $manifest);
$radioHideLabelText = Helpers::checkAttr('radioHideLabelText', $attributes, $manifest);
$radioName = Helpers::checkAttr('radioName', $attributes, $manifest);
$radioIsChecked = Helpers::checkAttr('radioIsChecked', $attributes, $manifest);
$radioIsDisabled = Helpers::checkAttr('radioIsDisabled', $attributes, $manifest);
$radioSingleSubmit = Helpers::checkAttr('radioSingleSubmit', $attributes, $manifest);
$radioAttrs = Helpers::checkAttr('radioAttrs', $attributes, $manifest);
$radioFieldAttrs = Helpers::checkAttr('radioFieldAttrs', $attributes, $manifest);
$radioIcon = Helpers::checkAttr('radioIcon', $attributes, $manifest);
$radioIsHidden = Helpers::checkAttr('radioIsHidden', $attributes, $manifest);
$radioTwSelectorsData = Helpers::checkAttr('radioTwSelectorsData', $attributes, $manifest);

$twClasses = FormsHelper::getTwSelectors($radioTwSelectorsData, ['radio']);

$radioClass = Helpers::classnames([
	FormsHelper::getTwBase($twClasses, 'radio', $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	Helpers::selector($radioIsDisabled, UtilsHelper::getStateSelector('isDisabled')),
	Helpers::selector($radioIsHidden, UtilsHelper::getStateSelector('isHidden')),
]);

$radioInputClass = Helpers::classnames([
	FormsHelper::getTwPart($twClasses, 'radio', 'input', "{$componentClass}__input"),
	Helpers::selector($radioSingleSubmit, UtilsHelper::getStateSelectorAdmin('singleSubmit')),
]);

$radioAttrs['value'] = esc_attr($radioValue);

$conditionalTags = Helpers::render(
	'conditional-tags',
	Helpers::props('conditionalTags', $attributes)
);

if ($conditionalTags) {
	$radioFieldAttrs[UtilsHelper::getStateAttribute('conditionalTags')] = $conditionalTags;
}

$radioFieldAttrs[UtilsHelper::getStateAttribute('fieldName')] = $radioValue;

if ($componentName) {
	$radioFieldAttrs[UtilsHelper::getStateAttribute('fieldType')] = 'radio';
}

?>

<div
	class="<?php echo esc_attr($radioClass); ?>"
	<?php echo Helpers::getAttrsOutput($radioFieldAttrs); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
	?>>
	<div class="<?php echo esc_attr(FormsHelper::getTwPart($twClasses, 'radio', 'content', "{$componentClass}__content")); ?>">
		<input
			class="<?php echo esc_attr($radioInputClass); ?>"
			type="radio"
			name="<?php echo esc_attr($radioName); ?>"
			id="<?php echo esc_attr($radioName); ?>"
			<?php checked($radioIsChecked); ?>
			<?php disabled($radioIsDisabled); ?>
			<?php echo Helpers::getAttrsOutput($radioAttrs); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
			?> />
		<?php if (!$radioHideLabel) { ?>
			<label
				for="<?php echo esc_attr($radioName); ?>"
				class="<?php echo esc_attr(FormsHelper::getTwPart($twClasses, 'radio', 'label', "{$componentClass}__label")); ?>">
				<?php if ($radioIcon) { ?>
					<img class="<?php echo esc_attr(FormsHelper::getTwPart($twClasses, 'radio', 'label-icon', "{$componentClass}__label-icon")); ?>" src="<?php echo esc_url($radioIcon); ?>" alt="<?php echo esc_attr($radioLabel); ?>" />
				<?php } ?>

				<?php if (!$radioHideLabelText) { ?>
					<span class="<?php echo esc_attr(FormsHelper::getTwPart($twClasses, 'radio', 'label-inner', "{$componentClass}__label-inner")); ?>">
						<?php echo wp_kses_post(apply_filters('the_content', $radioLabel)); ?>
					</span>
				<?php } ?>
			</label>
		<?php } ?>
	</div>
</div>
