<?php

/**
 * The class register route for public form submitting endpoint - Calculator
 *
 * @package EightshiftForms\Rest\Route\Integrations\Calculator
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Calculator;

use EightshiftForms\Integrations\Calculator\SettingsCalculator;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\ApiHelpers;

/**
 * Class FormSubmitCalculatorRoute
 */
class FormSubmitCalculatorRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsCalculator::SETTINGS_TYPE_KEY;

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

		$debug = [
			'formDetails' => $formDetails,
		];

		// Set validation submit once.
		$this->validator->setValidationSubmitOnce($formId);

		// Located before the sendEmail method so we can utilize common email response tags.
		$successAdditionalData = $this->getIntegrationResponseSuccessOutputAdditionalData($formDetails);

		// Send email.
		$this->getFormSubmitMailer()->sendEmails(
			$formDetails,
			$this->getCommonEmailResponseTags(
				\array_merge(
					$successAdditionalData['public'],
					$successAdditionalData['private']
				),
				$formDetails
			)
		);

		return \rest_ensure_response(
			ApiHelpers::getApiSuccessPublicOutput(
				$this->labels->getLabel('calculatorSuccess', $formId),
				\array_merge(
					$successAdditionalData['public'],
					$successAdditionalData['additional']
				),
				$debug
			)
		);
	}
}
