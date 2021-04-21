<?php

/**
 * Template for the Label Component.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftLibs\Helpers\Components;

$blockClass = $attributes['blockClass'] ?? '';
$label = $attributes['label'] ?? '';
$labelId = $attributes['id'] ?? '';
$theme = $attributes['theme'] ?? '';

$componentClass = 'label';

$componentClasses = Components::classnames([
	"{$componentClass}__label-wrap",
	"{$blockClass}__label-wrap",
	! empty($theme) ? "{$componentClass}__theme--{$theme}" : '',
]);

?>

<div class="<?php echo esc_attr($componentClasses); ?>">
	<label for="<?php echo esc_attr($labelId); ?>" class="<?php echo esc_attr("{$componentClass} {$blockClass}__label"); ?>">
	<?php echo esc_html($label); ?>
	</label>
</div>
