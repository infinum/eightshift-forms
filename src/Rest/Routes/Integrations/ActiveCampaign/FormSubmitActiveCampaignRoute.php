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
use EightshiftForms\Integrations\Mailer\MailerInterface;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftForms\Config\Config;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Exception\DisabledIntegrationException;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftFormsVendor\EightshiftLibs\Rest\Routes\AbstractRoute;

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
	 * @param SecurityInterface $security Inject security methods.
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param MailerInterface $mailer Inject mailerInterface methods.
	 * @param EnrichmentInterface $enrichment Inject enrichment methods.
	 * @param ActiveCampaignClientInterface $activeCampaignClient Inject ActiveCampaign methods.
	 */
	public function __construct(
		SecurityInterface $security,
		ValidatorInterface $validator,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		MailerInterface $mailer,
		EnrichmentInterface $enrichment,
		ActiveCampaignClientInterface $activeCampaignClient
	) {
		$this->security = $security;
		$this->validator = $validator;
		$this->labels = $labels;
		$this->captcha = $captcha;
		$this->mailer = $mailer;
		$this->enrichment = $enrichment;
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
			Config::FD_ITEM_ID => 'string',
			Config::FD_PARAMS => 'array',
		];
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @throws DisabledIntegrationException If integration is disabled.
	 * @throws BadRequestException If integration is missing config.
	 *
	 * @return mixed
	 */
	protected function submitAction(array $formDetails)
	{
		if (SettingsHelpers::isOptionCheckboxChecked(SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_SKIP_INTEGRATION_KEY, SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_SKIP_INTEGRATION_KEY)) {
			$integrationSuccessResponse = $this->getIntegrationResponseSuccessOutput($formDetails);

			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new DisabledIntegrationException(
				$integrationSuccessResponse[AbstractBaseRoute::R_MSG],
				$integrationSuccessResponse[AbstractBaseRoute::R_DEBUG],
				$integrationSuccessResponse[AbstractBaseRoute::R_DATA]
			);
			// phpcs:enable
		}

		if (!\apply_filters(SettingsActiveCampaign::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->getLabels()->getLabel('activeCampaignMissingConfig'),
				[
					AbstractBaseRoute::R_DEBUG => $formDetails,
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_ACTIVE_CAMPAIGN_MISSING_CONFIG,
				],
			);
			// phpcs:enable
		}

		$params = $formDetails[Config::FD_PARAMS];

		// Send application to ActiveCampaign.
		$response = $this->activeCampaignClient->postApplication($formDetails);

		$formDetails[Config::FD_RESPONSE_OUTPUT_DATA] = $response;

		$contactId = $response['contactId'] ?? '';

		// Make an additional requests to the API.
		if ($response['status'] === AbstractRoute::STATUS_SUCCESS && $contactId) {
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
