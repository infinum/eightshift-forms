<?php
/**
 * Template for the Form Block view.
 *
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

use Eightshift_Forms\Helpers\Components;

use Eightshift_Forms\Core\Config;
use Eightshift_Forms\Rest\Dynamics_Crm_Route;

$block_class         = $attributes['blockClass'] ?? '';
$form_action         = $attributes['action'] ?? '';
$form_method         = $attributes['method'] ?? '';
$form_target         = $attributes['target'] ?? '';
$form_classes        = $attributes['classes'] ?? '';
$form_id             = $attributes['id'] ?? '';
$form_type           = $attributes['type'] ?? '';
$form_theme          = $attributes['theme'] ?? '';
$success_message     = $attributes['successMessage'] ?? '';
$error_message       = $attributes['errorMessage'] ?? '';
$dynamics_crm_entity = $attributes['dynamicsEntity'] ?? '';

$block_classes = Components::classnames([
  $block_class,
  $form_classes,
  'js-form',
  "js-form__type--{$form_type}",
  ! empty( $form_theme ) ? "{$block_class}__theme--{$form_theme}" : ''
]);

?>

<div class="<?php echo esc_attr( $block_classes ); ?>">
  <form
    class="<?php echo esc_attr( "{$block_class}__form js-{$block_class}-form" ); ?>"
    action="<?php echo esc_attr( $form_action ); ?>"
    method="<?php echo esc_attr( $form_method ); ?>"
    target="<?php echo esc_attr( $form_target ); ?>"
    <?php ! empty( $form_id ) ? printf( 'id="%s"', esc_attr( $form_id ) ) : ''; ?>
    data-form-type="<?php echo esc_attr( $form_type ); ?>"
  >
    <?php echo wp_kses_post( $inner_block_content ); ?>
    
    <?php if ( $form_type === Config::DYNAMICS_CRM_METHOD ) { ?>
      <input type="hidden" name="<?php echo esc_attr( Dynamics_Crm_Route::ENTITY_PARAM ); ?>" value="<?php echo esc_attr( $dynamics_crm_entity ); ?>" />
    <?php } ?>
  
  </form>

  <?php echo wp_kses_post( Components::render( 'form-overlay' ) ); ?>
  <?php echo wp_kses_post( Components::render( 'spinner' ) ); ?>
  <?php echo wp_kses_post( Components::render( 'form-message', [ 'message' => $success_message, 'type' => 'success' ] ) ); ?>
  <?php echo wp_kses_post( Components::render( 'form-message', [ 'message' => $error_message, 'type' => 'error' ] ) ); ?>
</div>

