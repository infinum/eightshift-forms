<?php

/**
 * Template for the Input Block view.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftLibs\Helpers\Components;
use EightshiftForms\Helpers\Forms;
use EightshiftForms\Helpers\Prefill;
use EightshiftForms\Hooks\Filters;

$blockClass = $attributes['blockClass'] ?? '';
$name = $attributes['name'] ?? '';
$value = $attributes['value'] ?? '';
$label = $attributes['label'] ?? '';
$inputId = $attributes['id'] ?? '';
$placeholder = $attributes['placeholder'] ?? '';
$classes = $attributes['classes'] ?? '';
$theme = $attributes['theme'] ?? '';
$inputType = $attributes['type'] ?? '';
$pattern = $attributes['pattern'] ?? '';
$prefillSource = $attributes['prefillDataSource'] ?? '';
$shouldPrefill = isset($attributes['prefillData']) ? filter_var($attributes['prefillData'], FILTER_VALIDATE_BOOLEAN) : false;
$customValidityMsg = $attributes['customValidityMsg'] ?? '';
$isDisabled = isset($attributes['isDisabled']) && $attributes['isDisabled'] ? 'disabled' : '';
$isReadOnly = isset($attributes['isReadOnly']) && $attributes['isReadOnly'] ? 'readonly' : '';
$isRequired = isset($attributes['isRequired']) && $attributes['isRequired'] ? 'required' : '';
$preventSending = isset($attributes['preventSending']) && $attributes['preventSending'] ? 'data-do-not-send' : '';

// Prefill value if needed.
if ($shouldPrefill && ! empty($prefillSource)) {
	$value = Prefill::getPrefillSourceDataSingle($prefillSource, Filters::PREFILL_GENERIC_SINGLE);
} else {
	$value = $attributes['value'] ?? '';
}

// Override form value if it's passed from $_POST.
$value = Forms::maybeOverrideValueFromPost($value, $name);

$blockClasses = Components::classnames([
	$blockClass,
	"js-{$blockClass}",
]);

$wrapperClasses = Components::classnames([
	"{$blockClass}__content-wrap",
	! empty($theme) ? "{$blockClass}__theme--{$theme}" : '',
	"js-{$blockClass}",
]);

$inputClasses = Components::classnames([
	"{$blockClass}__input",
	'js-input',
	$classes,
]);

$labelClasses = Components::classnames([
	"{$blockClass}__label-content",
	$inputType === 'hidden' ? "{$blockClass}__label-content--hidden" : '',
]);

?>

<div class="<?php echo esc_attr($blockClasses); ?>">
	<div class="<?php echo esc_attr($wrapperClasses); ?>">
	<label class="<?php echo esc_attr("{$blockClass}__label js-{$blockClass}-label"); ?>">
		<div class="<?php echo esc_attr($labelClasses); ?>">
		<?php echo wp_kses_post($label); ?>
		</div>
		<input
		name="<?php echo esc_attr($name); ?>"
		placeholder="<?php echo esc_attr($placeholder); ?>"
		<?php ! empty($inputId) ? printf('id="%s"', esc_attr($inputId)) : ''; ?>
		class="<?php echo esc_attr($inputClasses); ?>"
		value="<?php echo esc_attr($value); ?>"
		type="<?php echo esc_attr($inputType); ?>"
		<?php echo esc_attr($isDisabled); ?>
		<?php echo esc_attr($isReadOnly); ?>
		<?php echo esc_attr($isRequired); ?>
		<?php echo esc_attr($preventSending); ?>
		<?php ( ! empty($pattern) ) ? printf('pattern="%s"', esc_attr($pattern)) : ''; ?>
		<?php ( ! empty($customValidityMsg) && ! empty($pattern) ) ? printf('oninput="setCustomValidity(\'\'); checkValidity(); setCustomValidity(validity.valid ? \'\' : \'%s\');"', esc_html($customValidityMsg)) : ''; ?>
		/>
	</label>
	</div>
</div>
