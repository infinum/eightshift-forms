<?php

/**
 * Template for the Select Option Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$selectOptionLabel = Components::checkAttr('selectOptionLabel', $attributes, $manifest);
$selectOptionValue = Components::checkAttr('selectOptionValue', $attributes, $manifest);
$selectOptionIsSelected = Components::checkAttr('selectOptionIsSelected', $attributes, $manifest);
$selectOptionIsDisabled = Components::checkAttr('selectOptionIsDisabled', $attributes, $manifest);
$selectOptionAsPlaceholder = Components::checkAttr('selectOptionAsPlaceholder', $attributes, $manifest);
$selectOptionAttrs = Components::checkAttr('selectOptionAttrs', $attributes, $manifest);

if (empty($selectOptionValue) && !$selectOptionAsPlaceholder) {
	$selectOptionValue = $selectOptionLabel;
}

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
