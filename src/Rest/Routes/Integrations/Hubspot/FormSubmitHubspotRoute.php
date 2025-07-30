<?php

/**
 * The class register route for public form submitting endpoint - HubSpot
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Hubspot
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Hubspot;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Integrations\Clearbit\ClearbitClientInterface;
use EightshiftForms\Integrations\Hubspot\HubspotClientInterface;
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
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
 * Class FormSubmitHubspotRoute
 */
class FormSubmitHubspotRoute extends AbstractIntegrationFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsHubspot::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for Hubspot data.
	 *
	 * @var HubspotClientInterface
	 */
	protected $hubspotClient;

	/**
	 * Instance variable for Clearbit data.
	 *
	 * @var ClearbitClientInterface
	 */
	protected $clearbitClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param SecurityInterface $security Inject security methods.
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject formSubmitMailer methods.
	 * @param EnrichmentInterface $enrichment Inject enrichment methods.
	 * @param HubspotClientInterface $hubspotClient Inject hubspotClient methods.
	 * @param ClearbitClientInterface $clearbitClient Inject clearbitClient methods.
	 */
	public function __construct(
		SecurityInterface $security,
		ValidatorInterface $validator,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		FormSubmitMailerInterface $formSubmitMailer,
		EnrichmentInterface $enrichment,
		HubspotClientInterface $hubspotClient,
		ClearbitClientInterface $clearbitClient
	) {
		$this->security = $security;
		$this->validator = $validator;
		$this->labels = $labels;
		$this->captcha = $captcha;
		$this->formSubmitMailer = $formSubmitMailer;
		$this->enrichment = $enrichment;
		$this->hubspotClient = $hubspotClient;
		$this->clearbitClient = $clearbitClient;
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
		if (SettingsHelpers::isOptionCheckboxChecked(SettingsHubspot::SETTINGS_HUBSPOT_SKIP_INTEGRATION_KEY, SettingsHubspot::SETTINGS_HUBSPOT_SKIP_INTEGRATION_KEY)) {
			$integrationSuccessResponse = $this->getIntegrationResponseSuccessOutput($formDetails);

			throw new DisabledIntegrationException(
				$integrationSuccessResponse[AbstractBaseRoute::R_MSG],
				$integrationSuccessResponse[AbstractBaseRoute::R_DEBUG],
				$integrationSuccessResponse[AbstractBaseRoute::R_DATA]
			);
		}

		// Send application to Hubspot.
		$response = $this->hubspotClient->postApplication($formDetails);

		$formDetails[Config::FD_RESPONSE_OUTPUT_DATA] = $response;

		// Finish.
		return $this->getIntegrationCommonSubmitAction($formDetails);
	}

	/**
	 * Call integration response success callback.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 * @param array<string, mixed> $successAdditionalData Data passed from the `getIntegrationResponseSuccessOutputAdditionalData` function.
	 *
	 * @return void
	 */
	protected function callIntegrationResponseSuccessCallback(array $formDetails, array $successAdditionalData): void
	{
		$this->clearbitClient->setQueue(
			$formDetails,
		);
	}
}
