<?php
/**
 * Template for the Form Block view.
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

use Eightshift_Libs\Helpers\Components;
use Eightshift_Forms\Core\Config;

use Eightshift_Forms\Rest\Dynamics_Crm_Route;

$block_class         = isset( $attributes['blockClass'] ) ? $attributes['blockClass'] : '';
$action              = isset( $attributes['action'] ) ? $attributes['action'] : '';
$method              = isset( $attributes['method'] ) ? $attributes['method'] : '';
$target              = isset( $attributes['target'] ) ? $attributes['target'] : '';
$classes             = isset( $attributes['classes'] ) ? $attributes['classes'] : '';
$id                  = isset( $attributes['id'] ) ? $attributes['id'] : '';
$type                = $attributes['type'] ?? '';
$dynamics_crm_entity = $attributes['dynamicsEntity'] ?? '';

$block_classes = Components::classnames([
  $block_class,
  $classes,
  'js-form',
  "js-form__type--{$type}",
]);
?>


<form
  class="<?php echo esc_attr( $block_classes ); ?>"
  action="<?php echo esc_attr( $action ); ?>"
  method="<?php echo esc_attr( $method ); ?>"
  target="<?php echo esc_attr( $target ); ?>"
  <?php ! empty( $id ) ? printf('id="%s"', esc_attr( $id ) ): '' ?>
  data-form-type="<?php echo esc_attr( $type ); ?>"
>
  <?php echo wp_kses_post( $inner_block_content ); ?>
  
  <?php if ( $type === Config::DYNAMICS_CRM_METHOD ) { ?>
    <input type="hidden" name="<?php echo esc_attr( Dynamics_Crm_Route::ENTITY_PARAM ); ?>" value="<?php echo esc_attr( $dynamics_crm_entity ); ?>" />
  <?php } ?>
</form>
