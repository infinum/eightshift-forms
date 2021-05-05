<?php

/**
 * Endpoint for fetching data from Dynamics CRM.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/dynamics-crm-fetch-entity
 *
 * @package EightshiftForms\Rest
 */

declare(strict_types=1);

namespace EightshiftForms\Rest;

use EightshiftForms\Cache\Cache;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\DynamicsCrm;
use EightshiftForms\Captcha\BasicCaptcha;
use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Integrations\Authorization\AuthorizationInterface;
use GuzzleHttp\Exception\ClientException;

/**
 * Class DynamicsCrmFetchEntityRoute
 */
class DynamicsCrmFetchEntityRoute extends BaseRoute implements Filters
{

	/**
	 * This is how long this route's response will be cached.
	 *
	 * @var int
	 */
	public const HOW_LONG_TO_CACHE_RESPONSE_IN_SEC = 3600;

	/**
	 * Dynamics CRM entity param.
	 *
	 * @var string
	 */
	public const ENTITY_PARAM = 'dynamics-crm-entity';

	/**
	 * Route slug
	 *
	 * @var string
	 */
	public const ENDPOINT_SLUG = '/dynamics-crm-fetch-entity';

	/**
	 * Dynamics CRM object.
	 *
	 * @var DynamicsCrm
	 */
	private $dynamicsCrm;

	/**
	 * Cache implementation obj.
	 *
	 * @var Cache
	 */
	private $cache;

	/**
	 * Construct object
	 *
	 * @param DynamicsCrm            $dynamicsCrm    Dynamics CRM object.
	 * @param AuthorizationInterface $hmac            Authorization object.
	 * @param Cache                  $transientCache Cache object.
	 */
	public function __construct(DynamicsCrm $dynamicsCrm, AuthorizationInterface $hmac, Cache $transientCache)
	{
		$this->dynamicsCrm = $dynamicsCrm;
		$this->hmac         = $hmac;
		$this->cache        = $transientCache;
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

		// Load the response from cache if possible.
		$cacheKey = $this->cache->calculateCacheKeyForRequest(self::ENDPOINT_SLUG, $this->getRouteUri(), $params);

		if ($this->cache->exists($cacheKey)) {
			return \rest_ensure_response([
				'code' => 200,
				'data' => json_decode($this->cache->get($cacheKey), true),
			]);
		}

		$this->dynamicsCrm->setOauthCredentials([
			'url'           => apply_filters(self::DYNAMICS_CRM, 'authTokenUrl'),
			'client_id'     => apply_filters(self::DYNAMICS_CRM, 'clientId'),
			'client_secret' => apply_filters(self::DYNAMICS_CRM, 'clientSecret'),
			'scope'         => apply_filters(self::DYNAMICS_CRM, 'scope'),
			'api_url'       => apply_filters(self::DYNAMICS_CRM, 'apiUrl'),
		]);

		// Retrieve all entities from the "leads" Entity Set.
		try {
			$response = $this->dynamicsCrm->fetchAllFromEntity($entity, $params);
			$this->cache->save($cacheKey, (string) wp_json_encode($response), self::HOW_LONG_TO_CACHE_RESPONSE_IN_SEC);
		} catch (ClientException $e) {
			$error = ! empty($e->getResponse()) ? $e->getResponse()->getBody()->getContents() : esc_html__('Unknown error', 'eightshift-forms');
			return $this->restResponseHandlerUnknownError(['error' => $error]);
		} catch (\Exception $e) {
			return $this->restResponseHandlerUnknownError(['error' => $e->getMessage()]);
		}

		return \rest_ensure_response(
			[
			'code' => 200,
			'data' => json_decode((string) wp_json_encode($response), true),
			]
		);
	}

	/**
	 * Returns keys of irrelevant params which we don't want to send to CRM (even tho they're in form).
	 *
	 * @return bool
	 */
	public function permissionCallback(): bool
	{
		return true;
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
	 * Provide the expected salt ($this->getAuthorizationSalt()) for this route. This
	 * should be some secret. For example the secretKey for accessing the 3rd party route this route is
	 * handling.
	 *
	 * If this function returns a non-empty value, it is assumed the route requires authorization.
	 *
	 * @return string
	 */
	protected function getAuthorizationSalt(): string
	{
		return \apply_filters(self::DYNAMICS_CRM, 'clientSecret') ?? 'invalid-salt';
	}
}
