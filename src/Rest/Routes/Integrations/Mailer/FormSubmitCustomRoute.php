<?php

/**
 * The class register route for public form submitting endpoint - custom
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Mailer;

use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Rest\Routes\AbstractIntegrationFormSubmit;
use EightshiftForms\Config\Config;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Troubleshooting\SettingsFallback;

/**
 * Class FormSubmitCustomRoute
 */
class FormSubmitCustomRoute extends AbstractIntegrationFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'custom';

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/' . Config::ROUTE_PREFIX_FORM_SUBMIT . '/' . self::ROUTE_SLUG;
	}

	/**
	 * Check if the route is admin protected.
	 *
	 * @return boolean
	 */
	protected function isRouteAdminProtected(): bool
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
		return [
			Config::FD_FORM_ID => 'string',
			Config::FD_POST_ID => 'string',
		];
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
		$action = $formDetails[Config::FD_ACTION];
		$formId = $formDetails[Config::FD_FORM_ID];
		$params = $formDetails[Config::FD_PARAMS];
		$actionExternal = $formDetails[Config::FD_ACTION_EXTERNAL];

		// If form action is not set or empty.
		if (!$action) {
			throw new BadRequestException(
				$this->labels->getLabel('customNoAction'),
				[
					AbstractBaseRoute::R_DEBUG => $formDetails,
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_CUSTOM_NO_ACTION,
				],
			);
		}

		// NOTE: no need to check if settings are valid, because this check is done in the Mailer class.

		// Located before the sendEmail method so we can utilize common email response tags.
		$successAdditionalData = $this->getIntegrationResponseSuccessOutputAdditionalData($formDetails);

		if ($actionExternal) {
			// Set validation submit once.
			$this->getValidator()->setValidationSubmitOnce($formId);

			return [
				AbstractBaseRoute::R_MSG => $this->labels->getLabel('customSuccessRedirect', $formId),
				AbstractBaseRoute::R_DEBUG => [
					AbstractBaseRoute::R_DEBUG => $formDetails,
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_CUSTOM_SUCCESS_REDIRECT,
				],
				AbstractBaseRoute::R_DATA => \array_merge(
					$successAdditionalData['public'],
					$successAdditionalData['additional'],
					[
						UtilsHelper::getStateResponseOutputKey('processExternally') => [
							'type' => 'SUBMIT',
						],
					]
				),
			];
		}

		// Prepare params for output.
		$params = GeneralHelpers::prepareGenericParamsOutput($params);

		// Create a custom form action request.
		$customResponse = \wp_remote_post(
			$action,
			[
				'headers' => [
					'Content-Type' => 'application/x-www-form-urlencoded',
				],
				'body' => \http_build_query($params),
			]
		);

		$formDetails[Config::FD_RESPONSE_OUTPUT_DATA] = $customResponse;

		if (\is_wp_error($customResponse)) {
			throw new BadRequestException(
				$this->labels->getLabel('customError', $formId),
				[
					AbstractBaseRoute::R_DEBUG => $formDetails,
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_CUSTOM_WP_ERROR,
				],
			);
		}

		$customResponseCode = \wp_remote_retrieve_response_code($customResponse);

		// If custom action request fails we'll return the generic error message.
		if (ApiHelpers::isErrorResponse($customResponseCode)) {
			throw new BadRequestException(
				$this->labels->getLabel('customError', $formId),
				[
					AbstractBaseRoute::R_DEBUG => $formDetails,
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_CUSTOM_ERROR,
				],
				$this->getIntegrationResponseErrorOutputAdditionalData($formDetails),
			);
		}

		// Set validation submit once.
		$this->getValidator()->setValidationSubmitOnce($formId);

		return [
			AbstractBaseRoute::R_MSG => $this->labels->getLabel('customSuccess', $formId),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG => $formDetails,
				AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_CUSTOM_SUCCESS,
			],
			AbstractBaseRoute::R_DATA => \array_merge(
				$successAdditionalData['public'],
				$successAdditionalData['additional']
			),
		];
	}
}
