<?php
/**
 * The Filters class, used for defining available filters
 *
 * @package Eightshift_Forms\Core
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Core;

use Eightshift_Libs\Core\Service;

/**
 * The Filters class, used for defining available filters
 */
class Filters implements Service {

  const DYNAMICS_CRM = 'eightshift_forms/dynamics_info';

  /**
   * A register method.
   *
   * @return void
   */
  public function register() : void {
    add_filter( self::DYNAMICS_CRM, function() { error_log('this is called'); return ''; }, 1, 1 );
  }
}
