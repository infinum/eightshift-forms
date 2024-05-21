<?php

/**
 * Template for the Rating Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

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
$ratingHideLabel = false;

$ratingClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
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

$ratingAttrsOutput = '';
if ($ratingAttrs) {
	foreach ($ratingAttrs as $key => $value) {
		$ratingAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = UtilsGeneralHelper::getBlockAdditionalContentViaFilter('rating', $attributes);

$stars = '';

for ($i = 1; $i < $ratingAmount + 1; $i++) {
	$stars .= '
		<div
			class="' . esc_attr("{$componentClass}__star") . '"
			' . UtilsHelper::getStateAttribute('ratingValue') . '="' . $i . '"
		>
		' . UtilsHelper::getUtilsIcons('rating') .
		'</div>';
}

$rating = '
	<input
		class="' . esc_attr($componentClass) . '__input"
		name="' . esc_attr($ratingName) . '"
		id="' . esc_attr($ratingName) . '"
		value="' . esc_attr($ratingValue) . '"
		type="text"
		' . disabled($ratingIsDisabled, true, false) . '
		' . wp_readonly($ratingIsReadOnly, true, false) . '
	/>
	<div
	class="' . esc_attr($ratingClass) . '"
		' . $ratingAttrsOutput . '
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
			'fieldId' => $ratingName,
			'fieldName' => $ratingName,
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
