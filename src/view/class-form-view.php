<?php
/**
 * Define rule for a form view.
 *
 * @package Eightshift_Forms\View
 */

declare( strict_types=1 );

namespace Eightshift_Forms\View;

/**
 * Define rule for a form view.
 */
class Form_View {

  public static function extra_allowed_tags(): array {
    $allowed_tags['form'] = [
      'class'          => 1,
      'id'             => 1,
      'action'         => 1,
      'method'         => 1,
      'target'         => 1,
      'accept-charset' => 1,
      'autocapitalize' => 1,
      'autocomplete'   => 1,
      'name' => 1,
      'rel' => 1,
      'enctype' => 1,
      'novalidate' => 1,
    ];

    return $allowed_tags;
  }

  /**
   * Returns an array of tags for wp_kses(). Less strict than the usual wp_kses_post().
   */
  public static function allowed_tags(): array {
    return array_merge( wp_kses_allowed_html( 'post' ), self::extra_allowed_tags() );
  }
}
