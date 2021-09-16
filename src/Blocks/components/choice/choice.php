<?php

/**
 * Template for the Choice Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$choiceLabel = Components::checkAttr('choiceLabel', $attributes, $manifest);
$choiceValue = Components::checkAttr('choiceValue', $attributes, $manifest);
$choiceName = Components::checkAttr('choiceName', $attributes, $manifest);
$choiceId = Components::checkAttr('choiceId', $attributes, $manifest);
$choiceType = Components::checkAttr('choiceType', $attributes, $manifest);
$choiceIsChecked = Components::checkAttr('choiceIsChecked', $attributes, $manifest);
$choiceIsDisabled = Components::checkAttr('choiceIsDisabled', $attributes, $manifest);
$choiceIsReadOnly = Components::checkAttr('choiceIsReadOnly', $attributes, $manifest);

?>

<label for="<?php echo esc_attr($choiceName); ?>">
	<?php echo esc_attr($choiceLabel); ?>
</label>
<input
	type="<?php echo esc_attr($choiceType); ?>"
	value="<?php echo esc_attr($choiceValue); ?>"
	name="<?php echo esc_attr($choiceName); ?>"
	id="<?php echo esc_attr($choiceId); ?>"
	<?php $choiceIsChecked ? 'checked': ''; ?>
	<?php $choiceIsDisabled ? 'disabled': ''; ?>
	<?php $choiceIsReadOnly ? 'readonly': ''; ?>
/>
