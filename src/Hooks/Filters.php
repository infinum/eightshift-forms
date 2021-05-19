<?php

/**
 * The Filters class, used for defining available filters
 *
 * @package EightshiftForms\Hooks
 */

declare(strict_types=1);

namespace EightshiftForms\Hooks;

/**
 * The Filters class, used for defining available filters.
 */
interface Filters
{

	/**
	 * Filter used to provide form themes.
	 *
	 * Example:
	 *
	 *  public function register(): void {
	 *     add_filter( 'eightshift_forms/general_info', [ $this, 'getThemes' ], 11 );
	 *   }
	 *
	 *  public function getThemes(): array
	 *   {
	 *     return [
	 *       'Newsletter',
	 *     ];
	 *   }
	 *
	 * @var string
	 */
	public const GENERAL = 'eightshift_forms/general_info';

	/**
	 * Filter used for defining prefill sources for form blocks which allow user to select one or more things
	 * (select & radio). Then in that block's options, the editor can select any of the prefill/multi sources you set.
	 * Prefilled block won't allow editor to modify the options. The options will instead be the ones you set in code.
	 *
	 * Example:
	 *
	 *  public function register(): void {
	 *     add_filter( 'eightshift_forms/prefill/multi', [ $this, 'prefillMulti' ], 1, 1 );
	 *   }
	 *
	 *
	 *  public function prefillMulti( array $prefilled_data ): array {
	 *    $prefilledData[ 'source-key' ] = [
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
	 *   return $prefilledData;
	 *  }
	 *
	 * @var string
	 */
	public const PREFILL_GENERIC_MULTI = 'eightshift_forms/prefill/multi';

	/**
	 * Filter used for defining prefill sources for form blocks which allow user to input a single value
	 * (input, textarea, checkbox). Then in that block's options, the editor can select any of the prefill/single sources you set.
	 * Prefilled block won't allow editor to modify the value. The options will instead be the ones you set in code.
	 *
	 * NOT YET IMPLEMENTED
	 *
	 * @var string
	 */
	public const PREFILL_GENERIC_SINGLE = 'eightshift_forms/prefill/single';

	/**
	 * Filter used for allowing your own blocks to form to be added inside a form.
	 *
	 * Example:
	 *
	 *  public function register(): void {
	 *     add_filter( 'eightshift_forms/allowed_blocks', [ $this, 'addAllowedBlocks' ], 1, 1 );
	 *   }
	 *
	 *  public function addAllowedBlocks( $allowed_blocks ) {
	 *    $allowed_blocks[] = 'your-namespace/paragraph';
	 *    $allowed_blocks[] = 'your-namespace/another-block';
	 *    return $allowed_blocks;
	 *  }
	 *
	 * @var string
	 */
	public const ALLOWED_BLOCKS = 'eightshift_forms/allowed_blocks';

	/**
	 * Used for generating authorization hash based on an array of parameters and secret hash. You need this if
	 * you wish to send requests to routes which require authorization from your project.
	 *
	 * You don't need to add the filter in your project, you can just use apply_filters().
	 *
	 * All routes currently use Hmac authorization and you should use it like in the example.
	 *
	 * Example:
	 *
	 *  use \EightshiftForms\Integrations\Authorization\Hmac;
	 *
	 *  public function addAuthorizationHashToParams( array $params, string $secret ) {
	 *    $params[ Hmac::AUTHORIZATION_KEY ] = apply_filters( 'eightshift_forms/authorization_generator', $params, $secret )
	 *    return $params;
	 *  }
	 *
	 * @var string
	 */
	public const AUTHORIZATION_GENERATOR = 'eightshift_forms/authorization_generator';

