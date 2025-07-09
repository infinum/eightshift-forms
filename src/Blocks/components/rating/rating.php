<?php

/**
 * Template for the Rating Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$ratingName = Helpers::checkAttr('ratingName', $attributes, $manifest);
if (!$ratingName) {
	return;
}

$ratingValue = Helpers::checkAttr('ratingValue', $attributes, $manifest);
$ratingTypeCustom = Helpers::checkAttr('ratingTypeCustom', $attributes, $manifest);
$ratingIsDisabled = Helpers::checkAttr('ratingIsDisabled', $attributes, $manifest);
$ratingIsReadOnly = Helpers::checkAttr('ratingIsReadOnly', $attributes, $manifest);
$ratingIsRequired = Helpers::checkAttr('ratingIsRequired', $attributes, $manifest);
$ratingTracking = Helpers::checkAttr('ratingTracking', $attributes, $manifest);
$ratingAttrs = Helpers::checkAttr('ratingAttrs', $attributes, $manifest);
$ratingFieldAttrs = Helpers::checkAttr('ratingFieldAttrs', $attributes, $manifest);
$ratingAmount = Helpers::checkAttr('ratingAmount', $attributes, $manifest);
$ratingSingleSubmit = Helpers::checkAttr('ratingSingleSubmit', $attributes, $manifest);
$ratingTwSelectorsData = Helpers::checkAttr('ratingTwSelectorsData', $attributes, $manifest);
$ratingHideLabel = false;

$ratingId = $ratingName . '-' . Helpers::getUnique();

$twClasses = FormsHelper::getTwSelectors($ratingTwSelectorsData, ['rating']);

$ratingClass = Helpers::classnames([
	FormsHelper::getTwBase($twClasses, 'rating', $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	Helpers::selector($ratingSingleSubmit, UtilsHelper::getStateSelectorAdmin('singleSubmit')),
	UtilsHelper::getStateSelector('rating'),
]);

if (!$ratingValue || !is_numeric($ratingValue)) {
	$ratingValue = '';
}

if ($ratingAmount < $ratingValue) {
	$ratingValue = $ratingAmount;
}

$ratingAttrs[UtilsHelper::getStateAttribute('ratingValue')] = $ratingValue;

$ratingAttrs['role'] = 'radiogroup';


// Additional content filter.
$additionalContent = UtilsGeneralHelper::getBlockAdditionalContentViaFilter('rating', $attributes);

$stars = '';

$iconFilterName = UtilsHooksHelper::getFilterName(['block', 'rating', 'starIcon']);

for ($i = 1; $i < $ratingAmount + 1; $i++) {
	$stars .= '
		<input
			class="' . esc_attr(FormsHelper::getTwPart($twClasses, 'rating', 'star', "{$componentClass}__star")) . '"
			type="radio"
			name="' . esc_attr($ratingName) . '"
			id="' . esc_attr($ratingId . $i) . '"
			value="' . $i . '"
			' . disabled($ratingIsDisabled, true, false) . '
			' . checked($ratingValue, $i, false) . '
		/>';

	// translators: %s is the star rating number.
	$ariaLabel = sprintf(__('Star rating %s', 'eightshift-forms'), $i);

	$stars .= '
		<label
			for="' . esc_attr($ratingId . $i) . '"
			aria-label="' . esc_attr($ariaLabel) . '"
		>
		' . apply_filters($iconFilterName, UtilsHelper::getUtilsIcons('rating'), $attributes) . '
		</label>
	';
}

$rating = '
	<div class="' . esc_attr($ratingClass) . '"
		' . Helpers::getAttrsOutput($ratingAttrs) . '
	>
	' . $stars . '
	</div>
	' . $additionalContent . '
';

echo Helpers::render(
	'field',
	array_merge(
		Helpers::props('field', $attributes, [
			'fieldContent' => $rating,
			'fieldId' => $ratingId,
			'fieldName' => $ratingName,
			'fieldTwSelectorsData' => $ratingTwSelectorsData,
			'fieldTypeInternal' => FormsHelper::getStateFieldType('rating'),
			'fieldIsRequired' => $ratingIsRequired,
			'fieldDisabled' => !empty($ratingIsDisabled),
			'fieldTypeCustom' => $ratingTypeCustom ?: 'rating', // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			'fieldTracking' => $ratingTracking,
			'fieldHideLabel' => $ratingHideLabel,
			'fieldConditionalTags' => Helpers::render(
				'conditional-tags',
				Helpers::props('conditionalTags', $attributes)
			),
			'fieldAttrs' => array_merge($ratingFieldAttrs, ['role' => 'radiogroup']),
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? ''
		]
	)
);
