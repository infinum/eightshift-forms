<?php

/**
 * The class register route for Form Settings endpoint
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Exception\UnverifiedRequestException;

/**
 * Class FormSettingsSubmitRoute
 */
class FormSettingsSubmitRoute extends AbstractBaseRoute
{

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/form-settings-submit';

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
			// Validate request.
			$params = $this->verifyRequest($request);

			$postParams = $params['post'];

			// Get normal form ID string.
			$formId = $this->getFormId($postParams);

			// Remove unecesery params.
			$postParams = $this->removeUneceseryParams($postParams);

			// Save all fields in the settings.
			foreach ($postParams as $key => $value) {
				$value = json_decode($value, true);

				// Check if key needs updating or deleting.
				if ($value['value']) {
					\update_post_meta($formId, $key, $value['value']);
				} else {
					\delete_post_meta($formId, $key);
				}
			}

			return \rest_ensure_response([
				'code' => 200,
				'status' => 'success',
				'message' => esc_html__('Form successfully saved!', 'eightshift-form'),
			]);

			// return \rest_ensure_response($response);
		} catch (UnverifiedRequestException $e) {
			// Die if any of the validation fails.
			return \rest_ensure_response($e->getData());
		}
	}
}
