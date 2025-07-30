<?php

/**
 * The class register route for public form submitting endpoint - Mailer
 *
 * @package EightshiftForms\Rest\Route\Integrations\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Mailer;

use EightshiftForms\Exception\ValidationFailedException;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Rest\Routes\AbstractIntegrationFormSubmit;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\ApiHelpers;

/**
 * Class FormSubmitMailerRoute
 */
class FormSubmitMailerRoute extends AbstractIntegrationFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsMailer::SETTINGS_TYPE_KEY;

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
		return true;
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
		$formId = $formDetails[Config::FD_FORM_ID];

		// Located before the sendEmail method so we can utilize common email response tags.
		$successAdditionalData = $this->getIntegrationResponseSuccessOutputAdditionalData($formDetails);

		// Send email.
		$mailerResponse = $this->getFormSubmitMailer()->sendEmails(
			$formDetails,
			$this->getCommonEmailResponseTags(
				\array_merge(
					$successAdditionalData['public'],
					$successAdditionalData['private']
				),
				$formDetails
			)
		);

		$status = $mailerResponse['status'] ?? Config::STATUS_ERROR;
		$label = $mailerResponse['label'] ?? 'mailerErrorEmailSend';
		$debug = $mailerResponse['debug'] ?? [];

		if ($status == Config::STATUS_SUCCESS) {
			// Set validation submit once.
			$this->getValidator()->setValidationSubmitOnce($formId);

			// return \rest_ensure_response(
			// 	ApiHelpers::getApiSuccessPublicOutput(
			// 		$this->getLabels()->getLabel($label, $formId),
			// 		\array_merge(
			// 			$successAdditionalData['public'],
			// 			$successAdditionalData['additional']
			// 		),
			// 		$debug
			// 	)
			// );
		}

		throw new ValidationFailedException(
			$this->getLabels()->getLabel($label, $formId),
			[
				self::RESPONSE_SEND_FALLBACK_KEY => true,
				self::RESPONSE_OUTPUT_KEY => $this->getIntegrationResponseErrorOutputAdditionalData($formDetails),
				self::RESPONSE_INTERNAL_KEY => 'mailerErrorEmailSend',
			]
		);

		return \rest_ensure_response(
			ApiHelpers::getApiErrorPublicOutput(
				$this->getLabels()->getLabel($label, $formId),
				$this->getIntegrationResponseErrorOutputAdditionalData($formDetails),
				$debug
			)
		);
	}
}
