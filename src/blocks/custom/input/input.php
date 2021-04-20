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

$block_class         = $attributes['blockClass'] ?? '';
$name                = $attributes['name'] ?? '';
$value               = $attributes['value'] ?? '';
$label               = $attributes['label'] ?? '';
$input_id            = $attributes['id'] ?? '';
$placeholder         = $attributes['placeholder'] ?? '';
$classes             = $attributes['classes'] ?? '';
$theme               = $attributes['theme'] ?? '';
$input_type          = $attributes['type'] ?? '';
$pattern             = $attributes['pattern'] ?? '';
$prefill_source      = $attributes['prefillDataSource'] ?? '';
$should_prefill      = isset($attributes['prefillData']) ? filter_var($attributes['prefillData'], FILTER_VALIDATE_BOOLEAN) : false;
$custom_validity_msg = $attributes['customValidityMsg'] ?? '';
$is_disabled         = isset($attributes['isDisabled']) && $attributes['isDisabled'] ? 'disabled' : '';
$is_read_only        = isset($attributes['isReadOnly']) && $attributes['isReadOnly'] ? 'readonly' : '';
$is_required         = isset($attributes['isRequired']) && $attributes['isRequired'] ? 'required' : '';
$prevent_sending     = isset($attributes['preventSending']) && $attributes['preventSending'] ? 'data-do-not-send' : '';

// Prefill value if needed.
if ($should_prefill && ! empty($prefill_source)) {
	$value = Prefill::get_prefill_source_data_single($prefill_source, Filters::PREFILL_GENERIC_SINGLE);
} else {
	$value = $attributes['value'] ?? '';
}

// Override form value if it's passed from $_GET.
$value = Forms::maybe_override_value_from_query_string($value, $name);

$block_classes = Components::classnames([
  $block_class,
  "js-{$block_class}",
]);

$wrapper_classes = Components::classnames([
  "{$block_class}__content-wrap",
  ! empty($theme) ? "{$block_class}__theme--{$theme}" : '',
  "js-{$block_class}",
]);

$input_classes = Components::classnames([
  "{$block_class}__input",
  'js-input',
  $classes,
]);

$label_classes = Components::classnames([
  "{$block_class}__label-content",
  $input_type === 'hidden' ? "{$block_class}__label-content--hidden" : '',
]);

?>

<div class="<?php echo esc_attr($block_classes); ?>">
  <div class="<?php echo esc_attr($wrapper_classes); ?>">
	<label class="<?php echo esc_attr("{$block_class}__label js-{$block_class}-label"); ?>">
	  <div class="<?php echo esc_attr($label_classes); ?>">
		<?php echo wp_kses_post($label); ?>
	  </div>
	  <input
		name="<?php echo esc_attr($name); ?>"
		placeholder="<?php echo esc_attr($placeholder); ?>"
		<?php ! empty($input_id) ? printf('id="%s"', esc_attr($input_id)) : ''; ?>
		class="<?php echo esc_attr($input_classes); ?>"
		value="<?php echo esc_attr($value); ?>"
		type="<?php echo esc_attr($input_type); ?>"
		<?php echo esc_attr($is_disabled); ?>
		<?php echo esc_attr($is_read_only); ?>
		<?php echo esc_attr($is_required); ?>
		<?php echo esc_attr($prevent_sending); ?>
		<?php ( ! empty($pattern) ) ? printf('pattern="%s"', esc_attr($pattern)) : ''; ?>
		<?php ( ! empty($custom_validity_msg) && ! empty($pattern) ) ? printf('oninput="setCustomValidity(\'\'); checkValidity(); setCustomValidity(validity.valid ? \'\' : \'%s\');"', esc_html($custom_validity_msg)) : ''; ?>
	  />
	</label>
  </div>
</div>
