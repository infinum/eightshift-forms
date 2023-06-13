<?php

/**
 * The class register route for public form submiting endpoint - mailchimp
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Mailchimp
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Mailchimp;

use EightshiftForms\Integrations\Mailchimp\MailchimpClientInterface;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\Validator;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class FormSubmitMailchimpRoute
 */
class FormSubmitMailchimpRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/' . AbstractBaseRoute::ROUTE_PREFIX_FORM_SUBMIT . '-mailchimp/';

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
	 * Instance variable for Mailchimp data.
	 *
	 * @var MailchimpClientInterface
	 */
	protected $mailchimpClient;

	/**
	 * Instance variable of FormSubmitMailerInterface data.
	 *
	 * @var FormSubmitMailerInterface
	 */
	public $formSubmitMailer;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 * @param ValidationPatternsInterface $validationPatterns Inject ValidationPatternsInterface which holds validation methods.
	 * @param LabelsInterface $labels Inject LabelsInterface which holds labels data.
	 * @param MailchimpClientInterface $mailchimpClient Inject Mailchimp which holds Mailchimp connect data.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject FormSubmitMailerInterface which holds mailer methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		LabelsInterface $labels,
		MailchimpClientInterface $mailchimpClient,
		FormSubmitMailerInterface $formSubmitMailer
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
		$this->labels = $labels;
		$this->mailchimpClient = $mailchimpClient;
		$this->formSubmitMailer = $formSubmitMailer;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return self::ROUTE_SLUG;
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
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $formDataRefrerence Form refference got from abstract helper.
	 *
	 * @return mixed
	 */
	protected function submitAction(array $formDataRefrerence)
	{
		$itemId = $formDataRefrerence['itemId'];
		$formId = $formDataRefrerence['formId'];
		$params = $formDataRefrerence['params'];
		$files = $formDataRefrerence['files'];

		// Send application to Mailchimp.
		$response = $this->mailchimpClient->postApplication(
			$itemId,
			$params,
			$files,
			$formId
		);

		// Output integrations validation issues.
		if (isset($response[Validator::VALIDATOR_OUTPUT_KEY])) {
			$response[Validator::VALIDATOR_OUTPUT_KEY] = $this->validator->getValidationLabelItems($response[Validator::VALIDATOR_OUTPUT_KEY], $formId);
		}

		// Skip fallback email if integration is disabled.
		if (!$response['isDisabled'] && $response['status'] === AbstractBaseRoute::STATUS_ERROR) {
			// Send fallback email.
			$this->formSubmitMailer->sendFallbackEmail($response);
		}

		// Send email if it is configured in the backend.
		if ($response['status'] === AbstractBaseRoute::STATUS_SUCCESS) {
			$this->formSubmitMailer->sendEmails($formDataRefrerence);
		}

		// Output fake success and send fallback email.
		if ($response['isDisabled'] && !$response[Validator::VALIDATOR_OUTPUT_KEY] ?? []) {
			$this->formSubmitMailer->sendFallbackEmail($response);

			$fakeResponse = $this->getIntegrationApiSuccessOutput($response);

			return \rest_ensure_response(
				$this->getIntegrationApiOutput(
					$fakeResponse,
					$this->labels->getLabel($fakeResponse['message'], $formId),
				)
			);
		}

		// Finish.
		return \rest_ensure_response(
			$this->getIntegrationApiOutput(
				$response,
				$this->labels->getLabel($response['message'], $formId),
				[
					Validator::VALIDATOR_OUTPUT_KEY
				]
			)
		);
	}
}
