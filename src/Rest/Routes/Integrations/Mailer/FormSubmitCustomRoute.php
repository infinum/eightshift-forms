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
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\UtilsHelper;

/**
 * Class FormSubmitCustomRoute
 */
class FormSubmitCustomRoute extends AbstractFormSubmit
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
		$action = $formDetails[Config::FD_ACTION];
		$actionExternal = $formDetails[Config::FD_ACTION_EXTERNAL];

		$debug = [
			'formDetails' => $formDetails,
		];

		// If form action is not set or empty.
		if (!$action) {
			return \rest_ensure_response(
				ApiHelpers::getApiErrorPublicOutput(
					$this->labels->getLabel('customNoAction', $formId),
					[],
					$debug
				)
			);
		}

		$successAdditionalData = $this->getIntegrationResponseSuccessOutputAdditionalData($formDetails);

		if ($actionExternal) {
			// Set validation submit once.
			$this->validator->setValidationSubmitOnce($formId);

			return \rest_ensure_response(
				ApiHelpers::getApiSuccessPublicOutput(
					$this->labels->getLabel('customSuccessRedirect', $formId),
					\array_merge(
						$successAdditionalData['public'],
						$successAdditionalData['additional'],
						[
							UtilsHelper::getStateResponseOutputKey('processExternally') => [
								'type' => 'SUBMIT',
							],
						]
					),
					$debug
				)
			);
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

		$customResponseCode = \wp_remote_retrieve_response_code($customResponse);

		// If custom action request fails we'll return the generic error message.
		if (!$customResponseCode || $customResponseCode > 399) {
			return \rest_ensure_response(
				ApiHelpers::getApiErrorPublicOutput(
					$this->labels->getLabel('customError', $formId),
					$this->getIntegrationResponseErrorOutputAdditionalData($formDetails),
					$debug
				)
			);
		}

		// Set validation submit once.
		$this->validator->setValidationSubmitOnce($formId);

		// Finish.
		return \rest_ensure_response(
			ApiHelpers::getApiSuccessPublicOutput(
				$this->labels->getLabel('customSuccess', $formId),
				\array_merge(
					$successAdditionalData['public'],
					$successAdditionalData['additional']
				),
				$debug
			)
		);
	}
}
