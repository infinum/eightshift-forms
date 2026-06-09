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
use Override;

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
	 */
	#[Override]
	protected function routeGetType(): string
	{
		return self::ROUTE_TYPE_SETTINGS;
	}

	/**
	 * Check if the route is admin protected.
	 */
	protected function isRouteAdminProtected(): bool
	{
		return true;
	}

	/**
	 * Check if filter params should be checked.
	 */
	#[Override]
	protected function shouldCheckFilterParams(): bool
	{
		return false;
	}

	/**
	 * Check if captcha should be checked.
	 */
	#[Override]
	protected function shouldCheckCaptcha(): bool
	{
		return false;
	}

	/**
	 * Check if security should be checked.
	 */
	#[Override]
	protected function shouldCheckSecurity(): bool
	{
		return false;
	}

	/**
	 * Check if enrichment should be checked.
	 */
	#[Override]
	protected function shouldCheckEnrichment(): bool
	{
		return false;
	}

	/**
	 * Check if country should be checked.
	 */
	#[Override]
	protected function shouldCheckCountry(): bool
	{
		return false;
	}

	/**
	 * Get mandatory params.
	 *
	 * @param array<string, mixed> $params Params passed from the request.
	 *
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(array $params): array
	{
		return match ($params[Config::FD_TYPE]) {
			Config::SETTINGS_GLOBAL_TYPE_NAME => [
				Config::FD_TYPE => 'string',
				Config::FD_PARAMS => 'array',
			],
			default => [
				Config::FD_FORM_ID => 'string',
				Config::FD_TYPE => 'string',
				Config::FD_PARAMS => 'array',
			],
		};
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 */
	protected function submitAction(array $formDetails): array
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

			if (\in_array($fieldType, ['checkbox', 'select', 'country'], true)) {
				$fieldValue = \implode(Config::DELIMITER, $fieldValue);
			}

			// Check if key needs updating or deleting.
			if ($fieldValue) {
				if (!$formId) {
					\update_option($key, $fieldValue);
				} else {
					\update_post_meta((int) $formId, $key, $fieldValue);
				}
			} elseif (!$formId) {
				\delete_option($key);
			} else {
				\delete_post_meta((int) $formId, $key);
			}
		}

		return [
			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('settingsSuccess'),

		];
	}
}
