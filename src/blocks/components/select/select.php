<?php

/**
 * Template for the Select Block view.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Prefill;

$blockClass = $attributes['blockClass'] ?? '';
$innerBlockContent = $attributes['innerBlockContent'] ?? '';
$name = $attributes['name'] ?? '';
$selectId = $attributes['id'] ?? '';
$classes = $attributes['classes'] ?? '';
$theme = $attributes['theme'] ?? '';
$prefillSource = $attributes['prefillDataSource'] ?? '';
$shouldPrefill = isset($attributes['prefillData']) ? filter_var($attributes['prefillData'], FILTER_VALIDATE_BOOLEAN) : false;
$hideLoading = isset($attributes['hideLoading']) ? filter_var($attributes['hideLoading'], FILTER_VALIDATE_BOOLEAN) : true;
$isDisabled = isset($attributes['isDisabled']) && $attributes['isDisabled'] ? 'disabled' : '';
$preventSending = isset($attributes['preventSending']) && $attributes['preventSending'] ? 'data-do-not-send' : '';

$componentClass = 'select';

$componentClasses = Components::classnames([
	$componentClass,
	"js-{$componentClass}",
	! empty($theme) ? "{$componentClass}__theme--{$theme}" : '',
	$hideLoading ? "{$componentClass}--has-loader is-loading" : '',
	! empty($isDisabled) ? "{$componentClass}--is-disabled" : '',
	"{$blockClass}__{$componentClass}",
]);

$contentWrapClasses = Components::classnames([
	"{$componentClass}__content-wrap",
	"js-{$componentClass}-content-wrap",
]);

$selectClasses = Components::classnames([
	"{$componentClass}__select",
	"js-{$componentClass}-select",
	$classes,
]);

?>

<div class="<?php echo esc_attr($componentClasses); ?>">
	<?php
	echo wp_kses_post(Components::render('label', [
		'blockClass' => $attributes['blockClass'] ?? '',
		'label' => $attributes['label'] ?? '',
	]));
	?>
	<div class="<?php echo esc_attr($contentWrapClasses); ?>">
	<select
		<?php ! empty($selectId) ? printf('id="%s"', esc_attr($selectId)) : ''; ?>
		name="<?php echo esc_attr($name); ?>"
		class="<?php echo esc_attr($selectClasses); ?>"
		<?php echo esc_attr($isDisabled); ?>
		<?php echo esc_attr($preventSending); ?>
	>
		<?php
		if ($shouldPrefill && ! empty($prefillSource)) {
			foreach (Prefill::get_prefill_source_data($prefillSource, Filters::PREFILL_GENERIC_MULTI) as $option) {
				printf('<option value="%s">%s</option>', esc_attr($option['value']), esc_html($option['label']));
			}
		} else {
			echo wp_kses_post($innerBlockContent);
		}
		?>
	</select>
	</div>
</div>
