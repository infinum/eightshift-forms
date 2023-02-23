<?php

/**
 * The class register route for public form submiting endpoint - Mailerlite
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Mailerlite
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Mailerlite;

use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Integrations\Mailer\MailerInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\Validator;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class FormSubmitMailerliteRoute
 */
class FormSubmitMailerliteRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/' . AbstractBaseRoute::ROUTE_PREFIX_FORM_SUBMIT . '-mailerlite/';

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
	 * Instance variable for Mailerlite data.
	 *
	 * @var ClientInterface
	 */
	protected $mailerliteClient;

	/**
	 * Instance variable of MailerInterface data.
	 *
	 * @var MailerInterface
	 */
	public $mailer;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 * @param ValidationPatternsInterface $validationPatterns Inject ValidationPatternsInterface which holds validation methods.
	 * @param LabelsInterface $labels Inject LabelsInterface which holds labels data.
	 * @param ClientInterface $mailerliteClient Inject Mailerlite which holds Mailerlite connect data.
	 * @param MailerInterface $mailer Inject MailerInterface which holds mailer methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		LabelsInterface $labels,
		ClientInterface $mailerliteClient,
		MailerInterface $mailer
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
		$this->labels = $labels;
		$this->mailerliteClient = $mailerliteClient;
		$this->mailer = $mailer;
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

		// Send application to Mailerlite.
		$response = $this->mailerliteClient->postApplication(
			$itemId,
			$params,
			$files,
			$formId
		);

		// Output integrations validation issues.
		if (isset($response[Validator::VALIDATOR_OUTPUT_KEY])) {
			$response[Validator::VALIDATOR_OUTPUT_KEY] = $this->validator->getValidationLabelItems($response[Validator::VALIDATOR_OUTPUT_KEY], $formId);
		}

		if ($response['status'] === AbstractBaseRoute::STATUS_ERROR) {
			// Send fallback email.
			$this->mailer->fallbackEmail($response);
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
