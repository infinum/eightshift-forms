<?php
/**
 * Template for the Select Block view.
 *
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

use EightshiftForms\Hooks\Filters;
use Eightshift_Forms\Helpers\Components;
use Eightshift_Forms\Helpers\Prefill;

$block_class         = $attributes['blockClass'] ?? '';
$inner_block_content = $attributes['innerBlockContent'] ?? '';
$name                = $attributes['name'] ?? '';
$select_id           = $attributes['id'] ?? '';
$classes             = $attributes['classes'] ?? '';
$theme               = $attributes['theme'] ?? '';
$prefill_source      = $attributes['prefillDataSource'] ?? '';
$should_prefill      = isset( $attributes['prefillData'] ) ? filter_var( $attributes['prefillData'], FILTER_VALIDATE_BOOLEAN ) : false;
$hide_loading        = isset( $attributes['hideLoading'] ) ? filter_var( $attributes['hideLoading'], FILTER_VALIDATE_BOOLEAN ) : true;
$is_disabled         = isset( $attributes['isDisabled'] ) && $attributes['isDisabled'] ? 'disabled' : '';
$prevent_sending     = isset( $attributes['preventSending'] ) && $attributes['preventSending'] ? 'data-do-not-send' : '';

$component_class = 'select';

$component_classes = Components::classnames([
  $component_class,
  "js-{$component_class}",
  ! empty( $theme ) ? "{$component_class}__theme--{$theme}" : '',
  $hide_loading ? "{$component_class}--has-loader is-loading" : '',
  ! empty( $is_disabled ) ? "{$component_class}--is-disabled" : '',
  "{$block_class}__{$component_class}",
]);

$content_wrap_classes = Components::classnames([
  "{$component_class}__content-wrap",
  "js-{$component_class}-content-wrap",
]);

$select_classes = Components::classnames([
  "{$component_class}__select",
  "js-{$component_class}-select",
  $classes,
]);

?>

<div class="<?php echo esc_attr( $component_classes ); ?>">
  <?php
    echo wp_kses_post( Components::render( 'label', [
      'blockClass' => $attributes['blockClass'] ?? '',
      'label'      => $attributes['label'] ?? '',
    ]));
    ?>
  <div class="<?php echo esc_attr( $content_wrap_classes ); ?>">
    <select
      <?php ! empty( $select_id ) ? printf( 'id="%s"', esc_attr( $select_id ) ) : ''; ?>
      name="<?php echo esc_attr( $name ); ?>"
      class="<?php echo esc_attr( $select_classes ); ?>"
      <?php echo esc_attr( $is_disabled ); ?>
      <?php echo esc_attr( $prevent_sending ); ?>
    >
      <?php
      if ( $should_prefill && ! empty( $prefill_source ) ) {
        foreach ( Prefill::get_prefill_source_data( $prefill_source, Filters::PREFILL_GENERIC_MULTI ) as $option ) {
          printf( '<option value="%s">%s</option>', esc_attr( $option['value'] ), esc_html( $option['label'] ) );
        }
      } else {
        echo wp_kses_post( $inner_block_content );
      }
      ?>
    </select>
  </div>
</div>
