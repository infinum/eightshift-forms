<?php
/**
 * Endpoint for testing BaseRoute.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/test-route
 *
 * @package EightshiftForms\Rest
 */

declare( strict_types=1 );

namespace EightshiftFormsTests\Mocks;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Captcha\BasicCaptcha;
use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Integrations\Authorization\AuthorizationInterface;
use EightshiftForms\Rest\BaseRoute;

/**
 * Class TestRoute
 */
class TestRoute extends BaseRoute implements Filters {

	/**
	 * Route slug
	 *
	 * @var string
	 */
	public const ENDPOINT_SLUG = '/test-route';

	/**
	 * Test salt used for this route.
	 *
	 * @var string
	 */
	public const TEST_SALT = '1234-test-salt';

	/**
	 * Mock of required parameters.
	 *
	 * @var array
	 */
	public const REQUIRED_PARAMETER_1 = 'required-param';
	public const REQUIRED_PARAMETER_2 = 'required-param-2';
	public const IRRELEVANT_PARAM     = 'irrelevant-param';

	/**
	 * Construct object
	 *
	 * @param AuthorizationInterface $hmac          Authorization object.
	 * @param BasicCaptcha           $basicCaptcha BasicCaptcha object.
	 */
	public function __construct(
    AuthorizationInterface $hmac,
    BasicCaptcha $basicCaptcha
  ) {
    $this->hmac = $hmac;
    $this->basicCaptcha = $basicCaptcha;
  }

	/**
	 * Method that returns rest response
	 *
	 * @param  \WP_REST_Request $request Data got from endpoint url.
	 *
	 * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function routeCallback( \WP_REST_Request $request ) {

    try {
      $params = $this->verifyRequest( $request );
    } catch ( UnverifiedRequestException $e ) {
      return rest_ensure_response( $e->getData() );
    }

    $params = $this->unsetIrrelevantParams( $params );

    $mockResponse = [
      'message' => 'all good',
      'received-params' => $params,
    ];

    return \rest_ensure_response(
      [
        'code' => 200,
        'data' => $mockResponse,
      ]
    );
  }

	/**
	 * Define authorization salt used for request to response handler.
	 *
	 * @return string
	 */
	protected function getAuthorizationSalt(): string {
    return self::TEST_SALT;
  }

	/**
	 * Defines a list of required parameters which must be present in the request or it will error out.
	 *
	 * @return array
	 */
	protected function getRequiredParams(): array {
    return [
      self::REQUIRED_PARAMETER_1,
      self::REQUIRED_PARAMETER_2,
    ];
  }

	/**
	 * Returns keys of irrelevant params which we don't want to send to CRM (even tho they're in form).
	 *
	 * @return array
	 */
	protected function getIrrelevantParams(): array {
    return [
      self::IRRELEVANT_PARAM,
    ];
  }
}
