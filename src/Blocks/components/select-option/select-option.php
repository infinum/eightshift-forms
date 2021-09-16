<?php

/**
 * Template for the Select Option Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$selectOptionLabel = Components::checkAttr('selectOptionLabel', $attributes, $manifest);
$selectOptionValue = Components::checkAttr('selectOptionValue', $attributes, $manifest);
$selectOptionIsSelected = Components::checkAttr('selectOptionIsSelected', $attributes, $manifest);
$selectOptionIsDisabled = Components::checkAttr('selectOptionIsDisabled', $attributes, $manifest);

?>

<option
	value="<?php echo esc_attr($selectOptionValue); ?>"
	<?php $selectOptionIsSelected ? 'selected': ''; ?>
	<?php $selectOptionIsDisabled ? 'disabled': ''; ?>
>
	<?php echo esc_attr($selectOptionLabel); ?>
</option>
