<?php

/**
 * Template for the Rating Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Helper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);
$icons = Components::getComponent('utils')['icons'];

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$ratingName = Components::checkAttr('ratingName', $attributes, $manifest);
if (!$ratingName) {
	return;
}

$ratingValue = Components::checkAttr('ratingValue', $attributes, $manifest);
$ratingTypeCustom = Components::checkAttr('ratingTypeCustom', $attributes, $manifest);
$ratingIsDisabled = Components::checkAttr('ratingIsDisabled', $attributes, $manifest);
$ratingIsReadOnly = Components::checkAttr('ratingIsReadOnly', $attributes, $manifest);
$ratingIsRequired = Components::checkAttr('ratingIsRequired', $attributes, $manifest);
$ratingTracking = Components::checkAttr('ratingTracking', $attributes, $manifest);
$ratingAttrs = Components::checkAttr('ratingAttrs', $attributes, $manifest);
$ratingFieldAttrs = Components::checkAttr('ratingFieldAttrs', $attributes, $manifest);
$ratingAmount = Components::checkAttr('ratingAmount', $attributes, $manifest);
$ratingHideLabel = false;

$ratingClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Helper::getStateSelector('rating'),
]);

if (!$ratingValue || !is_numeric($ratingValue)) {
	$ratingValue = '';
}

if ($ratingAmount < $ratingValue) {
	$ratingValue = $ratingAmount;
}

$ratingAttrs[Helper::getStateAttribute('ratingValue')] = $ratingValue;

$ratingAttrsOutput = '';
if ($ratingAttrs) {
	foreach ($ratingAttrs as $key => $value) {
		$ratingAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = Helper::getBlockAdditionalContentViaFilter('rating', $attributes);

$stars = '';

for ($i = 1; $i < $ratingAmount + 1; $i++) {
	$stars .= '
		<div
			class="' . esc_attr("{$componentClass}__star") . '"
			' . Helper::getStateAttribute('ratingValue') . '="' . $i . '"
		>
		' . $icons['rating'] .
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

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $rating,
			'fieldId' => $ratingName,
			'fieldName' => $ratingName,
			'fieldTypeInternal' => Helper::getStateFieldType('rating'),
			'fieldIsRequired' => $ratingIsRequired,
			'fieldDisabled' => !empty($ratingIsDisabled),
			'fieldTypeCustom' => $ratingTypeCustom ?: 'rating', // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			'fieldTracking' => $ratingTracking,
			'fieldHideLabel' => $ratingHideLabel,
			'fieldConditionalTags' => Components::render(
				'conditional-tags',
				Components::props('conditionalTags', $attributes)
			),
			'fieldAttrs' => $ratingFieldAttrs,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? ''
		]
	)
);
