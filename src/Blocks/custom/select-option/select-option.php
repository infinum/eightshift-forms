<?php

/**
 * Template for the Select Option Block view.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

$blockClass = $attributes['blockClass'] ?? '';
$label = $attributes['label'] ?? '';
$value = $attributes['value'] ?? '';
$isDisabled = isset($attributes['isDisabled']) && $attributes['isDisabled'] ? 'disabled' : '';
$isSelected = isset($attributes['isSelected']) && $attributes['isSelected'] ? 'selected' : '';

?>

<option
	class="<?php echo esc_attr("{$blockClass}__option"); ?>"
	value="<?php echo esc_attr($value); ?>"
	<?php echo esc_attr($isDisabled); ?>
	<?php echo esc_attr($isSelected); ?>
>
	<?php echo esc_html($label); ?>
</option>
