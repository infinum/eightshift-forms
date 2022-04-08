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

if (empty($selectOptionValue)) {
	$selectOptionValue = $selectOptionLabel;
}

?>

<option
	value="<?php echo esc_attr($selectOptionValue); ?>"
	<?php selected($selectOptionIsSelected); ?>
	<?php disabled($selectOptionIsDisabled); ?>
>
	<?php echo esc_attr($selectOptionLabel); ?>
</option>
