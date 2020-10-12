<?php
/**
 * The Filters class, used for defining available filters
 *
 * @package Eightshift_Forms\Core
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Core;

/**
 * The Actions class, used for defining available actions.
 */
class Actions {

  /**
   * This action should be used when doing Buckaroo integration. It will run after the user
   * has been redirected from Buckaroo (with appropriate status - success, cancel, error, reject)
   * back to our site but before he is redirected to the success / error page defined in Form's options.
   *
   * The action's callback needs to be provided in your project.
   *
   * @var string
   */
  const BUCKAROO_RESPONSE_HANDLER = 'eightshift_forms/buckaroo_response_handler';
}
