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

  /**
   * Add extra allowed tags specifically for forms.
   *
   * @param  array $allowed_tags Already allowed tags.
   * @return array
   */
  public static function extra_allowed_tags( $allowed_tags ): array {
    $allowed_tags['form'] = array(
      'class'                    => 1,
      'id'                       => 1,
      'action'                   => 1,
      'method'                   => 1,
      'target'                   => 1,
      'accept-charset'           => 1,
      'autocapitalize'           => 1,
      'autocomplete'             => 1,
      'name'                     => 1,
      'rel'                      => 1,
      'enctype'                  => 1,
      'novalidate'               => 1,
      'data-form-type'           => 1,
      'data-dynamics-crm-entity' => 1,
    );

    // Append additional allowed tags.
    $allowed_tags['input']['required']       = 1;
    $allowed_tags['textarea']['required']    = 1;
    $allowed_tags['select']['required']      = 1;

    return $allowed_tags;
  }

  /**
   * Returns an array of tags for wp_kses(). Less strict than the usual wp_kses_post().
   */
  public static function allowed_tags(): array {
    return self::extra_allowed_tags( wp_kses_allowed_html( 'post' ) );
  }
}
