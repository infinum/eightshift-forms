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


// Additional content filter.
$additionalContent = UtilsGeneralHelper::getBlockAdditionalContentViaFilter('rating', $attributes);

$stars = '';

$iconFilterName = UtilsHooksHelper::getFilterName(['block', 'rating', 'starIcon']);

for ($i = 1; $i < $ratingAmount + 1; $i++) {
	$icon = "<svg aria-hidden role='none' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' width='20' height='20' fill='none'><path d='M9.453 1.668a.75.75 0 0 1 1.345 0l2.239 4.536a.75.75 0 0 0 .564.41l5.006.728a.75.75 0 0 1 .416 1.28l-3.622 3.53a.75.75 0 0 0-.216.664l.855 4.986a.75.75 0 0 1-1.088.79l-4.478-2.353a.75.75 0 0 0-.698 0L5.3 18.593a.75.75 0 0 1-1.089-.79l.855-4.987a.75.75 0 0 0-.215-.664l-3.623-3.53a.75.75 0 0 1 .416-1.28l5.006-.727a.75.75 0 0 0 .565-.41l2.239-4.537Z' stroke='currentColor' fill='none'></path></svg>";
	$starLabel = sprintf(__('Star rating %s', 'eightshift-forms'), $i);
	$stars .= '
		<div
			aria-label="' . $starLabel . '"
			class="' . esc_attr(FormsHelper::getTwPart($twClasses, 'rating', 'star', "{$componentClass}__star")) . '"
			' . UtilsHelper::getStateAttribute('ratingValue') . '="' . $i . '"
		>
		' . apply_filters($iconFilterName, $icon, $attributes) .
		'</div>';
}

$rating = '
	<input
		class="' . esc_attr(FormsHelper::getTwPart($twClasses, 'rating', 'input', "{$componentClass}__input")) . '"
		name="' . esc_attr($ratingName) . '"
		id="' . esc_attr($ratingId) . '"
		value="' . esc_attr($ratingValue) . '"
		step="1"
		min="0"
		max="' . esc_attr($ratingAmount) . '"
		type="number"
		' . disabled($ratingIsDisabled, true, false) . '
		' . wp_readonly($ratingIsReadOnly, true, false) . '
	/>
	<div
	class="' . esc_attr($ratingClass) . '"
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
			'fieldAttrs' => $ratingFieldAttrs,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? ''
		]
	)
);
