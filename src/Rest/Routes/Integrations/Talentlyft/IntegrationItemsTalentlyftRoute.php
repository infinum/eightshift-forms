<?php

/**
 * The class to provide integration items from cache.
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Talentlyft
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Talentlyft;

use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Talentlyft\SettingsTalentlyft;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Rest\Routes\AbstractUtilsBaseRoute;
use WP_REST_Request;

/**
 * Class IntegrationItemsTalentlyftRoute
 */
class IntegrationItemsTalentlyftRoute extends AbstractUtilsBaseRoute
{
	/**
	 * Instance variable for Talentlyft data.
	 *
	 * @var ClientInterface
	 */
	protected $talentlyftClient;

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsTalentlyft::SETTINGS_TYPE_KEY;

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/' . UtilsConfig::ROUTE_PREFIX_INTEGRATION_ITEMS . '/' . self::ROUTE_SLUG;
	}

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ClientInterface $talentlyftClient Inject HubSpot which holds HubSpot connect data.
	 */
	public function __construct(ClientInterface $talentlyftClient)
	{
		$this->talentlyftClient = $talentlyftClient;
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
		$permission = $this->checkUserPermission(UtilsConfig::CAP_SETTINGS);
		if ($permission) {
			return \rest_ensure_response($permission);
		}

		$debug = [
			'request' => $request,
		];

		// Check if Talentlyft global settings is valid.
		$isGlobalSettingsValid = \apply_filters(SettingsTalentlyft::FILTER_SETTINGS_GLOBAL_NAME, false);

		if (!$isGlobalSettingsValid) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					\esc_html__('Global settings not configured', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$items = $this->talentlyftClient->getItems();

		if (!$items) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					\esc_html__('Items missing', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$items = \array_filter(\array_values(\array_map(
			static function ($item) {
				$id = $item['id'] ?? '';

				if ($id) {
					return [
						'label' => $item['title'] ?? \__('No title', 'eightshift-forms'),
						'value' => $id,
					];
				}
			},
			$items
		)));

		// Finish.
		return \rest_ensure_response(
			UtilsApiHelper::getApiSuccessPublicOutput(
				\esc_html__('Success', 'eightshift-forms'),
				$items,
				$debug
			)
		);
	}
}
