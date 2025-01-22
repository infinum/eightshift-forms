<?php

/**
 * The class register route for Form Settings endpoint
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;

/**
 * Class SettingsSubmitRoute
 */
class SettingsSubmitRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'settings';

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
	 * Detect what type of route it is.
	 *
	 * @return string
	 */
	protected function routeGetType(): string
	{
		return self::ROUTE_TYPE_SETTINGS;
	}

	/**
	 * Check if the route is admin protected.
	 *
	 * @return boolean
	 */
	protected function isRouteAdminProtected(): bool
	{
		return true;
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return mixed
	 */
	protected function submitAction(array $formDetails)
	{
		$debug = [
			'formDetails' => $formDetails,
		];
		$formId = $formDetails[UtilsConfig::FD_FORM_ID];
		$params = $formDetails[UtilsConfig::FD_PARAMS];

		// Remove unecesery params.
		$params = UtilsGeneralHelper::removeUneceseryParamFields($params);

		// If form ID is not set this is considered an global setting.
		// Save all fields in the settings.
		foreach ($params as $key => $value) {
			// Check if key needs updating or deleting.
			if ($value['value']) {
				if (!$formId) {
					\update_option($key, $value['value']);
				} else {
					\update_post_meta((int) $formId, $key, $value['value']);
				}
			} else {
				if (!$formId) {
					\delete_option($key);
				} else {
					\delete_post_meta((int) $formId, $key);
				}
			}
		}

		// Finish.
		return \rest_ensure_response(
			UtilsApiHelper::getApiSuccessPublicOutput(
				\esc_html__('Changes saved!', 'eightshift-forms'),
				[],
				$debug
			)
		);
	}
}
