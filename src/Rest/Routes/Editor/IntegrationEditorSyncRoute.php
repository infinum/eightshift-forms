<?php

/**
 * The class to provide form builder json to block editor for integrations.
 *
 * @package EightshiftForms\Rest\Routes\Editor
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Editor;

use EightshiftForms\Integrations\IntegrationSyncInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\SettingsHelper;
use WP_REST_Request;

/**
 * Class IntegrationEditorSyncRoute
 */
class IntegrationEditorSyncRoute extends AbstractBaseRoute
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'sync';

	/**
	 * Instance variable for HubSpot form data.
	 *
	 * @var IntegrationSyncInterface
	 */
	protected $integrationSyncDiff;

	/**
	 * Create a new instance.
	 *
	 * @param IntegrationSyncInterface $integrationSyncDiff Inject IntegrationSyncDiff which holds sync data.
	 */
	public function __construct(IntegrationSyncInterface $integrationSyncDiff)
	{
		$this->integrationSyncDiff = $integrationSyncDiff;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/' . AbstractBaseRoute::ROUTE_PREFIX_INTEGRATION_EDITOR . '/' . self::ROUTE_SLUG;
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
	 * Returns allowed methods for this route.
	 *
	 * @return string
	 */
	protected function getMethods(): string
	{
		return static::READABLE;
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

		$formId = $request->get_param('id') ?? '';

		$syncForm = $this->integrationSyncDiff->syncFormEditor($formId, true);

		$status = $syncForm['status'] ?? '';
		$message = $syncForm['message'] ?? '';

		unset($syncForm['message']);
		unset($syncForm['status']);

		if ($status === AbstractBaseRoute::STATUS_ERROR) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					$message,
					$syncForm
				)
			);
		}

		return \rest_ensure_response(
			$this->getApiSuccessOutput(
				$message,
				$syncForm
			)
		);
	}
}