	/**
	 * Filter used for providing Microsoft 365 Dynamics CRM credentials.
	 * IMPORTANT - Make sure to always return a string, even if $key isn't set.
	 *
	 * Example:
	 *
	 *  public function register(): void {
	 *     add_filter( 'eightshift_forms/dynamics_info', [ $this, 'getInfo' ], 1, 1 );
	 *   }
	 *
	 *  public function getInfo( string $key ) {
	 *    $info = [
	 *      'clientId' => 'client-id',
	 *      'clientSecret' => 'client-secret',
	 *      'authTokenUrl' => 'https://login.microsoftonline.com/1234-some-hash/oauth2/v2.0/token',
	 *      'scope' => 'https://your-crm-api-endpoint.dynamics.com/.default',
	 *      'apiUrl' => 'https://your-crm-api-endpoint.dynamics.com/api/data/v9.1',
	 *      'availableEntities' => [
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
	public const DYNAMICS_CRM = 'eightshift_forms/dynamics_info';

	/**
	 * Filter used for providing Buckaroo credentials.
	 * IMPORTANT - Make sure to always return a string, even if $key isn't set.
	 *
	 * Example:
	 *
	 *  public function register(): void {
	 *     add_filter( 'eightshift_forms/buckaroo', [ $this, 'getInfo' ], 1, 1 );
	 *   }
	 *
	 *  public function getInfo( string $key ) {
	 *    $info = [
	 *      'websiteKey' => 'websiteKey',
	 *      'secretKey' => 'secretKey',
	 *    ];
	 *
	 *    return $info[ $key ] ?? '';
	 *  }
	 *
	 * @var string
	 */
	public const BUCKAROO = 'eightshift_forms/buckaroo';

	/**
	 * Filter used for providing Mailchimp credentials.
	 * IMPORTANT - Make sure to always return a string, even if $key isn't set.
	 *
	 * Example:
	 *
	 *  public function register(): void {
	 *     add_filter( 'eightshift_forms/mailchimp', [ $this, 'getInfo' ], 1, 1 );
	 *   }
	 *
	 *  public function getInfo( string $key ) {
	 *    $info = [
	 *      'apiKey' => 'your-api-key',
	 *      'server' => 'us2',
	 *    ];
	 *
	 *    return $info[ $key ] ?? '';
	 *  }
	 *
	 * @var string
	 */
	public const MAILCHIMP = 'eightshift_forms/mailchimp';

	/**
	 * Filter used for providing mailerlite credentials.
	 * IMPORTANT - Make sure to always return a string, even if $key isn't set.
	 *
	 * Example:
	 *
	 *  public function register(): void {
	 *     add_filter( 'eightshift_forms/mailerlite', [ $this, 'getInfo' ], 1 );
	 *   }
	 *
	 *  public function getInfo( string $key ): string
	 *  {
	 *    $info = [
	 *      'apiKey' => 'your-api-key',
	 *    ];
	 *
	 *    return $info[ $key ] ?? '';
	 *  }
	 *
	 * @var string
	 */
	public const MAILERLITE = 'eightshift_forms/mailerlite';

	/**
	 * Filter used to add additional required parameters to Buckaroo Emandate route
	 *
	 * You should return an array with 1 or more key names and those keys will now be required params.
	 *
	 * Example:
	 *
	 *  public function register(): void {
	 *     add_filter('eightshift_forms/required_params/buckaroo_emandate', [$this, 'addRequiredParams'], 11, 1);
	 *   }
	 *
	 *  public function getInfo(array $requiredParams) {
	 *     $requiredParams[] = 'new-key';
	 *     return $requiredParams;
	 *   }
	 *
	 * @var string
	 */
	public const REQUIRED_PARAMS_BUCKAROO_EMANDATE = 'eightshift_forms/required_params/buckaroo_emandate';


