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
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Rest\Routes\SubmitForm;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\Validator;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class FormSubmitActiveCampaignRoute
 */
class FormSubmitActiveCampaignRoute extends SubmitForm
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
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 * @param ValidationPatternsInterface $validationPatterns Inject ValidationPatternsInterface which holds validation methods.
	 * @param LabelsInterface $labels Inject LabelsInterface which holds labels data.
	 * @param CaptchaInterface $captcha Inject CaptchaInterface which holds captcha data.
	 * @param SecurityInterface $security Inject SecurityInterface which holds security data.
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
		return '/' . AbstractBaseRoute::ROUTE_PREFIX_FORM_SUBMIT . '/' . self::ROUTE_SLUG;
	}

	/**
	 * Returns validator class.
	 *
	 * @return ValidatorInterface
	 */
	protected function getValidator()
	{
		return $this->validator;
	}

	/**
	 * Returns validator patterns class.
	 *
	 * @return ValidationPatternsInterface
	 */
	protected function getValidatorPatterns()
	{
		return $this->validationPatterns;
	}

	/**
	 * Returns validator labels class.
	 *
	 * @return LabelsInterface
	 */
	protected function getValidatorLabels()
	{
		return $this->labels;
	}

	/**
	 * Returns captcha class.
	 *
	 * @return CaptchaInterface
	 */
	protected function getCaptcha()
	{
		return $this->captcha;
	}

	/**
	 * Returns securicty class.
	 *
	 * @return SecurityInterface
	 */
	protected function getSecurity()
	{
		return $this->security;
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $formDataReference Form reference got from abstract helper.
	 *
	 * @return mixed
	 */
	protected function submitAction(array $formDataReference)
	{
		$itemId = $formDataReference['itemId'];
		$formId = $formDataReference['formId'];
		$params = $formDataReference['params'];
		$files = $formDataReference['files'];

		// Send application to ActiveCampaign.
		$response = $this->activeCampaignClient->postApplication(
			$itemId,
			$params,
			$files,
			$formId
		);

		$contactId = $response['contactId'] ?? '';

		// Make an additional requests to the API.
		if ($response['status'] === AbstractBaseRoute::STATUS_SUCCESS && $contactId) {
			// If form has action to save tags.
			$actionTags = $params['actionTags']['value'] ?? '';

			if ($actionTags) {
				$actionTags = \explode(AbstractBaseRoute::DELIMITER, $actionTags);

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
				$actionLists = \explode(AbstractBaseRoute::DELIMITER, $actionLists);

				// Create API req for each list.
				foreach ($actionLists as $list) {
					$this->activeCampaignClient->postList(
						$list,
						$contactId
					);
				}
			}
		}

		$validation = $response[Validator::VALIDATOR_OUTPUT_KEY] ?? [];
		$disableFallbackEmail = false;

		// Output integrations validation issues.
		if ($validation) {
			$response[Validator::VALIDATOR_OUTPUT_KEY] = $this->validator->getValidationLabelItems($validation, $formId);
			$disableFallbackEmail = true;
		}

		// Skip fallback email if integration is disabled.
		if (!$response['isDisabled'] && $response['status'] === AbstractBaseRoute::STATUS_ERROR) {
			// Prevent fallback email if we have validation errors parsed.
			if (!$disableFallbackEmail) {
				// Send fallback email.
				$this->formSubmitMailer->sendFallbackEmail($response);
			}
		}

		// Send email if it is configured in the backend.
		if ($response['status'] === AbstractBaseRoute::STATUS_SUCCESS) {
			$this->formSubmitMailer->sendEmails($formDataReference);
		}

		$labelsOutput = $this->labels->getLabel($response['message'], $formId);
		$responseOutput = $response;

		// Output fake success and send fallback email.
		if ($response['isDisabled'] && !$validation) {
			$this->formSubmitMailer->sendFallbackEmail($response);

			$fakeResponse = $this->getIntegrationApiSuccessOutput($response);

			$labelsOutput = $this->labels->getLabel($fakeResponse['message'], $formId);
			$responseOutput = $fakeResponse;
		}

		// Finish.
		return \rest_ensure_response(
			$this->getIntegrationApiOutput(
				$responseOutput,
				$labelsOutput,
			)
		);
	}
}
