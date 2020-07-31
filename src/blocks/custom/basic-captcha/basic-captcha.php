<?php
/**
 * Template for the BasicCaptcha Block view.
 *
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

use Eightshift_Libs\Helpers\Components;

$block_class   = $attributes['blockClass'] ?? '';
$name          = $attributes['name'] ?? 'basicCaptcha';
$theme         = $attributes['theme'] ?? '';
$first_number  = rand(1, 15);
$second_number = rand(1, 15);

$block_classes = Components::classnames([
  $block_class,
  "js-{$block_class}"
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
  <div class="<?php echo esc_attr( "{$block_class}__content-wrap {$block_class}__theme--{$theme}" ); ?>">
    <div class="<?php echo esc_attr( "{$block_class}__captcha-number" ); ?>" >
      <?php echo esc_html( $first_number ); ?>
      <input type="hidden" tabindex="-1" readonly value="<?php echo esc_html( $first_number ); ?>" />
    </div>
    <div class="<?php echo esc_attr( "{$block_class}__captcha-plus" ); ?>"> + </div>
    <div class="<?php echo esc_attr( "{$block_class}__captcha-number" ); ?>">
      <?php echo esc_html( $second_number ); ?>
      <input type="hidden" value="<?php echo esc_html( $second_number ); ?>" />
    </div>
    <input
      name="<?php echo esc_attr( $name ); ?>"
      class="<?php echo esc_attr( "{$block_class}__captcha" ); ?>"
      type="text"
      required
    />
  </div>
</div>
