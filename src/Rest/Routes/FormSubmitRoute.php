<?php

/**
 * The class register route for $className endpoint
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Helpers\TraitHelper;
use EightshiftForms\Settings\FormOption;

/**
 * Class FormSubmitRoute
 */
class FormSubmitRoute extends AbstractBaseRoute
{

	use TraitHelper;

	public const ROUTE_SLUG = '/form-submit';

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
	 * Get callback arguments array
	 *
	 * @return array Either an array of options for the endpoint, or an array of arrays for multiple methods.
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
			$params =	$this->verifyRequest($request);

			$postParams = $params['post'];

			$formId = $this->getFormId($postParams, true);

			$mailerUse = $this->getSettingsValue(FormOption::MAILER_USE_KEY, $formId);

			// if ($mailerUse) {
			// 	$this->mailer->sendFormEmail(
			// 		$formId,
			// 		$this->getSettingsValue(FormOption::MAILER_TO_KEY, $formId),
			// 		[],
			// 		$this->removeUneceseryParams($postParams)
			// 	);
			// } else {
			// 	return \rest_ensure_response([
			// 		'code' => 404,
			// 		'status' => 'error',
			// 		'message' => esc_html__('Email not sent due to configuration issue. Please contact your admin.', 'eightshift-form'),
			// 	]);
			// }

			return \rest_ensure_response([
				'code' => 200,
				'status' => 'success',
				'message' => esc_html__('Success', 'eightshift-form'),
			]);

			// return \rest_ensure_response($response);
		} catch (UnverifiedRequestException $e) {
			// Die if any of the validation fails.
			return \rest_ensure_response($e->getData());
		}
	}
}
