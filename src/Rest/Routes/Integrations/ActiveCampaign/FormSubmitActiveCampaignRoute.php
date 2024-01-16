<?php

/**
 * The class register route for public form submiting endpoint - ActiveCampaign
 *
 * @package EightshiftForms\Rest\Routes\Integrations\ActiveCampaign
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\ActiveCampaign;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Integrations\ActiveCampaign\ActiveCampaignClientInterface;
use EightshiftForms\Integrations\ActiveCampaign\SettingsActiveCampaign;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidationPatternsInterface;
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
	 * @param ValidatorInterface $validator Inject validation methods.
	 * @param ValidationPatternsInterface $validationPatterns Inject validation patterns methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param SecurityInterface $security Inject security methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject FormSubmitMailerInterface which holds mailer methods.
	 * @param ActiveCampaignClientInterface $activeCampaignClient Inject ActiveCampaign which holds ActiveCampaign connect data.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		SecurityInterface $security,
		FormSubmitMailerInterface $formSubmitMailer,
		ActiveCampaignClientInterface $activeCampaignClient
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
		$this->labels = $labels;
		$this->captcha = $captcha;
		$this->security = $security;
		$this->formSubmitMailer = $formSubmitMailer;
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
		$formId = $formDetails[UtilsConfig::FD_FORM_ID];
		$params = $formDetails[UtilsConfig::FD_PARAMS];

		// Send application to ActiveCampaign.
		$response = $this->activeCampaignClient->postApplication(
			$formDetails[UtilsConfig::FD_ITEM_ID],
			$params,
			$formDetails[UtilsConfig::FD_FILES],
			$formId
		);

		$formDetails[UtilsConfig::FD_RESPONSE_OUTPUT_DATA] = $response;

		$contactId = $response['contactId'] ?? '';

		// Make an additional requests to the API.
		if ($response['status'] === UtilsConfig::STATUS_SUCCESS && $contactId) {
			// If form has action to save tags.
			$actionTags = $params['actionTags']['value'] ?? '';

			if ($actionTags) {
				$actionTags = \explode(UtilsConfig::DELIMITER, $actionTags);

				// Create API req for each tag.
				foreach ($actionTags as $tag) {
					$this->activeCampaignClient->postTag(
						$tag,
						$contactId
					);
				}
			}

			// If form has action to save list.
			$actionLists = $params['actionLists']['value'] ?? '';

			if ($actionLists) {
				$actionLists = \explode(UtilsConfig::DELIMITER, $actionLists);

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
