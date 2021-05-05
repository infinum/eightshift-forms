<?php

/**
 * Endpoint for fetching data for highlight card component.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/dynamics-crm
 *
 * @package EightshiftForms\Rest
 */

declare(strict_types=1);

namespace EightshiftForms\Rest;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\DynamicsCrm;
use EightshiftForms\Captcha\BasicCaptcha;
use EightshiftForms\Exception\UnverifiedRequestException;
use GuzzleHttp\Exception\ClientException;

/**
 * Class DynamicsCrmRoute
 */
class DynamicsCrmRoute extends BaseRoute implements Filters, ActiveRouteInterface
{

	public const ENTITY_PARAM = 'dynamics-crm-entity';

	/**
	 * Route slug
	 *
	 * @var string
	 */
	public const ENDPOINT_SLUG = '/dynamics-crm';

	/**
	 * Dynamics CRM object.
	 *
	 * @var DynamicsCrm
	 */
	protected $dynamicsCrm;

	/**
	 * Basic Captcha object.
	 *
	 * @var BasicCaptcha
	 */
	protected $basicCaptcha;

	/**
	 * Construct object
	 *
	 * @param DynamicsCrm  $dynamicsCrm Dynamics CRM object.
	 * @param BasicCaptcha $basicCaptcha BasicCaptcha object.
	 */
	public function __construct(DynamicsCrm $dynamicsCrm, BasicCaptcha $basicCaptcha)
	{
		$this->dynamicsCrm  = $dynamicsCrm;
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
	public function routeCallback(\WP_REST_Request $request)
	{

		try {
			$params = $this->verifyRequest($request, self::DYNAMICS_CRM);
		} catch (UnverifiedRequestException $e) {
			return rest_ensure_response($e->getData());
		}

		// We don't want to send thee entity to CRM or it will reject our request.
		$entity = $params[self::ENTITY_PARAM];
		$params = $this->unsetIrrelevantParams($params);

		$this->dynamicsCrm->setOauthCredentials([
			'url'           => apply_filters(self::DYNAMICS_CRM, 'authTokenUrl'),
			'client_id'     => apply_filters(self::DYNAMICS_CRM, 'clientId'),
			'client_secret' => apply_filters(self::DYNAMICS_CRM, 'clientSecret'),
			'scope'         => apply_filters(self::DYNAMICS_CRM, 'scope'),
			'api_url'       => apply_filters(self::DYNAMICS_CRM, 'apiUrl'),
		]);

		// Retrieve all entities from the "leads" Entity Set.
		try {
			$response = $this->dynamicsCrm->addRecord($entity, $params);
		} catch (ClientException $e) {
			$error = ! empty($e->getResponse()) ? $e->getResponse()->getBody()->getContents() : '';
			$error = empty($error) ? $e->getMessage() : $error;
			return $this->restResponseHandlerUnknownError(['error' => $error]);
		}

		return \rest_ensure_response([
			'code' => 200,
			'data' => $response,
		]);
	}

	/**
	 * Returns keys of irrelevant params which we don't want to send to CRM (even tho they're in form).
	 *
	 * @return array
	 */
	protected function getIrrelevantParams(): array
	{
		return [
			self::ENTITY_PARAM,
			BasicCaptcha::FIRST_NUMBER_KEY,
			BasicCaptcha::SECOND_NUMBER_KEY,
			BasicCaptcha::RESULT_KEY,
			'privacy',
			'privacy-policy',
		];
	}

	/**
	 * Defines a list of required parameters which must be present in the request or it will error out.
	 *
	 * @return array
	 */
	protected function getRequiredParams(): array
	{
		return [
			self::ENTITY_PARAM,
		];
	}

	/**
	 * Toggle if this route requires nonce verification
	 *
	 * @return bool
	 */
	protected function requiresNonceVerification(): bool
	{
		return true;
	}

	/**
	 * Returns allowed methods for this route.
	 *
	 * @return string|array
	 */
	protected function getMethods()
	{
		return static::CREATABLE;
	}
}
