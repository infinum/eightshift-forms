<?php

/**
 * Template for the Radio Block view.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Prefill;
use EightshiftForms\Hooks\Filters;

$block_class         = $attributes['blockClass'] ?? '';
$theme               = $attributes['theme'] ?? '';
$style_class         = $attributes['className'] ?? '';
$name                = $attributes['name'] ?? '';
$should_prefill      = $attributes['prefillData'] ?? false;
$prefill_source      = $attributes['prefillDataSource'] ?? '';
$inner_block_content = ! empty($inner_block_content) ? $inner_block_content : '';

$block_classes = Components::classnames([
  $block_class,
  $style_class,
  ! empty($theme) ? "{$block_class}__theme--{$theme}" : '',
]);

if (empty($this)) {
	return;
}

?>

<div class="<?php echo esc_attr($block_classes); ?>">
  <?php
	echo Components::render('label', [
	  'blockClass' => $attributes['blockClass'] ?? '',
	  'label'      => $attributes['label'] ?? '',
	]);
	?>
  <div class="<?php echo esc_attr("{$block_class}__content-wrap"); ?>">
	<?php
	if ($should_prefill && ! empty($prefill_source)) {
		foreach (Prefill::get_prefill_source_data($prefill_source, Filters::PREFILL_GENERIC_MULTI) as $option) {
			echo wp_kses_post(Components::render('src/blocks/custom/radio-item/radio-item.php', array_merge($option, [
			'blockClass' => 'block-radio-item',
			'name' => $name,
			'theme' => $theme,
			])));
		}
	} else {
		echo wp_kses_post($inner_block_content);
	}
	?>
  </div>
</div>
