<?php

/**
 * The enqueue helper specific functionality.
 *
 * @package EightshiftForms\Enqueue
 */

declare(strict_types=1);

namespace EightshiftForms\Enqueue;

use EightshiftForms\Rest\Routes\Editor\FormFieldsRoute;
use EightshiftForms\Rest\Routes\Editor\IntegrationEditorCreateRoute;
use EightshiftForms\Rest\Routes\Editor\IntegrationEditorSyncRoute;
use EightshiftForms\Rest\Routes\Editor\Options\GeolocationCountriesRoute;
use EightshiftForms\Rest\Routes\Settings\BulkRoute;
use EightshiftForms\Rest\Routes\Settings\CacheDeleteRoute;
use EightshiftForms\Rest\Routes\Settings\IncrementRoute;
use EightshiftForms\Rest\Routes\Settings\DebugEncryptRoute;
use EightshiftForms\Rest\Routes\Settings\ExportRoute;
use EightshiftForms\Rest\Routes\Settings\LocationsRoute;
use EightshiftForms\Rest\Routes\Settings\MigrationRoute;
use EightshiftForms\Rest\Routes\Settings\SettingsSubmitRoute;
use EightshiftForms\Rest\Routes\Settings\TransferRoute;
use EightshiftForms\Rest\Routes\SubmitCaptchaRoute;
use EightshiftForms\Rest\Routes\SubmitFilesRoute;
use EightshiftForms\Rest\Routes\SubmitGeolocationRoute;
use EightshiftForms\Rest\Routes\SubmitValidateStepRoute;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\HooksHelpers;

/**
 * Trait SharedEnqueue
 */
trait SharedEnqueue
{
	/**
	 * Get enqueue shared inline variables.
	 *
	 * @param boolean $isPublic If enqueue is public or not.
	 *
	 * @return array<mixed>
	 */
	public function getEnqueueSharedInlineCommonItems(bool $isPublic = true): array
	{
		$restPrefixProject = Config::ROUTE_NAMESPACE . '/' . Config::ROUTE_VERSION;

		$outputPublicFilter = [];
		$outputPrivateFilter = [];

		$outputPublic = [
			// Common.
			'prefix' => \get_rest_url(\get_current_blog_id()) . $restPrefixProject,
			'prefixProject' => $restPrefixProject,
			'prefixSubmit' => Config::ROUTE_PREFIX_FORM_SUBMIT,
			'prefixTestApi' => Config::ROUTE_PREFIX_TEST_API,
			'files' => SubmitFilesRoute::ROUTE_SLUG,

			// Public.
			'captcha' => SubmitCaptchaRoute::ROUTE_SLUG,
			'geolocation' => SubmitGeolocationRoute::ROUTE_SLUG,
			'validationStep' => SubmitValidateStepRoute::ROUTE_SLUG,
		];

		// Public routes filter.
		$filterName = HooksHelpers::getFilterName(['scripts', 'routes', 'public']);
		if (\has_filter($filterName)) {
			$outputPublicFilter = \apply_filters($filterName, []);
		}

		$outputPrivate = [];

		if (!$isPublic) {
			$outputPrivate = [
				// Admin.
				'settings' => SettingsSubmitRoute::ROUTE_SLUG,
				'increment' => IncrementRoute::ROUTE_SLUG,
				'cacheClear' => CacheDeleteRoute::ROUTE_SLUG,
				'migration' => MigrationRoute::ROUTE_SLUG,
				'transfer' => TransferRoute::ROUTE_SLUG,
				'bulk' => BulkRoute::ROUTE_SLUG,
				'locations' => LocationsRoute::ROUTE_SLUG,
				'export' => ExportRoute::ROUTE_SLUG,
				'debugEncrypt' => DebugEncryptRoute::ROUTE_SLUG,

				// Editor.
				'prefixIntegrationItemsInner' => Config::ROUTE_PREFIX_INTEGRATION_ITEMS_INNER,
				'prefixIntegrationItems' => Config::ROUTE_PREFIX_INTEGRATION_ITEMS,
				'prefixIntegrationEditor' => Config::ROUTE_PREFIX_INTEGRATION_EDITOR,
				'integrationsEditorSync' => IntegrationEditorSyncRoute::ROUTE_SLUG,
				'integrationsEditorCreate' => IntegrationEditorCreateRoute::ROUTE_SLUG,
				'formFields' => FormFieldsRoute::ROUTE_SLUG,
				'countriesGeolocation' => GeolocationCountriesRoute::ROUTE_SLUG,
			];

			// Private routes filter.
			$filterName = HooksHelpers::getFilterName(['scripts', 'routes', 'private']);
			if (\has_filter($filterName)) {
				$outputPrivateFilter = \apply_filters($filterName, []);
			}
		}

		return [
			'restRoutes' => \array_merge(
				$outputPublic,
				$outputPrivate,
				$outputPublicFilter,
				$outputPrivateFilter
			),
		];
	}
}
