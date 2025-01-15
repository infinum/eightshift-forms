<?php

/**
 * The class register route for increment endpoint.
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\Entries\EntriesHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Rest\Routes\AbstractUtilsBaseRoute;
use EightshiftFormsVendor\EightshiftLibs\Cache\ManifestCacheInterface;
use WP_REST_Request;

/**
 * Class IncrementRoute
 */
class IncrementRoute extends AbstractUtilsBaseRoute
{
	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	protected $validator;

	/**
	 * Instance variable for listing data.
	 *
	 * @var ManifestCacheInterface
	 */
	protected $manifestCache;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject validation methods.
	 * @param ManifestCacheInterface $manifestCache Inject manifest cache interface.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ManifestCacheInterface $manifestCache
	) {
		$this->validator = $validator;
		$this->manifestCache = $manifestCache;
	}

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'increment';

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
	 * Method that returns rest response
	 *
	 * @param WP_REST_Request $request Data got from endpoint url.
	 *
	 * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function routeCallback(WP_REST_Request $request)
	{
		$premission = $this->checkUserPermission();
		if ($premission) {
			return \rest_ensure_response($premission);
		}

		$debug = [
			'request' => $request,
		];

		$params = $this->prepareSimpleApiParams($request);

		$formId = $params['formId'] ?? '';
		if (!$formId) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					\esc_html__('Form ID key was not provided.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		EntriesHelper::resetIncrement($formId);

		return \rest_ensure_response(
			UtilsApiHelper::getApiSuccessPublicOutput(
				\esc_html__('Increment reset successful.', 'eightshift-forms'),
				[],
				$debug
			)
		);
	}
}
