<?php

/**
 * The class register route for Form Settings endpoint
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\Rest\Routes\AbstractIntegrationFormSubmit;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;

/**
 * Class SettingsSubmitRoute
 */
class SettingsSubmitRoute extends AbstractIntegrationFormSubmit
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
	 * Check if filter params should be checked.
	 *
	 * @return bool
	 */
	protected function shouldCheckFilterParams(): bool
	{
		return false;
	}

	/**
	 * Check if captcha should be checked.
	 *
	 * @return bool
	 */
	protected function shouldCheckCaptcha(): bool
	{
		return false;
	}

	/**
	 * Check if security should be checked.
	 *
	 * @return bool
	 */
	protected function shouldCheckSecurity(): bool
	{
		return false;
	}

	/**
	 * Check if enrichment should be checked.
	 *
	 * @return bool
	 */
	protected function shouldCheckEnrichment(): bool
	{
		return false;
	}

	/**
	 * Check if country should be checked.
	 *
	 * @return bool
	 */
	protected function shouldCheckCountry(): bool
	{
		return false;
	}

	/**
	 * Get mandatory params.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(array $params): array
	{
		switch ($params[Config::FD_TYPE]) {
			case Config::SETTINGS_GLOBAL_TYPE_NAME:
				// case Config::FILE_UPLOAD_ADMIN_TYPE_NAME: // TODO: Add file upload admin.
				return [];
			default:
				return [
					Config::FD_FORM_ID => 'string',
				];
		}
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
		$formId = $formDetails[Config::FD_FORM_ID];
		$params = $formDetails[Config::FD_PARAMS];

		// Remove unnecessary params.
		$params = GeneralHelpers::removeUnnecessaryParamFields($params);

		// If form ID is not set this is considered an global setting.
		// Save all fields in the settings.
		foreach ($params as $key => $value) {
			$fieldValue = $value['value'] ?? '';
			$fieldType = $value['type'] ?? '';

			if ($fieldType === 'checkbox' || $fieldType === 'select' || $fieldType === 'country') {
				$fieldValue = \implode(Config::DELIMITER, $fieldValue);
			}

			// Check if key needs updating or deleting.
			if ($fieldValue) {
				if (!$formId) {
					\update_option($key, $fieldValue);
				} else {
					\update_post_meta((int) $formId, $key, $fieldValue);
				}
			} else {
				if (!$formId) {
					\delete_option($key);
				} else {
					\delete_post_meta((int) $formId, $key);
				}
			}
		}

		return [
			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('settingsSuccess'),

		];
	}
}
