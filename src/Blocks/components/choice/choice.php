<?php

/**
 * Template for the Choice Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$choiceLabel = Components::checkAttr('choiceLabel', $attributes, $manifest);
$choiceName = Components::checkAttr('choiceName', $attributes, $manifest);
$choiceId = Components::checkAttr('choiceId', $attributes, $manifest);
$choiceValue = Components::checkAttr('choiceValue', $attributes, $manifest);
$choiceType = Components::checkAttr('choiceType', $attributes, $manifest);
$choiceIsChecked = Components::checkAttr('choiceIsChecked', $attributes, $manifest);
$choiceIsDisabled = Components::checkAttr('choiceIsDisabled', $attributes, $manifest);
$choiceIsReadOnly = Components::checkAttr('choiceIsReadOnly', $attributes, $manifest);
$choiceIsRequired = Components::checkAttr('choiceIsRequired', $attributes, $manifest);
$choiceTracking = Components::checkAttr('choiceTracking', $attributes, $manifest);

if (empty($choiceId)) {
	$choiceId = $choiceName;
}

$choiceClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
]);

?>

<div class="<?php echo esc_attr($choiceClass); ?>">
	<label
		for="<?php echo esc_attr($choiceName); ?>"
		class="<?php echo esc_attr("{$componentClass}__label"); ?>"
	>
		<?php echo esc_attr($choiceLabel); ?>
	</label>
	<input
		class="<?php echo esc_attr("{$componentClass}__input"); ?>"
		type="<?php echo esc_attr($choiceType); ?>"
		name="<?php echo esc_attr($choiceName); ?>"
		id="<?php echo esc_attr($choiceId); ?>"
		value="<?php echo esc_attr($choiceValue); ?>"
		data-validation-required="<?php echo esc_attr($choiceIsRequired); ?>"
		data-tracking="<?php echo esc_attr($choiceTracking); ?>"
		<?php echo $choiceIsChecked ? 'checked': ''; ?>
		<?php echo $choiceIsDisabled ? 'disabled': ''; ?>
		<?php echo $choiceIsReadOnly ? 'readonly': ''; ?>
	/>
</div>
