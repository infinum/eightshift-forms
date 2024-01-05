<?php

/**
 * The enqueue helper specific functionality.
 *
 * @package EightshiftForms\Enqueue
 */

declare(strict_types=1);

namespace EightshiftForms\Enqueue;

use EightshiftForms\Config\Config;
use EightshiftForms\Rest\Routes\AbstractTestApi;
use EightshiftForms\Rest\Routes\Editor\FormFieldsRoute;
use EightshiftForms\Rest\Routes\Editor\IntegrationEditorCreateRoute;
use EightshiftForms\Rest\Routes\Editor\IntegrationEditorSyncRoute;
use EightshiftForms\Rest\Routes\Editor\Options\GeolocationCountriesRoute;
use EightshiftForms\Rest\Routes\Settings\BulkRoute;
use EightshiftForms\Rest\Routes\Settings\CacheDeleteRoute;
use EightshiftForms\Rest\Routes\Settings\ExportRoute;
use EightshiftForms\Rest\Routes\Settings\LocationsRoute;
use EightshiftForms\Rest\Routes\Settings\MigrationRoute;
use EightshiftForms\Rest\Routes\Settings\SettingsSubmitRoute;
use EightshiftForms\Rest\Routes\Settings\TransferRoute;
use EightshiftForms\Rest\Routes\SubmitCaptchaRoute;
use EightshiftForms\Rest\Routes\SubmitFilesRoute;
use EightshiftForms\Rest\Routes\SubmitGeolocationRoute;
use EightshiftForms\Rest\Routes\SubmitValidateStepRoute;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;

/**
 * Trait SharedEnqueue
 */
trait SharedEnqueue
{
	/**
	 * Get enqueue shared inline varables.
	 *
	 * @param boolean $isPublic If enqueue is public or not.
	 *
	 * @return array<mixed>
	 */
	public function getEnqueueSharedInlineCommonItems(bool $isPublic = true): array
	{
		$restPrefixProject = Config::getProjectRoutesNamespace() . '/' . Config::getProjectRoutesVersion();

		$outputPublic = [
			// Common.
			'prefix' => \get_rest_url(\get_current_blog_id()) . $restPrefixProject,
			'prefixProject' => $restPrefixProject,
			'prefixSubmit' => UtilsConfig::ROUTE_PREFIX_FORM_SUBMIT,
			'prefixTestApi' => AbstractTestApi::ROUTE_PREFIX_TEST_API,
			'files' => SubmitFilesRoute::ROUTE_SLUG,

			// Public.
			'captcha' => SubmitCaptchaRoute::ROUTE_SLUG,
			'geolocation' => SubmitGeolocationRoute::ROUTE_SLUG,
			'validationStep' => SubmitValidateStepRoute::ROUTE_SLUG,
		];

		$outputPrivate = [];

		if (!$isPublic) {
			$outputPrivate = [
				// Admin.
				'settings' => SettingsSubmitRoute::ROUTE_SLUG,
				'cacheClear' => CacheDeleteRoute::ROUTE_SLUG,
				'migration' => MigrationRoute::ROUTE_SLUG,
				'transfer' => TransferRoute::ROUTE_SLUG,
				'bulk' => BulkRoute::ROUTE_SLUG,
				'locations' => LocationsRoute::ROUTE_SLUG,
				'export' => ExportRoute::ROUTE_SLUG,

					// Editor.
				'prefixIntegrationItemsInner' => UtilsConfig::ROUTE_PREFIX_INTEGRATION_ITEMS_INNER,
				'prefixIntegrationItems' => UtilsConfig::ROUTE_PREFIX_INTEGRATION_ITEMS,
				'prefixIntegrationEditor' => UtilsConfig::ROUTE_PREFIX_INTEGRATION_EDITOR,
				'integrationsEditorSync' => IntegrationEditorSyncRoute::ROUTE_SLUG,
				'integrationsEditorCreate' => IntegrationEditorCreateRoute::ROUTE_SLUG,
				'formFields' => FormFieldsRoute::ROUTE_SLUG,
				'countriesGeolocation' => GeolocationCountriesRoute::ROUTE_SLUG,
			];
		}

		return [
			'restRoutes' => \array_merge($outputPublic, $outputPrivate),
		];
	}
}
