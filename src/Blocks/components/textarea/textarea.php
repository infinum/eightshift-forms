<?php

/**
 * Template for the Textarea Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$textareaName = Components::checkAttr('textareaName', $attributes, $manifest);
$textareaValue = Components::checkAttr('textareaValue', $attributes, $manifest);
$textareaId = Components::checkAttr('textareaId', $attributes, $manifest);
$textareaPlaceholder = Components::checkAttr('textareaPlaceholder', $attributes, $manifest);
$textareaIsDisabled = Components::checkAttr('textareaIsDisabled', $attributes, $manifest);
$textareaIsReadOnly = Components::checkAttr('textareaIsReadOnly', $attributes, $manifest);
$textareaIsRequired = Components::checkAttr('textareaIsRequired', $attributes, $manifest);

$textareaClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
]);

?>

<textarea
	class="<?php echo esc_attr($textareaClass); ?>"
	name="<?php echo esc_attr($textareaName); ?>"
	id="<?php echo esc_attr($textareaId); ?>"
	placeholder="<?php echo esc_attr($textareaPlaceholder) ?>"
	<?php $textareaIsDisabled ? 'disabled': ''; ?>
	<?php $textareaIsReadOnly ? 'readonly': ''; ?>
	<?php $textareaIsRequired ? 'required': ''; ?>
>
	<?php echo esc_attr($textareaValue); ?>
</textarea>
