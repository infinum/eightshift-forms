<?php

/**
 * The class register route for getting Greenhouse Jobs endpoint
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Integrations\Greenhouse\GreenhouseClientInterface;

/**
 * Class GreenhouseJobsRoute
 */
class GreenhouseJobsRoute extends AbstractBaseRoute
{
	/**
	 * Instance variable of GreenhouseClientInterface data.
	 *
	 * @var GreenhouseClientInterface
	 */
	protected $greenhouseClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param GreenhouseClientInterface $greenhouseClient Inject GreenhouseClientInterface which holds Greenhouse connect data.
	 */
	public function __construct(GreenhouseClientInterface $greenhouseClient)
	{
		$this->greenhouseClient = $greenhouseClient;
	}

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/greenhouse-jobs';

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return self::ROUTE_SLUG;
	}

	/**
	 * Returns allowed methods for this route.
	 *
	 * @return string
	 */
	protected function getMethods(): string
	{
		return static::READABLE;
	}

	/**
	 * Get callback arguments array
	 *
	 * @return array<string, mixed> Either an array of options for the endpoint, or an array of arrays for multiple methods.
	 */
	protected function getCallbackArguments(): array
	{
		return [
			'methods' => $this->getMethods(),
			'callback' => [$this, 'routeCallback'],
			'permission_callback' => [$this, 'permissionCallback'],
		];
	}

	/**
	 * Method that returns rest response
	 *
	 * @param \WP_REST_Request $request Data got from endpoint url.
	 *
	 * @return \WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function routeCallback(\WP_REST_Request $request)
	{
		// Try catch request.
		try {
			$output = [];

			foreach ($this->greenhouseClient->getJobs() as $job) {
				// Remove unecesery data for security reasons.
				unset($job['id']);
				$output[] = $job;
			}

			return $output;
		} catch (UnverifiedRequestException $e) {
			// Die if any of the validation fails.
			return \rest_ensure_response($e->getData());
		}
	}
}
