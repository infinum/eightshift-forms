<?php namespace EightshiftFormsTests;

use \Brain\Monkey\Functions;

class BaseTest extends \Codeception\Test\Unit
{
  protected function _before()
  {
    // Given functions will return the first argument they will receive,
    // just like `when( $function_name )->justReturnArg()` was used for all of them.
    Functions\stubs(
      [
          'esc_attr',
          'esc_html',
          'esc_textarea',
          '__',
          '_x',
          'esc_html__',
          'esc_html_x',
          'esc_attr_x',
      ]
    );

    // Given functions can have a custom callback.
    Functions\stubs([
      'wp_json_encode' => function($data) {
          return json_encode($data);
      }
    ]);
  }
}