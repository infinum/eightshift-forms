<?php

/**
 * Template for the Field Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$fieldUse = Components::checkAttr('fieldUse', $attributes, $manifest);
if (!$fieldUse) {
	return;
}

$fieldContent = Components::checkAttr('fieldContent', $attributes, $manifest);
$fieldSkip = Components::checkAttr('fieldSkip', $attributes, $manifest);

// Enable option to skip field and just render content.
if ($fieldSkip) {
	echo $fieldContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
	return;
}

$componentClass = $manifest['componentClass'] ?? '';
$additionalFieldClass = $attributes['additionalFieldClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;
$blockJsClass = $attributes['blockJsClass'] ?? '';
$blockName = $attributes['blockName'] ?? '';
$componentJsClass = $manifest['componentJsClass'] ?? '';

// Update media breakpoints from the filter.
$filterName = Filters::getFilterName(['blocks', 'mediaBreakpoints']);

if (has_filter($filterName)) {
	$customMediaBreakpoints = apply_filters($filterName, []);

	if (
		is_array($customMediaBreakpoints) &&
		isset($customMediaBreakpoints['mobile']) &&
		isset($customMediaBreakpoints['tablet']) &&
		isset($customMediaBreakpoints['desktop']) &&
		isset($customMediaBreakpoints['large'])
	) {
			Components::setSettingsGlobalVariablesBreakpoints($customMediaBreakpoints);
	}
}

$unique = Components::getUnique();

$fieldLabel = Components::checkAttr('fieldLabel', $attributes, $manifest);
$fieldHideLabel = Components::checkAttr('fieldHideLabel', $attributes, $manifest);
$fieldId = Components::checkAttr('fieldId', $attributes, $manifest);
$fieldName = Components::checkAttr('fieldName', $attributes, $manifest);
$fieldBeforeContent = Components::checkAttr('fieldBeforeContent', $attributes, $manifest);
$fieldAfterContent = Components::checkAttr('fieldAfterContent', $attributes, $manifest);
$fieldType = Components::checkAttr('fieldType', $attributes, $manifest);
$fieldUseError = Components::checkAttr('fieldUseError', $attributes, $manifest);
$fieldUseTooltip = Components::checkAttr('fieldUseTooltip', $attributes, $manifest);
$fieldHelp = Components::checkAttr('fieldHelp', $attributes, $manifest);
$fieldDisabled = Components::checkAttr('fieldDisabled', $attributes, $manifest);
$fieldHidden = Components::checkAttr('fieldHidden', $attributes, $manifest);
$fieldStyle = Components::checkAttr('fieldStyle', $attributes, $manifest);
$fieldUniqueId = Components::checkAttr('fieldUniqueId', $attributes, $manifest);
$fieldAttrs = Components::checkAttr('fieldAttrs', $attributes, $manifest);
$fieldIsRequired = Components::checkAttr('fieldIsRequired', $attributes, $manifest);
$fieldConditionalTags = Components::checkAttr('fieldConditionalTags', $attributes, $manifest);
$fieldInlineBeforeAfterContent = Components::checkAttr('fieldInlineBeforeAfterContent', $attributes, $manifest);
$fieldIsFiftyFiftyHorizontal = Components::checkAttr('fieldIsFiftyFiftyHorizontal', $attributes, $manifest);

$fieldClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($componentClass, $componentClass, '', $selectorClass),
	Components::selector($additionalFieldClass, $additionalFieldClass),
	Components::selector($fieldDisabled, $componentClass, '', 'disabled'),
	Components::selector($fieldHidden, $componentClass, '', 'hidden'),
	Components::selector($blockJsClass, $blockJsClass),
	Components::selector($componentJsClass, $componentJsClass),
	Components::selector($fieldStyle && $componentClass, $componentClass, '', $fieldStyle),
	Components::selector($fieldInlineBeforeAfterContent && $componentClass, $componentClass, '', 'inline-before-after-content'),
	Components::selector($fieldIsFiftyFiftyHorizontal && $componentClass, $componentClass, '', 'fifty-fifty-horizontal'),
]);

$labelClass = Components::classnames([
	Components::selector($componentClass, $componentClass, 'label'),
	Components::selector($fieldIsRequired && $componentClass, $componentClass, 'label', 'is-required'),
]);

$fieldTag = 'div';
$labelTag = 'label';

if ($fieldType === 'fieldset') {
	$fieldTag = 'fieldset';
	$labelTag = 'legend';
}

if ($fieldConditionalTags) {
	$fieldAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['conditionalTags']] = $fieldConditionalTags;
}

if ($fieldName) {
	$fieldAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['fieldName']] = $fieldName;
}

if ($blockName) {
	$fieldAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['fieldType']] = $blockName;
}

$fieldAttrsOutput = '';
if ($fieldAttrs) {
	foreach ($fieldAttrs as $key => $value) {
		$fieldAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = Helper::getBlockAdditionalContentViaFilter('field', $attributes);
?>

<<?php echo esc_attr($fieldTag); ?>
	class="<?php echo esc_attr($fieldClass); ?>"
	data-id="<?php echo esc_attr($unique); ?>"
	<?php echo $fieldAttrsOutput; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
>

	<?php
	if ($fieldUniqueId) {
		echo Components::outputCssVariables($attributes, $manifest, $fieldUniqueId, [], 'wp-block');
	} else {
		echo Components::outputCssVariables($attributes, $manifest, $unique);
	}

	?>
	<div class="<?php echo esc_attr("{$componentClass}__inner"); ?>">
		<?php if ($fieldLabel && !$fieldHideLabel) { ?>
			<<?php echo esc_attr($labelTag); ?>
				class="<?php echo esc_attr($labelClass); ?>"
				for="<?php echo esc_attr($fieldId); ?>"
			>
				<span class="<?php echo esc_attr("{$componentClass}__label-inner"); ?>">
					<?php echo wp_kses_post($fieldLabel); ?>

					<?php
					if ($fieldUseTooltip) {
						echo Components::render(
							'tooltip',
							Components::props('tooltip', $attributes, [
								'selectorClass' => $componentClass
							])
						);
					}
					?>
				</span>
			</<?php echo esc_attr($labelTag); ?>>
		<?php } ?>
		<div class="<?php echo esc_attr("{$componentClass}__content"); ?>">
			<?php if ($fieldBeforeContent) { ?>
				<div class="<?php echo esc_attr("{$componentClass}__before-content"); ?>">
					<?php echo $fieldBeforeContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
				</div>
			<?php } ?>
			<div class="<?php echo esc_attr("{$componentClass}__content-wrap"); ?>">
				<?php echo $fieldContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
			</div>
			<?php if ($fieldAfterContent) { ?>
				<div class="<?php echo esc_attr("{$componentClass}__after-content"); ?>">
					<?php echo $fieldAfterContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
				</div>
			<?php } ?>
		</div>
		<?php if ($fieldHelp) { ?>
			<div class="<?php echo esc_attr("{$componentClass}__help"); ?>">
				<?php echo $fieldHelp; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
			</div>
		<?php } ?>
		<?php
		if ($fieldUseError) {
			echo Components::render(
				'error',
				Components::props('error', $attributes, [
					'errorId' => $fieldId,
					'selectorClass' => $componentClass
				])
			);
		}
		?>
	</div>

	<?php echo $additionalContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
</<?php echo esc_attr($fieldTag); ?>>
