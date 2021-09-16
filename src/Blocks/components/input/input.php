<?php

/**
 * Template for the Input Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$inputName = Components::checkAttr('inputName', $attributes, $manifest);
$inputValue = Components::checkAttr('inputValue', $attributes, $manifest);
$inputId = Components::checkAttr('inputId', $attributes, $manifest);
$inputPlaceholder = Components::checkAttr('inputPlaceholder', $attributes, $manifest);
$inputType = Components::checkAttr('inputType', $attributes, $manifest);
$inputIsDisabled = Components::checkAttr('inputIsDisabled', $attributes, $manifest);
$inputIsReadOnly = Components::checkAttr('inputIsReadOnly', $attributes, $manifest);
$inputIsRequired = Components::checkAttr('inputIsRequired', $attributes, $manifest);

$inputClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
]);

?>

<input
	class="<?php echo esc_attr($inputClass); ?>"
	name="<?php echo esc_attr($inputName); ?>"
	value="<?php echo esc_attr($inputValue); ?>"
	id="<?php echo esc_attr($inputId); ?>"
	placeholder="<?php echo esc_attr($inputPlaceholder) ?>"
	type="<?php echo esc_attr($inputType) ?>"
	<?php $inputIsDisabled ? 'disabled': ''; ?>
	<?php $inputIsReadOnly ? 'readonly': ''; ?>
	<?php $inputIsRequired ? 'required': ''; ?>
/>
