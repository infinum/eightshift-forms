<?php
/**
 * The Filters class, used for defining available filters
 *
 * @package Eightshift_Forms\Hooks
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Hooks;

/**
 * The Filters class, used for defining available filters.
 */
interface Filters {

  /**
   * Filter used to provide Microsoft Dynamics CRM credentials / info from your project to Eightshift Forms.
   *
   * Expects an array to be returned with at least the following keys:
   *  'client_id'
   *  'client_secret'
   *  'auth_token_url'
   *  'scope'
   *  'api_url'
   *  'available_entities'
   *
   * Example:
   *
   *   public function register(): void {
   *     add_filter( 'eightshift_forms/dynamics_info', [ $this, 'get_info' ], 11, 1 );
   *   }
   *
   *   public function get_info( string $key ) {
   *     $available_entities = [
   *       'entity1',
   *       'entity2,
   *        // ...
   *     ];
   *
   *     $info = [
   *       'client_id' => 'defined( 'ESF_DYNAMICS_CRM_CLIENT_ID' ) ? ESF_DYNAMICS_CRM_CLIENT_ID : '','
   *       'client_secret' => defined( 'ESF_DYNAMICS_CRM_CLIENT_SECRET' ) ? ESF_DYNAMICS_CRM_CLIENT_SECRET : '',
   *       'auth_token_url' => defined( 'ESF_DYNAMICS_CRM_AUTH_TOKEN_URL' ) ? ESF_DYNAMICS_CRM_AUTH_TOKEN_URL : '',
   *       'scope' => defined( 'ESF_DYNAMICS_CRM_SCOPE' ) ? ESF_DYNAMICS_CRM_SCOPE : '',
   *       'api_url' => defined( 'ESF_DYNAMICS_CRM_API_URL' ) ? ESF_DYNAMICS_CRM_API_URL : '',
   *       'available_entities' => $available_entities,
   *     ];
   *
   *     return $info[ $key ] ?? '';
   *   }
   *
   * @var string
   */
  const GENERAL                 = 'eightshift_forms/general_info';
  const PREFILL_GENERIC_MULTI   = 'eightshift_forms/prefill/multi';
  const PREFILL_GENERIC_SINGLE  = 'eightshift_forms/prefill/single';
  const ALLOWED_BLOCKS          = 'eightshift_forms/allowed_blocks';
  const AUTHORIZATION_GENERATOR = 'eightshift_forms/authorization_generator';
  const DYNAMICS_CRM            = 'eightshift_forms/dynamics_info';
  const BUCKAROO                = 'eightshift_forms/buckaroo';
  const MAILCHIMP               = 'eightshift_forms/mailchimp';

  /**
   * Filter used to add additional required parameters to Buckaroo Emandate route
   *
   * You should return an array with 1 or more key names and those keys will now be required params.
   *
   * Example:
   *
   *   public function register(): void {
   *     add_filter( 'eightshift_forms/required_params/buckaroo_emandate', [ $this, 'add_required_params' ], 11, 1 );
   *   }
   *
   *   public function get_info( array $required_params ) {
   *     $required_params[] = 'new-key';
   *     return $required_params;
   *   }
   *
   * @var string
   */
  const REQUIRED_PARAMS_BUCKAROO_EMANDATE = 'eightshift_forms/required_params/buckaroo_emandate';


  /**
   * Filter used to add additional required parameters to Buckaroo Ideal route
   *
   * You should return an array with 1 or more key names and those keys will now be required params.
   *
   * Example:
   *
   *   public function register(): void {
   *     add_filter( 'eightshift_forms/required_params/buckaroo_ideal', [ $this, 'add_required_params' ], 11, 1 );
   *   }
   *
   *   public function get_info( array $required_params ) {
   *     $required_params[] = 'new-key';
   *     return $required_params;
   *   }
   *
   * @var string
   */
  const REQUIRED_PARAMS_BUCKAROO_IDEAL = 'eightshift_forms/required_params/buckaroo_ideal';

  /**
   * Filter used to modify which roles have access to Forms CPT (by default it's just admins).
   *
   * You should return an array with the key name == role_name and value as true / false (if you wish to add or remove access)
   * [
   *   'administrator' => true,
   *   'editor' => false,
   *   'author' => false,
   *   'contributor' => false,
   *   'subscriber' => false,
   * ]
   *
   * Example:
   *
   *   public function register(): void {
   *     add_filter( 'eightshift_forms/roles_with_access_to_forms', [ $this, 'modify_roles_with_access' ], 11, 1 );
   *   }
   *
   *   public function modify_roles_with_access( array $existing_roles ) {
   *     $existing_roles['editor] = true;
   *     return $existing_roles;
   *   }
   *
   * @var string
   */
  const ROLES_WITH_FORMS_ACCESS = 'eightshift_forms/roles_with_access_to_forms';
}
