<?php

/**
 * The class register route for public form submitting endpoint - Airtable
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Airtable
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Airtable;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Integrations\Airtable\AirtableClientInterface;
use EightshiftForms\Integrations\Airtable\SettingsAirtable;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Rest\Routes\AbstractIntegrationFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftForms\Config\Config;
use EightshiftForms\Exception\DisabledIntegrationException;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;

/**
 * Class FormSubmitAirtableRoute
 */
class FormSubmitAirtableRoute extends AbstractIntegrationFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsAirtable::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for Airtable data.
	 *
	 * @var AirtableClientInterface
	 */
	protected $airtableClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param SecurityInterface $security Inject security methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject formSubmitMailer methods.
	 * @param EnrichmentInterface $enrichment Inject enrichment methods.
	 * @param AirtableClientInterface $airtableClient Inject airtableClient methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		SecurityInterface $security,
		FormSubmitMailerInterface $formSubmitMailer,
		EnrichmentInterface $enrichment,
		AirtableClientInterface $airtableClient
	) {
		parent::__construct($validator, $labels, $captcha, $security, $formSubmitMailer, $enrichment);
		$this->airtableClient = $airtableClient;
	}

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
			Config::FD_ITEM_ID => 'string',
			Config::FD_INNER_ID => 'string',
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
		if (SettingsHelpers::isOptionCheckboxChecked(SettingsAirtable::SETTINGS_AIRTABLE_SKIP_INTEGRATION_KEY, SettingsAirtable::SETTINGS_AIRTABLE_SKIP_INTEGRATION_KEY)) {
			$integrationSuccessResponse = $this->getIntegrationResponseSuccessOutput($formDetails);

			throw new DisabledIntegrationException(
				$integrationSuccessResponse[AbstractBaseRoute::R_MSG],
				$integrationSuccessResponse[AbstractBaseRoute::R_DEBUG],
				$integrationSuccessResponse[AbstractBaseRoute::R_DATA]
			);
		}

		// Send application to Airtable.
		$response = $this->airtableClient->postApplication($formDetails);

		$formDetails[Config::FD_RESPONSE_OUTPUT_DATA] = $response;

		// Finish.
		return $this->getIntegrationCommonSubmitAction($formDetails);
	}
}
