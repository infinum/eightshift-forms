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

  /**
   * Build a single fast (key based) array for checking which from type(s) is/are used.
   *
   * @return array
   */
  public static function detect_used_types( bool $is_complex, string $form_type, array $form_types_complex, array $form_types_complex_redirect ): array {
    $used_types = [];

    if ( $is_complex ) {
      foreach ( array_merge( $form_types_complex, $form_types_complex_redirect ) as $complex_form_type ) {
        $used_types[ $complex_form_type ] = 1;
      }
    } else {
      $used_types[ $form_type ] = 1;
    }

    return $used_types;
  }

  /**
   * Recursively changes theme for all inner blocks.
   *
   * @param array  $inner_blocks Array of inner blocks.
   * @param string $theme Theme name.
   * @return array
   */
  public static function recursively_change_theme_for_all_inner_blocks( array $inner_blocks, string $theme ) {
    foreach ( $inner_blocks as $key => $inner_block ) {
      $inner_blocks[ $key ]['attrs']['theme'] = $theme;

      if ( ! empty( $inner_block['innerBlocks'] ) ) {
        $inner_blocks[ $key ]['innerBlocks'] = self::recursively_change_theme_for_all_inner_blocks( $inner_block['innerBlocks'], $theme );
      }
    }

    return $inner_blocks;
  }

  /**
   * Manually sets theme attribute for all form fields. This is done so we can decouple the form's theme (light / dark / etc)
   * from it's contents. I.e. we can have the same form light in 1 section and dark in another.
   *
   * @param  array  $parsed_blocks Array of parsed blocks.
   * @param  string $theme         Theme name.
   * @return array
   */
  public static function add_theme_to_parsed_blocks( array $parsed_blocks, string $theme ): array {

    if ( empty( $theme ) ) {
      return $parsed_blocks;
    }

    if ( ! isset( $parsed_blocks[0]['attrs']['theme'] ) ) {
      return $parsed_blocks;
    }

    // Update form's theme.
    $parsed_blocks[0]['attrs']['theme'] = $theme;

    // Update theme for all inner blocks.
    if ( empty( $parsed_blocks[0]['innerBlocks'] ) ) {
      return $parsed_blocks;
    } else {
      $parsed_blocks[0]['innerBlocks'] = self::recursively_change_theme_for_all_inner_blocks( $parsed_blocks[0]['innerBlocks'], $theme );
    }

    return $parsed_blocks;
  }
}
