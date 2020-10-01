<?php
/**
 * Template for the Select Block view.
 *
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

use Eightshift_Forms\Core\Filters;
use Eightshift_Libs\Helpers\Components;
use Eightshift_Forms\Helpers\Prefill;

$block_class     = $attributes['blockClass'] ?? '';
$name            = $attributes['name'] ?? '';
$select_id       = $attributes['id'] ?? '';
$classes         = $attributes['classes'] ?? '';
$theme           = $attributes['theme'] ?? '';
$should_prefill  = $attributes['prefillData'] ?? false;
$prefill_source  = $attributes['prefillDataSource'] ?? '';
$hide_loading    = $attributes['hideLoading'] ?? true;
$is_disabled     = isset( $attributes['isDisabled'] ) && $attributes['isDisabled'] ? 'disabled' : '';
$prevent_sending = isset( $attributes['preventSending'] ) && $attributes['preventSending'] ? 'data-do-not-send' : '';

$block_classes = Components::classnames([
  $block_class,
  "js-$block_class",
  ! empty( $theme ) ? "{$block_class}__theme--{$theme}" : '',
  $hide_loading ? "{$block_class}--has-loader is-loading" : '',
]);

$content_wrap_classes = Components::classnames([
  "{$block_class}__content-wrap",
  "js-{$block_class}-content-wrap",
]);

$select_classes = Components::classnames([
  "{$block_class}__select",
  "js-{$block_class}-select",
  $classes,
]);

?>

<div class="<?php echo esc_attr( $block_classes ); ?>">
  <?php
    $this->render_block_view(
      '/components/label/label.php',
      array(
        'blockClass' => $attributes['blockClass'] ?? '',
        'label'      => $attributes['label'] ?? '',
      )
    );
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
