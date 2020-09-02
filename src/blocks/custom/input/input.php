<?php
/**
 * Template for the Input Block view.
 *
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

use Eightshift_Libs\Helpers\Components;
use Eightshift_Forms\Helpers\Forms;

$block_class         = $attributes['blockClass'] ?? '';
$name                = $attributes['name'] ?? '';
$value               = $attributes['value'] ?? '';
$input_id            = $attributes['id'] ?? '';
$placeholder         = $attributes['placeholder'] ?? '';
$classes             = $attributes['classes'] ?? '';
$theme               = $attributes['theme'] ?? '';
$input_type          = $attributes['type'] ?? '';
$pattern             = $attributes['pattern'] ?? '';
$custom_validity_msg = $attributes['customValidityMsg'] ?? '';
$is_disabled         = isset( $attributes['isDisabled'] ) && $attributes['isDisabled'] ? 'disabled' : '';
$is_read_only        = isset( $attributes['isReadOnly'] ) && $attributes['isReadOnly'] ? 'readonly' : '';
$is_required         = isset( $attributes['isRequired'] ) && $attributes['isRequired'] ? 'required' : '';
$prevent_sending     = isset( $attributes['preventSending'] ) && $attributes['preventSending'] ? 'data-do-not-send' : '';

// Override form value if it's passed from $_GET.
$value = Forms::maybe_override_value_from_query_string( $value, $name );

$block_classes = Components::classnames([
  $block_class,
  "js-{$block_class}"
]);

$wrapper_classes = Components::classnames([
  "{$block_class}__content-wrap",
  "{$block_class}__theme--{$theme}",
  "js-{$block_class}"
]);

$input_classes = Components::classnames([
  "{$block_class}__input",
  "js-input",
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
        'id'         => $attributes['id'] ?? '',
      )
    );
    ?>
  <div class="<?php echo esc_attr( $wrapper_classes ); ?>">
    <input
      name="<?php echo esc_attr( $name ); ?>"
      placeholder="<?php echo esc_attr( $placeholder ); ?>"
      <?php ! empty( $input_id ) ? printf( 'id="%s"', esc_attr( $input_id ) ) : ''; ?>
      class="<?php echo esc_attr( $input_classes ); ?>"
      value="<?php echo esc_attr( $value ); ?>"
      type="<?php echo esc_attr( $input_type ); ?>"
      <?php echo esc_attr( $is_disabled ); ?>
      <?php echo esc_attr( $is_read_only ); ?>
      <?php echo esc_attr( $is_required ); ?>
      <?php echo esc_attr( $prevent_sending ); ?>
      <?php ( ! empty( $pattern ) ) ? printf( 'pattern="%s"', esc_attr( $pattern ) ) : ''; ?>
      <?php ( ! empty( $custom_validity_msg ) && ! empty( $pattern ) ) ? printf( 'data-attr-custom-validity="%s"', esc_attr( $custom_validity_msg ) ) : ''; ?>
    />
  </div>
</div>
