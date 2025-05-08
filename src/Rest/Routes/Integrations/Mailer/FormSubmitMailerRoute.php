<?php

/**
 * The class register route for public form submiting endpoint - Mailer
 *
 * @package EightshiftForms\Rest\Route\Integrations\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Mailer;

use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\ApiHelpers;

/**
 * Class FormSubmitMailerRoute
 */
class FormSubmitMailerRoute extends AbstractFormSubmit
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

		if ($status === Config::STATUS_SUCCESS) {
			// Set validation submit once.
			$this->validator->setValidationSubmitOnce($formId);

			return \rest_ensure_response(
				ApiHelpers::getApiSuccessPublicOutput(
					$this->labels->getLabel($label, $formId),
					\array_merge(
						$successAdditionalData['public'],
						$successAdditionalData['additional']
					),
					$debug
				)
			);
		}

		return \rest_ensure_response(
			ApiHelpers::getApiErrorPublicOutput(
				$this->labels->getLabel($label, $formId),
				$this->getIntegrationResponseErrorOutputAdditionalData($formDetails),
				$debug
			)
		);
	}
}
