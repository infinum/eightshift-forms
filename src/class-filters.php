<?php
/**
 * The Filters class, used for defining available filters
 *
 * @package Eightshift_Forms\Core
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Core;

/**
 * The Filters class, used for defining available filters.
 */
class Filters {

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
}
