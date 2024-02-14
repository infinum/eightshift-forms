<?php

/**
 * The class register route for debug encrypt testing endpoint
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsEncryption;
use EightshiftFormsVendor\EightshiftFormsUtils\Rest\Routes\AbstractUtilsBaseRoute;
use WP_REST_Request;

/**
 * Class DebugEncryptRoute
 */
class DebugEncryptRoute extends AbstractUtilsBaseRoute
{
	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	protected $validator;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject validation methods.
	 */
	public function __construct(
		ValidatorInterface $validator
	) {
		$this->validator = $validator;
	}

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'debug-encrypt';

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

		$type = $params['type'] ?? '';
		if (!$type) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					\esc_html__('Type key was not provided.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$data = $params['data'] ?? '';
		if (!$data) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					\esc_html__('Data key was not provided.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		if ($type === 'encrypt') {
			$output = UtilsEncryption::encryptor($data);
		} else {
			$output = UtilsEncryption::decryptor($data);
		}

		if (!$output) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					// translators: %s will be replaced with the action type.
					\sprintf(\esc_html__('%s failed!', 'eightshift-forms'), \ucfirst($type)),
					[],
					$debug
				)
			);
		}

		// Finish.
		return \rest_ensure_response(
			UtilsApiHelper::getApiSuccessPublicOutput(
				// translators: %s will be replaced with the action type.
				\sprintf(\esc_html__('%s finished successfully!', 'eightshift-forms'), \ucfirst($type)),
				[
					'output' => $output,
				],
				$debug
			)
		);
	}
}
