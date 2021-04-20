<?php
/**
 * Template for the BasicCaptcha Block view.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Captcha\BasicCaptcha;

$block_class   = $attributes['blockClass'] ?? '';
$name          = $attributes['name'] ?? BasicCaptcha::RESULT_KEY;
$theme         = $attributes['theme'] ?? '';
$first_number  = wp_rand( 1, 15 );
$second_number = wp_rand( 1, 15 );

$block_classes = Components::classnames([
  $block_class,
  "js-{$block_class}",
  ! empty( $theme ) ? "{$block_class}__theme--{$theme}" : '',
]);

if ( empty( $this ) ) {
  return;
}

?>

<div class="<?php echo esc_attr( $block_classes ); ?>">
  <?php
    $this->render_block_view(
      '/components/label/label.php',
      array(
        'blockClass' => $attributes['blockClass'] ?? '',
        'label'      => $attributes['label'] ?? '',
        'id'         => $attributes['id'] ?? '',
        'theme'      => $attributes['theme'] ?? '',
      )
    );
    ?>
  <div class="<?php echo esc_attr( "{$block_class}__content-wrap {$block_class}__theme--{$theme}" ); ?>">
    <div class="<?php echo esc_attr( "{$block_class}__captcha-number" ); ?>" >
      <?php echo intval( $first_number ); ?>
      <input type="hidden" name="<?php echo esc_attr( BasicCaptcha::FIRST_NUMBER_KEY ); ?>" readonly value="<?php echo intval( $first_number ); ?>" />
    </div>
    <div class="<?php echo esc_attr( "{$block_class}__captcha-plus" ); ?>"> + </div>
    <div class="<?php echo esc_attr( "{$block_class}__captcha-number" ); ?>">
      <?php echo intval( $second_number ); ?>
      <input type="hidden" name="<?php echo esc_attr( BasicCaptcha::SECOND_NUMBER_KEY ); ?>" readonly value="<?php echo intval( $second_number ); ?>" />
    </div>
    <div class="<?php echo esc_attr( "{$block_class}__captcha-equals" ); ?>"> = </div>
    <input
      name="<?php echo esc_attr( $name ); ?>"
      class="<?php echo esc_attr( "{$block_class}__captcha" ); ?>"
      type="text"
      required
      aria-describedby="basic-captcha-description"
    />
  </div>

  <div id="basic-captcha-description"><?php printf( esc_html__( 'Math captcha. Input sum of %1$d and %2$d.', 'eightshift-forms' ), intval( $first_number ), intval( $second_number ) ); ?></div>
</div>
