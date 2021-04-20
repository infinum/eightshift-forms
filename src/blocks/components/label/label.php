<?php

/**
 * Template for the Label Component.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftLibs\Helpers\Components;

$block_class = $attributes['blockClass'] ?? '';
$label       = $attributes['label'] ?? '';
$label_id    = $attributes['id'] ?? '';
$theme       = $attributes['theme'] ?? '';

$component_class = 'label';

$component_classes = Components::classnames([
  "{$component_class}__label-wrap",
  "{$block_class}__label-wrap",
  ! empty($theme) ? "{$component_class}__theme--{$theme}" : '',
]);

?>

<div class="<?php echo esc_attr($component_classes); ?>">
  <label for="<?php echo esc_attr($label_id); ?>" class="<?php echo esc_attr("{$component_class} {$block_class}__label"); ?>">
	<?php echo esc_html($label); ?>
  </label>
</div>
