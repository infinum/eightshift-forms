<?php

/**
 * Endpoint for fetching segments for a list from Mailerlite.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/mailerlite-fetch-groups
 *
 * @package EightshiftForms\Rest
 */

declare(strict_types=1);

namespace EightshiftForms\Rest;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Captcha\BasicCaptcha;
use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Integrations\Mailerlite\Mailerlite;

/**
 * Class MailerliteFetchGroupsRoute
 */
class MailerliteFetchGroupsRoute extends BaseRoute implements Filters
{

	/**
	 * Route slug
	 *
	 * @var string
	 */
	public const ENDPOINT_SLUG = '/mailerlite-fetch-groups';

	/**
	 * Mailerlite object.
	 *
	 * @var Mailerlite
	 */
	protected $mailerlite;

	/**
	 * Basic Captcha object.
	 *
	 * @var BasicCaptcha
	 */
	protected $basicCaptcha;

	/**
	 * Construct object
	 *
	 * @param Mailerlite   $mailerlite    Mailerlite object.
	 * @param BasicCaptcha $basicCaptcha BasicCaptcha object.
	 */
	public function __construct(Mailerlite $mailerlite, BasicCaptcha $basicCaptcha)
	{
		$this->mailerlite   = $mailerlite;
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
			$params = $this->verifyRequest($request);
		} catch (UnverifiedRequestException $e) {
			return rest_ensure_response($e->getData());
		}

	  // Retrieve all entities from the "leads" Entity Set.
		try {
			$response = $this->mailerlite->getAllGroups();
		} catch (\Exception $e) {
			return \rest_ensure_response([
				'code' => $e->getCode(),
				'message' => esc_html__('error', 'eightshift-forms'),
				'data' => [
					'error' => $e->getMessage(),
				],
			]);
		}

		return \rest_ensure_response([
			'code' => 200,
			'data' => $response,
			'message' => esc_html__('success', 'eightshift-forms'),
		]);
	}
}
