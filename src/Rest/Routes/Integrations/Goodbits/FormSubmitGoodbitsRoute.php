<?php

/**
 * The class register route for public form submiting endpoint - Goodbits
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Goodbits
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Goodbits;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Goodbits\SettingsGoodbits;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\Validator;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class FormSubmitGoodbitsRoute
 */
class FormSubmitGoodbitsRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsGoodbits::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	protected $validator;

	/**
	 * Instance variable of ValidationPatternsInterface data.
	 *
	 * @var ValidationPatternsInterface
	 */
	protected $validationPatterns;

	/**
	 * Instance variable of LabelsInterface data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Instance variable for Goodbits data.
	 *
	 * @var ClientInterface
	 */
	protected $goodbitsClient;

	/**
	 * Instance variable of FormSubmitMailerInterface data.
	 *
	 * @var FormSubmitMailerInterface
	 */
	public $formSubmitMailer;

	/**
	 * Instance variable of CaptchaInterface data.
	 *
	 * @var CaptchaInterface
	 */
	protected $captcha;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 * @param ValidationPatternsInterface $validationPatterns Inject ValidationPatternsInterface which holds validation methods.
	 * @param LabelsInterface $labels Inject LabelsInterface which holds labels data.
	 * @param ClientInterface $goodbitsClient Inject Goodbits which holds Goodbits connect data.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject FormSubmitMailerInterface which holds mailer methods.
	 * @param CaptchaInterface $captcha Inject CaptchaInterface which holds captcha data.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		LabelsInterface $labels,
		ClientInterface $goodbitsClient,
		FormSubmitMailerInterface $formSubmitMailer,
		CaptchaInterface $captcha
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
		$this->labels = $labels;
		$this->goodbitsClient = $goodbitsClient;
		$this->formSubmitMailer = $formSubmitMailer;
		$this->captcha = $captcha;
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

		// Send application to Goodbits.
		$response = $this->goodbitsClient->postApplication(
			$itemId,
			$params,
			$files,
			$formId
		);

		$validation = $response[Validator::VALIDATOR_OUTPUT_KEY] ?? [];

		// Output integrations validation issues.
		if ($validation) {
			$response[Validator::VALIDATOR_OUTPUT_KEY] = $this->validator->getValidationLabelItems($validation, $formId);
		}

		// Skip fallback email if integration is disabled.
		if (!$response['isDisabled'] && $response['status'] === AbstractBaseRoute::STATUS_ERROR) {
			// Send fallback email.
			$this->formSubmitMailer->sendFallbackEmail($response);
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
