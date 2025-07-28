<?php

/**
 * The class register route for increment endpoint.
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftForms\Config\Config;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use WP_REST_Request;

/**
 * Class IncrementRoute
 */
class IncrementRoute extends AbstractBaseRoute
{
	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	protected $validator;

	/**
	 * Create a new instance that injects classes.
	 *
	 * @param ValidatorInterface $validator Inject validation methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
	) {
		$this->validator = $validator;
	}

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'increment';

	/**
	 * Get the base url of the route.
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return self::ROUTE_SLUG;
	}

	/**
	 * Method that returns REST response.
	 *
	 * @param WP_REST_Request $request Data got from endpoint url.
	 *
	 * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function routeCallback(WP_REST_Request $request)
	{
		$permission = $this->checkUserPermission(Config::CAP_SETTINGS);
		if ($permission) {
			return \rest_ensure_response($permission);
		}

		$debug = [
			'request' => $request,
		];

		$params = $this->prepareSimpleApiParams($request);

		$formId = $params['formId'] ?? '';
		if (!$formId) {
			return \rest_ensure_response(
				ApiHelpers::getApiErrorPublicOutput(
					\esc_html__('Form ID key was not provided.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		FormsHelper::resetIncrement($formId);

		return \rest_ensure_response(
			ApiHelpers::getApiSuccessPublicOutput(
				\esc_html__('Increment reset successful.', 'eightshift-forms'),
				[],
				$debug
			)
		);
	}
}
