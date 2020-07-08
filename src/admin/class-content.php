<?php
/**
 * The Content specific functionality.
 *
 * @since   1.0.0
 * @package Eightshift_Forms\Admin
 */

namespace Eightshift_Forms\Admin;

use Eightshift_Libs\Core\Service;

/**
 * Class Content
 */
class Content implements Service {

  /**
   * Register all the hooks
   *
   * @since 1.0.0
   */
  public function register() {
    add_action( 'wp_kses_allowed_html', [ $this, 'set_custom_wpkses_post_tags' ], 10, 2 );
  }

  /**
   * Add tags to default wp_kses_post
   *
   * @param  array  $tags    Allowed tags array.
   * @param  string $context Context in which the filter is called.
   * @return array           Modified allowed tags array.
   *
   * @since 1.0.0
   */
  public function set_custom_wpkses_post_tags( $tags, $context ) {
    $appended_tags = [
      'form' => [
        'action'      => true,
        'method'      => true,
        'target'      => true,
        'id'          => true,
        'class'       => true,
      ],
      'input' => [
        'name'        => true,
        'value'       => true,
        'type'        => true,
        'id'          => true,
        'class'       => true,
        'disabled'    => true,
        'checked'     => true,
        'readonly'    => true,
        'placeholder' => true,
      ],
      'button' => [
        'name'        => true,
        'value'       => true,
        'type'        => true,
        'id'          => true,
        'class'       => true,
        'disabled'    => true,
      ],
      'select' => [
        'name'        => true,
        'id'          => true,
        'class'       => true,
        'disabled'    => true,
      ],
      'option' => [
        'value'       => true,
        'class'       => true,
        'selected'    => true,
        'disabled'    => true,
      ],
    ];

    $tags = array_merge( $appended_tags, $tags );

    return $tags;
  }
}
