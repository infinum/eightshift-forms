<?php

/**
 * Template for the radio Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

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

$radioClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	Helpers::selector($radioIsDisabled, UtilsHelper::getStateSelector('isDisabled')),
	Helpers::selector($radioIsHidden, UtilsHelper::getStateSelector('isHidden')),
]);

$radioInputClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass, 'input'),
	Helpers::selector($radioSingleSubmit, UtilsHelper::getStateSelectorAdmin('singleSubmit')),
]);

$radioAttrs['value'] = esc_attr($radioValue);

$radioAttrsOutput = '';
if ($radioAttrs) {
	foreach ($radioAttrs as $key => $value) {
		$radioAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

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

$radioFieldAttrsOutput = '';
if ($radioFieldAttrs) {
	foreach ($radioFieldAttrs as $key => $value) {
		$radioFieldAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

?>

<div class="<?php echo esc_attr($radioClass); ?>" <?php echo $radioFieldAttrsOutput; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>>
	<div class="<?php echo esc_attr("{$componentClass}__content"); ?>">
		<input
			class="<?php echo esc_attr($radioInputClass); ?>"
			type="radio"
			name="<?php echo esc_attr($radioName); ?>"
			id="<?php echo esc_attr($radioName); ?>"
			<?php checked($radioIsChecked); ?>
			<?php disabled($radioIsDisabled); ?>
			<?php echo $radioAttrsOutput; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>
		/>
		<?php if (!$radioHideLabel) { ?>
			<label
				for="<?php echo esc_attr($radioName); ?>"
				class="<?php echo esc_attr("{$componentClass}__label"); ?>"
			>
				<?php if ($radioIcon) { ?>
					<img  class="<?php echo esc_attr("{$componentClass}__label-icon"); ?>" src="<?php echo esc_url($radioIcon); ?>" alt="<?php echo esc_attr($radioLabel); ?>" />
				<?php } ?>

				<?php if (!$radioHideLabelText) { ?>
					<span class="<?php echo esc_attr("{$componentClass}__label-inner"); ?>">
						<?php echo wp_kses_post(apply_filters('the_content', $radioLabel)); ?>
					</span>
				<?php } ?>
			</label>
		<?php } ?>
	</div>
</div>
