<?php
/**
 * The Content specific functionality.
 *
 * @package EightshiftForms\Admin
 */

namespace EightshiftForms\Admin;

use Eightshift_Libs\Core\Service;

/**
 * Class Content
 */
class Content implements Service {

  /**
   * Register all the hooks
   */
  public function register() {
    add_action( 'wp_kses_allowed_html', array( $this, 'set_custom_wpkses_post_tags' ), 10, 2 );
  }

  /**
   * Add tags to default wp_kses_post
   *
   * @param  array  $tags    Allowed tags array.
   * @param  string $context Context in which the filter is called.
   * @return array           Modified allowed tags array.
   */
  public function set_custom_wpkses_post_tags( $tags, $context ) {
    $appended_tags = array(
      'form' => array(
        'action'      => true,
        'method'      => true,
        'target'      => true,
        'id'          => true,
        'class'       => true,
      ),
      'input' => array(
        'name'        => true,
        'value'       => true,
        'type'        => true,
        'id'          => true,
        'class'       => true,
        'disabled'    => true,
        'checked'     => true,
        'readonly'    => true,
        'placeholder' => true,
      ),
      'button' => array(
        'name'        => true,
        'value'       => true,
        'type'        => true,
        'id'          => true,
        'class'       => true,
        'disabled'    => true,
      ),
      'select' => array(
        'name'        => true,
        'id'          => true,
        'class'       => true,
        'disabled'    => true,
      ),
      'option' => array(
        'value'       => true,
        'class'       => true,
        'selected'    => true,
        'disabled'    => true,
      ),
    );

    $tags = array_merge( $appended_tags, $tags );

    return $tags;
  }
}
