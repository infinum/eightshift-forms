<?php

/**
 * The class register route for public form submitting endpoint - ActiveCampaign
 *
 * @package EightshiftForms\Rest\Routes\Integrations\ActiveCampaign
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\ActiveCampaign;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Integrations\ActiveCampaign\ActiveCampaignClientInterface;
use EightshiftForms\Integrations\ActiveCampaign\SettingsActiveCampaign;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractIntegrationFormSubmit;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftForms\Config\Config;
use EightshiftForms\Exception\DisabledIntegrationException;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;

/**
 * Class FormSubmitActiveCampaignRoute
 */
class FormSubmitActiveCampaignRoute extends AbstractIntegrationFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsActiveCampaign::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for ActiveCampaign data.
	 *
	 * @var ActiveCampaignClientInterface
	 */
	private $activeCampaignClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param SecurityInterface $security Inject security methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject formSubmitMailer methods.
	 * @param EnrichmentInterface $enrichment Inject enrichment methods.
	 * @param ActiveCampaignClientInterface $activeCampaignClient Inject ActiveCampaign methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		SecurityInterface $security,
		FormSubmitMailerInterface $formSubmitMailer,
		EnrichmentInterface $enrichment,
		ActiveCampaignClientInterface $activeCampaignClient
	) {
		parent::__construct($validator, $labels, $captcha, $security, $formSubmitMailer, $enrichment);
		$this->activeCampaignClient = $activeCampaignClient;
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
		if (SettingsHelpers::isOptionCheckboxChecked(SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_SKIP_INTEGRATION_KEY, SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_SKIP_INTEGRATION_KEY)) {
			$integrationSuccessResponse = $this->getIntegrationResponseSuccessOutput($formDetails);

			throw new DisabledIntegrationException(
				$integrationSuccessResponse[AbstractBaseRoute::R_MSG],
				$integrationSuccessResponse[AbstractBaseRoute::R_DEBUG],
				$integrationSuccessResponse[AbstractBaseRoute::R_DATA]
			);
		}

		$params = $formDetails[Config::FD_PARAMS];

		// Send application to ActiveCampaign.
		$response = $this->activeCampaignClient->postApplication($formDetails);

		$formDetails[Config::FD_RESPONSE_OUTPUT_DATA] = $response;

		$contactId = $response['contactId'] ?? '';

		// Make an additional requests to the API.
		if ($response['status'] === Config::STATUS_SUCCESS && $contactId) {
			// If form has action to save tags.
			$actionTags = $params['actionTags']['value'] ?? [];

			if ($actionTags) {
				// Create API req for each tag.
				foreach ($actionTags as $tag) {
					$this->activeCampaignClient->postTag(
						$tag,
						$contactId
					);
				}
			}

			// If form has action to save list.
			$actionLists = $params['actionLists']['value'] ?? [];

			if ($actionLists) {
				// Create API req for each list.
				foreach ($actionLists as $list) {
					$this->activeCampaignClient->postList(
						$list,
						$contactId
					);
				}
			}
		}

		// Finish.
		return $this->getIntegrationCommonSubmitAction($formDetails);
	}
}
