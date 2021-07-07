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

$blockClass = $attributes['blockClass'] ?? '';
$theme = $attributes['theme'] ?? '';
$styleClass = $attributes['className'] ?? '';
$name = $attributes['name'] ?? '';
$shouldPrefill = $attributes['prefillData'] ?? false;
$prefillSource = $attributes['prefillDataSource'] ?? '';
$innerBlockContent = ! empty($innerBlockContent) ? $innerBlockContent : '';

$blockClasses = Components::classnames([
	$blockClass,
	$styleClass,
	! empty($theme) ? "{$blockClass}__theme--{$theme}" : '',
]);

if (empty($this)) {
	return;
}

?>

<div class="<?php echo esc_attr($blockClasses); ?>">
	<?php
	echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'label',
		[
			'blockClass' => $attributes['blockClass'] ?? '',
			'label' => $attributes['label'] ?? '',
		]
	);
	?>
	<div class="<?php echo esc_attr("{$blockClass}__content-wrap"); ?>">
	<?php
	if ($shouldPrefill && ! empty($prefillSource)) {
		foreach (Prefill::getPrefillSourceData($prefillSource, Filters::PREFILL_GENERIC_MULTI) as $option) {
			echo wp_kses_post(Components::render('src/blocks/custom/radio-item/radio-item.php', array_merge($option, [
			'blockClass' => 'block-radio-item',
			'name' => $name,
			'theme' => $theme,
			])));
		}
	} else {
		echo wp_kses_post($innerBlockContent);
	}
	?>
	</div>
</div>
