<?php

/**
 * Template for the Choice Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$choiceLabel = Components::checkAttr('choiceLabel', $attributes, $manifest);
$choiceName = Components::checkAttr('choiceName', $attributes, $manifest);
$choiceId = Components::checkAttr('choiceId', $attributes, $manifest);
$choiceType = Components::checkAttr('choiceType', $attributes, $manifest);
$choiceIsChecked = Components::checkAttr('choiceIsChecked', $attributes, $manifest);
$choiceIsDisabled = Components::checkAttr('choiceIsDisabled', $attributes, $manifest);
$choiceIsReadOnly = Components::checkAttr('choiceIsReadOnly', $attributes, $manifest);
$choiceIsRequired = Components::checkAttr('choiceIsRequired', $attributes, $manifest);
$choiceTracking = Components::checkAttr('choiceTracking', $attributes, $manifest);

if (empty($choiceId)) {
	$choiceId = $choiceName;
}

?>

<label for="<?php echo esc_attr($choiceName); ?>">
	<?php echo esc_attr($choiceLabel); ?>
</label>
<input
	type="<?php echo esc_attr($choiceType); ?>"
	name="<?php echo esc_attr($choiceName); ?>"
	id="<?php echo esc_attr($choiceId); ?>"
	data-validation-required="<?php echo esc_attr($choiceIsRequired); ?>"
	data-tracking="<?php echo esc_attr($choiceTracking); ?>"
	<?php echo $choiceIsChecked ? 'checked': ''; ?>
	<?php echo $choiceIsDisabled ? 'disabled': ''; ?>
	<?php echo $choiceIsReadOnly ? 'readonly': ''; ?>
/>
