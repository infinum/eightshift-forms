<?php

/**
 * Template for the Select Option Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Helper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$selectOptionValue = Components::checkAttr('selectOptionValue', $attributes, $manifest);
$selectOptionAsPlaceholder = Components::checkAttr('selectOptionAsPlaceholder', $attributes, $manifest);
$selectOptionLabel = Components::checkAttr('selectOptionLabel', $attributes, $manifest);

if ((!$selectOptionValue || !$selectOptionLabel) && !$selectOptionAsPlaceholder) {
	return;
}

$selectOptionIsSelected = Components::checkAttr('selectOptionIsSelected', $attributes, $manifest);
$selectOptionIsDisabled = Components::checkAttr('selectOptionIsDisabled', $attributes, $manifest);
$selectOptionIsHidden = Components::checkAttr('selectOptionIsHidden', $attributes, $manifest);
$selectOptionAttrs = Components::checkAttr('selectOptionAttrs', $attributes, $manifest);

$conditionalTags = Components::render(
	'conditional-tags',
	Components::props('conditionalTags', $attributes)
);

$customAttributes = [];

$customAttributes[Helper::getStateAttribute('selectOptionIsHidden')] = $selectOptionIsHidden;

if ($conditionalTags) {
	$customAttributes[Helper::getStateAttribute('conditionalTags')] = $conditionalTags;
}

$selectOptionAttrs[Helper::getStateAttribute('selectCustomProperties')] = wp_json_encode($customAttributes);

$selectOptionAttrsOutput = '';
if ($selectOptionAttrs) {
	foreach ($selectOptionAttrs as $key => $value) {
		$selectOptionAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

?>

<option
	value="<?php echo esc_attr($selectOptionValue); ?>"
	<?php selected($selectOptionIsSelected); ?>
	<?php disabled($selectOptionIsDisabled); ?>
	<?php echo $selectOptionAttrsOutput; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
>
	<?php echo esc_attr($selectOptionLabel); ?>
</option>
