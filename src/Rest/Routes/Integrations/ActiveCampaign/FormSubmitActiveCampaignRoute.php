<?php

/**
 * The class register route for public form submiting endpoint - ActiveCampaign
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
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;

/**
 * Class FormSubmitActiveCampaignRoute
 */
class FormSubmitActiveCampaignRoute extends AbstractFormSubmit
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
		return '/' . UtilsConfig::ROUTE_PREFIX_FORM_SUBMIT . '/' . self::ROUTE_SLUG;
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
		$params = $formDetails[UtilsConfig::FD_PARAMS];

		// Send application to ActiveCampaign.
		$response = $this->activeCampaignClient->postApplication($formDetails);

		$formDetails[UtilsConfig::FD_RESPONSE_OUTPUT_DATA] = $response;

		$contactId = $response['contactId'] ?? '';

		// Make an additional requests to the API.
		if ($response['status'] === UtilsConfig::STATUS_SUCCESS && $contactId) {
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
		return \rest_ensure_response(
			$this->getIntegrationCommonSubmitAction($formDetails)
		);
	}
}
