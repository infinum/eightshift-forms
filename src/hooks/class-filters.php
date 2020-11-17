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
  const GENERAL = 'eightshift_forms/general_info';

  /**
   * Filter used for defining prefill sources for form blocks which allow user to select one or more things
   * (select & radio). Then in that block's options, the editor can select any of the prefill/multi sources you set.
   * Prefilled block won't allow editor to modify the options. The options will instead be the ones you set in code.
   *
   * Example:
   *
   *   public function register(): void {
   *     add_filter( 'eightshift_forms/prefill/multi', [ $this, 'prefill_multi' ], 1, 1 );
   *   }
   *
   *
   *  public function prefill_multi( array $prefilled_data ): array {
   *    $prefilled_data[ 'source-key' ] = [
   *      'value' => 'source-key',
   *      'label' => 'Test source',
   *      'data' => [
   *        [
   *          'label' => 'Label source 1',
   *          'value' => 'value',
   *        ],
   *        [
   *          'label' => 'Label 2',
   *          'value' => 'value-2',
   *        ],
   *      ],
   *    ];
   *
   *   return $prefilled_data;
   *  }
   *
   * @var string
   */
  const PREFILL_GENERIC_MULTI = 'eightshift_forms/prefill/multi';

  /**
   * Filter used for defining prefill sources for form blocks which allow user to input a single value
   * (input, textarea, checkbox). Then in that block's options, the editor can select any of the prefill/single sources you set.
   * Prefilled block won't allow editor to modify the value. The options will instead be the ones you set in code.
   *
   * NOT YET IMPLEMENTED
   *
   * @var string
   */
  const PREFILL_GENERIC_SINGLE = 'eightshift_forms/prefill/single';

  /**
   * Filter used for allowing your own blocks to form to be added inside a form.
   *
   * Example:
   *
   *   public function register(): void {
   *     add_filter( 'eightshift_forms/allowed_blocks', [ $this, 'add_allowed_blocks' ], 1, 1 );
   *   }
   *
   *  public function add_allowed_blocks( $allowed_blocks ) {
   *    $allowed_blocks[] = 'your-namespace/paragraph';
   *    $allowed_blocks[] = 'your-namespace/another-block';
   *    return $allowed_blocks;
   *  }
   *
   * @var string
   */
  const ALLOWED_BLOCKS = 'eightshift_forms/allowed_blocks';

  /**
   * Used for generating authorization hash based on an array of parameters and secret hash. You need this if
   * you wish to send requests to routes which require authorization from your project.
   *
   * You don't need to add the filter in your project, you can just use apply_filters().
   *
   * All routes currently use HMAC authorization and you should use it like in the example.
   *
   * Example:
   *
   *  use \Eightshift_Forms\Integrations\Authorization\HMAC;
   *
   *  public function add_authorization_hash_to_params( array $params, string $secret ) {
   *    $params[ HMAC::AUTHORIZATION_KEY ] = apply_filters( 'eightshift_forms/authorization_generator', $params, $secret )
   *    return $params;
   *  }
   *
   * @var string
   */
  const AUTHORIZATION_GENERATOR = 'eightshift_forms/authorization_generator';

  /**
   * Filter used for providing Microsoft 365 Dynamics CRM credentials.
   * IMPORTANT - Make sure to always return a string, even if $key isn't set.
   *
   * Example:
   *
   *   public function register(): void {
   *     add_filter( 'eightshift_forms/dynamics_info', [ $this, 'get_info' ], 1, 1 );
   *   }
   *
   *  public function get_info( string $key ) {
   *    $info = [
   *      'client_id' => 'client-id',
   *      'client_secret' => 'client-secret',
   *      'auth_token_url' => 'https://login.microsoftonline.com/1234-some-hash/oauth2/v2.0/token',
   *      'scope' => 'https://your-crm-api-endpoint.dynamics.com/.default',
   *      'api_url' => 'https://your-crm-api-endpoint.dynamics.com/api/data/v9.1',
   *      'available_entities' => [
   *        'entity_1',
   *        'entity_2,
   *      ],
   *    ];
   *
   *    return $info[ $key ] ?? '';
   *  }
   *
   * @var string
   */
  const DYNAMICS_CRM = 'eightshift_forms/dynamics_info';

  /**
   * Filter used for providing Buckaroo credentials.
   * IMPORTANT - Make sure to always return a string, even if $key isn't set.
   *
   * Example:
   *
   *   public function register(): void {
   *     add_filter( 'eightshift_forms/buckaroo', [ $this, 'get_info' ], 1, 1 );
   *   }
   *
   *  public function get_info( string $key ) {
   *    $info = [
   *      'website_key' => 'website_key',
   *      'secret_key' => 'secret_key',
   *    ];
   *
   *    return $info[ $key ] ?? '';
   *  }
   *
   * @var string
   */
  const BUCKAROO = 'eightshift_forms/buckaroo';

  /**
   * Filter used for providing Mailchimp credentials.
   * IMPORTANT - Make sure to always return a string, even if $key isn't set.
   *
   * Example:
   *
   *   public function register(): void {
   *     add_filter( 'eightshift_forms/mailchimp', [ $this, 'get_info' ], 1, 1 );
   *   }
   *
   *  public function get_info( string $key ) {
   *    $info = [
   *      'api_key' => 'your-api-key',
   *      'server' => 'us2',
   *    ];
   *
   *    return $info[ $key ] ?? '';
   *  }
   *
   * @var string
   */
  const MAILCHIMP = 'eightshift_forms/mailchimp';

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
   * Filter used for providing filtering the Buckaroo redirect URL (the url to which the user is
   * redirected after completing / erroring out on payment - these are defined in Form's Buckaroo options)
   *
   * Example:
   *
   *   public function register(): void {
   *     add_filter( 'eightshift_forms/modify_buckaroo_redirect_url', [ $this, 'modify_url' ], 1, 3 );
   *   }
   *
   *  public function modify_url( string $redirect_url, array $params, Buckaroo_Response $buckaroo_response ) {
   *    $redirect_url = add_query_arg( 'key', 'value', $redirect_url );
   *    return $redirect_url;
   *  }
   *
   * @var string
   */
  const BUCKAROO_REDIRECT_URL = 'eightshift_forms/modify_buckaroo_redirect_url';

  /**
   * This filter receives the buckaroo params (POST params that Buckaroo sends back to our site after processing the
   * request) and allows you to modify them. For example you can check if everything is valid, mock some data
   * or otherwise modify the params. You also get GET params as well so you can change things depending on the
   * sending context (which route / service it was sent from, which fields were set by user, etc).
   *
   * Example:
   *
   *   public function register(): void {
   *     add_filter( 'eightshift_forms/buckaroo_filter_buckaroo_params', [ $this, 'modify_params' ], 1, 2 );
   *   }
   *
   *  public function modify_params( array $params, array $buckaroo_params ) {
   *    if ( $params['test-enabled-field'] === '1' ) {
   *      $buckaroo_params['BRQ_STATUSCODE'] = 123;
   *      $buckaroo_params['BRQ_MOCK'] = true;
   *    }
   *
   *    return $buckaroo_params;
   *  }
   *
   * @var string
   */
  const BUCKAROO_FILTER_BUCKAROO_PARAMS = 'eightshift_forms/buckaroo_filter_buckaroo_params';

  /**
   * Filter used for providing filtering the Buckaroo redirect URL (the url to which the user is
   * redirected after completing / erroring out on payment - these are defined in Form's Buckaroo options)
   *
   * Example:
   *
   *   public function register(): void {
   *     add_filter( 'eightshift_forms/buckaroo_pay_by_email_redirect_url_override', [ $this, 'modify_url' ], 1, 1 );
   *   }
   *
   *  public function modify_url( string $redirect_url ) {
   *    $redirect_url = add_query_arg( 'key', 'value', $redirect_url );
   *    return $redirect_url;
   *  }
   *
   * @var string
   */
  const BUCKAROO_PAY_BY_EMAIL_OVERRIDE = 'eightshift_forms/buckaroo_pay_by_email_redirect_url_override';

  /**
   * Filter used to modify which roles have access to Forms CPT (by default it's just admins).
   *
   * You should return an array with the key name == role_name and value as true / false (if you wish to add or remove access)
   * [
   *   'administrator' => true,
   *   'editor' => true,
   * ]
   *
   * If you wish to remove access from a role you previously authorized, you can just return it in the array with false:
   * [
   *   'administrator' => true,
   *   'editor' => false,
   * ]
   *
   * Example:
   *
   *   public function register(): void {
   *     add_filter( 'eightshift_forms/roles_with_access_to_forms', [ $this, 'modify_roles_with_access' ], 11, 1 );
   *   }
   *
   *   public function modify_roles_with_access( array $existing_roles ) {
   *     $existing_roles['editor'] = true;
   *     return $existing_roles;
   *   }
   *
   * @var string
   */
  const ROLES_WITH_FORMS_ACCESS = 'eightshift_forms/roles_with_access_to_forms';
}
