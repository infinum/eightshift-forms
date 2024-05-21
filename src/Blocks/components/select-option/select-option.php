<?php

/**
 * Template for the Select Option Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$selectOptionValue = Helpers::checkAttr('selectOptionValue', $attributes, $manifest);
$selectOptionAsPlaceholder = Helpers::checkAttr('selectOptionAsPlaceholder', $attributes, $manifest);
$selectOptionLabel = Helpers::checkAttr('selectOptionLabel', $attributes, $manifest);

if ((!$selectOptionValue || !$selectOptionLabel) && !$selectOptionAsPlaceholder) {
	return;
}

$selectOptionIsSelected = Helpers::checkAttr('selectOptionIsSelected', $attributes, $manifest);
$selectOptionIsDisabled = Helpers::checkAttr('selectOptionIsDisabled', $attributes, $manifest);
$selectOptionIsHidden = Helpers::checkAttr('selectOptionIsHidden', $attributes, $manifest);
$selectOptionAttrs = Helpers::checkAttr('selectOptionAttrs', $attributes, $manifest);

$conditionalTags = Helpers::render(
	'conditional-tags',
	Helpers::props('conditionalTags', $attributes)
);

$customAttributes = [];

$customAttributes[UtilsHelper::getStateAttribute('selectOptionIsHidden')] = $selectOptionIsHidden;

if ($conditionalTags) {
	$customAttributes[UtilsHelper::getStateAttribute('conditionalTags')] = $conditionalTags;
}

$selectOptionAttrs[UtilsHelper::getStateAttribute('selectCustomProperties')] = wp_json_encode($customAttributes);

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
	<?php echo $selectOptionAttrsOutput; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>
>
	<?php echo esc_attr($selectOptionLabel); ?>
</option>
