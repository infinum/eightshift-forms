<?php

/**
 * Endpoint for getting Greenhouse jobs list used in the form to select.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/greenhouse-jobs-list
 *
 * @package EightshiftForms\Rest
 */

declare(strict_types=1);

namespace EightshiftForms\Rest;

use EightshiftForms\Exception\MissingFilterInfoException;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\Greenhouse\GreenhouseClientInterface;

/**
 * Class GreenhouseJobsFetchRoute
 */
class GreenhouseJobsFetchRoute extends BaseRoute implements Filters
{

	/**
	 * Route slug
	 *
	 * @var string
	 */
	public const ENDPOINT_SLUG = '/greenhouse-jobs-list';

	/**
	 * Instance variable of GreenhouseClientInterface data.
	 *
	 * @var GreenhouseClientInterface
	 */
	protected $greenhouse;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param GreenhouseClientInterface $greenhouse Inject GreenhouseClientInterface which holds data for greenhouse connection.
	 */
	public function __construct(GreenhouseClientInterface $greenhouse)
	{
		$this->greenhouse = $greenhouse;
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
			return \rest_ensure_response([
				'code' => 200,
				'data' => $this->greenhouse->getJobs(),
				'message' => \esc_html__('Success', 'eightshift-forms'),
			]);
		} catch (MissingFilterInfoException $e) {
			return $this->restResponseHandler('greenhouse-missing-keys', ['message' => $e->getMessage()]);
		}
	}
}
