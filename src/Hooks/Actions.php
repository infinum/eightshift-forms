<?php

/**
 * The Filters class, used for defining available filters
 *
 * @package EightshiftForms\Hooks
 */

declare(strict_types=1);

namespace EightshiftForms\Hooks;

/**
 * The Actions class, used for defining available actions.
 */
interface Actions
{

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

  /**
   * Use this action if you need to echo extra hidden (or non-hidden) fields to the form specifically for your project.
   * Make sure to echo valid HTML (most likely <input type="hidden"> fields) and this will be echoed inside the form.
   *
   * The action's callback needs to be provided in your project.
   *
   * Example:
   *
   *   public function register(): void {
   *     add_action( 'eightshift_forms/extra_form_fields', [ $this, 'add_additional_fields' ], 1, 1 );
   *   }
   *
   *   @param $attributes Array of form's attributes.
   *   public function add_additional_fields( array $attributes ) {
   *     echo '<input type="hidden" name="some-extra-field" value="your-value" />';
   *   }
   *
   * @var string
   */
	const EXTRA_FORM_FIELDS = 'eightshift_forms/extra_form_fields';
}
