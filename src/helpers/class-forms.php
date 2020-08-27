<?php
/**
 * Helpers for forms
 *
 * @package Eightshift_Forms\Helpers
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Helpers;

/**
 * Helpers for forms
 */
class Forms {

  /**
   * Overrides the value from $_GET if it's set.
   *
   * @param  string $value Input field's value.
   * @param  string $name  Field's key / name.
   * @return string
   */
  public static function maybe_override_value_from_query_string( string $value, string $name ) {
    if ( isset( $_GET[ "field-$name" ] ) ) {
      $value = \sanitize_text_field( \wp_unslash( $_GET[ "field-$name" ] ) );
    }

    return $value;
  }
}
