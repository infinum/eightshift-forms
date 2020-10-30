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
      'class'                            => 1,
      'id'                               => 1,
      'action'                           => 1,
      'method'                           => 1,
      'target'                           => 1,
      'accept-charset'                   => 1,
      'autocapitalize'                   => 1,
      'autocomplete'                     => 1,
      'name'                             => 1,
      'rel'                              => 1,
      'enctype'                          => 1,
      'novalidate'                       => 1,
      'data-is-form-complex'             => 1,
      'data-form-type'                   => 1,
      'data-form-types-complex'          => 1,
      'data-form-types-complex-redirect' => 1,
      'data-dynamics-crm-entity'         => 1,
      'data-buckaroo-service'            => 1,
    );

    // Append additional allowed tags.
    $allowed_tags['input']['required']            = 1;
    $allowed_tags['input']['checked']             = 1;
    $allowed_tags['input']['tabindex']            = 1;
    $allowed_tags['input']['pattern']             = 1;
    $allowed_tags['input']['data-opens-popup']    = 1;
    $allowed_tags['input']['data-do-not-send']    = 1;
    $allowed_tags['input']['oninput']             = 1;
    $allowed_tags['input']['min']                 = 1;
    $allowed_tags['input']['max']                 = 1;
    $allowed_tags['input']['maxlength']           = 1;
    $allowed_tags['input']['aria-labelledby']     = 1;
    $allowed_tags['input']['aria-describedby']    = 1;
    $allowed_tags['textarea']['required']         = 1;
    $allowed_tags['textarea']['data-do-not-send'] = 1;
    $allowed_tags['textarea']['aria-labelledby']  = 1;
    $allowed_tags['textarea']['aria-describedby'] = 1;
    $allowed_tags['select']['required']           = 1;
    $allowed_tags['select']['data-do-not-send']   = 1;
    $allowed_tags['select']['aria-describedby']   = 1;
    $allowed_tags['select']['aria-labelledby']    = 1;
    $allowed_tags['button']['aria-label']         = 1;
    $allowed_tags['button']['role']               = 1;
    $allowed_tags['button']['aria-describedby']   = 1;
    $allowed_tags['button']['aria-labelledby']    = 1;
    $allowed_tags['radio']['aria-describedby']    = 1;
    $allowed_tags['radio']['aria-labelledby']     = 1;

    return $allowed_tags;
  }

  /**
   * Returns an array of tags for wp_kses(). Less strict than the usual wp_kses_post().
   */
  public static function allowed_tags(): array {
    return self::extra_allowed_tags( wp_kses_allowed_html( 'post' ) );
  }
}