	/**
	 * Filter used to add additional required parameters to Buckaroo Ideal route
	 *
	 * You should return an array with 1 or more key names and those keys will now be required params.
	 *
	 * Example:
	 *
	 *  public function register(): void {
	 *     add_filter('eightshift_forms/required_params/buckaroo_ideal', [$this, 'addRequiredParams'], 11, 1);
	 *   }
	 *
	 *  public function getInfo(array $requiredParams) {
	 *     $requiredParams[] = 'new-key';
	 *     return $requiredParams;
	 *   }
	 *
	 * @var string
	 */
	public const REQUIRED_PARAMS_BUCKAROO_IDEAL = 'eightshift_forms/required_params/buckaroo_ideal';

	/**
	 * Filter used for providing filtering the Buckaroo redirect URL (the url to which the user is
	 * redirected after completing / erroring out on payment - these are defined in Form's Buckaroo options)
	 *
	 * Example:
	 *
	 *  public function register(): void {
	 *     add_filter( 'eightshift_forms/modify_buckaroo_redirect_url', [ $this, 'modifyUrl' ], 1, 3 );
	 *   }
	 *
	 *  public function modifyUrl(string $redirectUrl, array $params, BuckarooResponse buckarooResponse) {
	 *    $redirectUrl = add_query_arg( 'key', 'value', $redirectUrl );
	 *    return $redirectUrl;
	 *  }
	 *
	 * @var string
	 */
	public const BUCKAROO_REDIRECT_URL = 'eightshift_forms/modify_buckaroo_redirectUrl';

	/**
	 * This filter receives the buckaroo params (POST params that Buckaroo sends back to our site after processing the
	 * request) and allows you to modify them. For example you can check if everything is valid, mock some data
	 * or otherwise modify the params. You also get GET params as well so you can change things depending on the
	 * sending context (which route / service it was sent from, which fields were set by user, etc).
	 *
	 * Example:
	 *
	 *  public function register(): void {
	 *     add_filter('eightshift_forms/buckaroo_filter_buckaroo_params', [$this, 'modifyParams'], 1, 2);
	 *   }
	 *
	 *  public function modifyParams( array $params, array $buckarooParams ) {
	 *    if ($params['test-enabled-field'] === '1') {
	 *      $buckarooParams['BRQ_STATUSCODE'] = 123;
	 *      $buckarooParams['BRQ_MOCK'] = true;
	 *    }
	 *
	 *    return $buckarooParams;
	 *  }
	 *
	 * @var string
	 */
	public const BUCKAROO_FILTER_BUCKAROO_PARAMS = 'eightshift_forms/buckaroo_filter_buckaroo_params';

	/**
	 * Filter used for providing filtering the Buckaroo redirect URL (the url to which the user is
	 * redirected after completing / erroring out on payment - these are defined in Form's Buckaroo options)
	 *
	 * Example:
	 *
	 *  public function register(): void {
	 *     add_filter('eightshift_forms/buckaroo_pay_by_email_redirect_url_override', [$this, 'modifyUrl'], 1, 1);
	 *   }
	 *
	 *  public function modifyUrl(string $redirectUrl) {
	 *    $redirectUrl = add_query_arg('key', 'value', $redirectUrl);
	 *    return $redirectUrl;
	 *  }
	 *
	 * @var string
	 */
	public const BUCKAROO_PAY_BY_EMAIL_OVERRIDE = 'eightshift_forms/buckaroo_pay_by_email_redirect_url_override';

	/**
	 * Filter used to modify which roles have access to Forms CPT (by default it's just admins).
	 *
	 * You should return an array with the key name == roleName and value as true / false (if you wish to add or remove access)
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
	 *  public function register(): void {
	 *     add_filter('eightshift_forms/roles_with_access_to_forms', [$this, 'modifyRolesWithAccess'], 11, 1);
	 *   }
	 *
	 *  public function modifyRolesWithAccess(array $existingRoles) {
	 *     $existingRoles['editor'] = true;
	 *     return $existingRoles;
	 *   }
	 *
	 * @var string
	 */
	public const ROLES_WITH_FORMS_ACCESS = 'eightshift_forms/roles_with_access_to_forms';
}
