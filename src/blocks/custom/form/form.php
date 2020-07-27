<?php
/**
 * Template for the Form Block view.
 *
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

use Eightshift_Libs\Helpers\Components;

use Eightshift_Forms\Core\Config;
use Eightshift_Forms\Rest\Dynamics_Crm_Route;

$block_class         = $attributes['blockClass'] ?? '';
$form_action         = $attributes['action'] ?? '';
$form_method         = $attributes['method'] ?? '';
$form_target         = $attributes['target'] ?? '';
$form_classes        = $attributes['classes'] ?? '';
$form_id             = $attributes['id'] ?? 'form_' . crc32(microtime(true));
$form_type           = $attributes['type'] ?? '';
$dynamics_crm_entity = $attributes['dynamicsEntity'] ?? '';

$block_classes = Components::classnames(
  array(
    $block_class,
    $form_classes,
    'js-form',
    "js-form__type--{$form_type}",
  )
);
?>


<form
  class="<?php echo esc_attr( $block_classes ); ?>"
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

<?php echo wp_kses_post( Components::render( 'spinner', [ 'formId' => $id ], Config::get_project_path() ) ); ?>

