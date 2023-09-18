<?php

/**
 * The enqueue helper specific functionality.
 *
 * @package EightshiftForms\Enqueue
 */

declare(strict_types=1);

namespace EightshiftForms\Enqueue;

use EightshiftForms\Config\Config;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractTestApi;
use EightshiftForms\Rest\Routes\Editor\FormFieldsRoute;
use EightshiftForms\Rest\Routes\Editor\IntegrationEditorCreateRoute;
use EightshiftForms\Rest\Routes\Editor\IntegrationEditorSyncRoute;
use EightshiftForms\Rest\Routes\Editor\Options\GeolocationCountriesRoute;
use EightshiftForms\Rest\Routes\Settings\BulkRoute;
use EightshiftForms\Rest\Routes\Settings\CacheDeleteRoute;
use EightshiftForms\Rest\Routes\Settings\LocationsRoute;
use EightshiftForms\Rest\Routes\Settings\MigrationRoute;
use EightshiftForms\Rest\Routes\Settings\SettingsSubmitRoute;
use EightshiftForms\Rest\Routes\Settings\TransferRoute;
use EightshiftForms\Rest\Routes\SubmitCaptchaRoute;
use EightshiftForms\Rest\Routes\SubmitFilesRoute;
use EightshiftForms\Rest\Routes\SubmitGeolocationRoute;
use EightshiftForms\Rest\Routes\SubmitValidateStepRoute;

/**
 * Trait SharedEnqueue
 */
trait SharedEnqueue
{
	/**
	 * Get enqueue shared inline varables.
	 *
	 * @return array<mixed>
	 */
	public function getEnqueueSharedInlineCommonItems(): array
	{
		$restPrefixProject = Config::getProjectRoutesNamespace() . '/' . Config::getProjectRoutesVersion();

		return [
			'customFormParams' => AbstractBaseRoute::CUSTOM_FORM_PARAMS,
			'customFormDataAttributes' => AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES,

			// Adding routes here, you must provide the same map to state STATE_ROUTES enum.
			'restRoutes' => [
				// Common.
				'prefix' => \get_rest_url(\get_current_blog_id()) . $restPrefixProject,
				'prefixProject' => $restPrefixProject,
				'prefixSubmit' => AbstractBaseRoute::ROUTE_PREFIX_FORM_SUBMIT,
				'prefixTestApi' => AbstractTestApi::ROUTE_PREFIX_TEST_API,
				'files' => SubmitFilesRoute::ROUTE_SLUG,

				// Admin.
				'settings' => SettingsSubmitRoute::ROUTE_SLUG,
				'cacheClear' => CacheDeleteRoute::ROUTE_SLUG,
				'migration' => MigrationRoute::ROUTE_SLUG,
				'transfer' => TransferRoute::ROUTE_SLUG,
				'bulk' => BulkRoute::ROUTE_SLUG,
				'locations' => LocationsRoute::ROUTE_SLUG,

				// Editor.
				'prefixIntegrationItemsInner' => AbstractBaseRoute::ROUTE_PREFIX_INTEGRATION_ITEMS_INNER,
				'prefixIntegrationItems' => AbstractBaseRoute::ROUTE_PREFIX_INTEGRATION_ITEMS,
				'prefixIntegrationEditor' => AbstractBaseRoute::ROUTE_PREFIX_INTEGRATION_EDITOR,
				'integrationsEditorSync' => IntegrationEditorSyncRoute::ROUTE_SLUG,
				'integrationsEditorCreate' => IntegrationEditorCreateRoute::ROUTE_SLUG,
				'formFields' => FormFieldsRoute::ROUTE_SLUG,
				'countriesGeolocation' => GeolocationCountriesRoute::ROUTE_SLUG,

				// Public.
				'captcha' => SubmitCaptchaRoute::ROUTE_SLUG,
				'geolocation' => SubmitGeolocationRoute::ROUTE_SLUG,
				'validationStep' => SubmitValidateStepRoute::ROUTE_SLUG,
			]
		];
	}
}
