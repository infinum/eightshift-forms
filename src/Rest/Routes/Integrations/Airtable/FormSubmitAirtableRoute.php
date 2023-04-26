<?php

/**
 * The class register route for public form submiting endpoint - Airtable
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Airtable
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Airtable;

use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class FormSubmitAirtableRoute
 */
class FormSubmitAirtableRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/' . AbstractBaseRoute::ROUTE_PREFIX_FORM_SUBMIT . '-airtable/';

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
	 * Instance variable for Airtable data.
	 *
	 * @var ClientInterface
	 */
	protected $airtableClient;

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
	 * @param ClientInterface $airtableClient Inject Airtable which holds Airtable connect data.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject FormSubmitMailerInterface which holds mailer methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		LabelsInterface $labels,
		ClientInterface $airtableClient,
		FormSubmitMailerInterface $formSubmitMailer
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
		$this->labels = $labels;
		$this->airtableClient = $airtableClient;
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
		$innerId = $formDataRefrerence['innerId'];
		$formId = $formDataRefrerence['formId'];
		$params = $formDataRefrerence['params'];
		$files = $formDataRefrerence['files'];

		// Send application to Airtable.
		$delimiter = AbstractBaseRoute::DELIMITER;

		$response = $this->airtableClient->postApplication(
			"{$itemId}{$delimiter}{$innerId}",
			$params,
			$files,
			$formId
		);

		if ($response['status'] === AbstractBaseRoute::STATUS_ERROR) {
			// Send fallback email.
			$this->formSubmitMailer->sendFallbackEmail($response);
		}

		// Send email if it is configured in the backend.
		$this->formSubmitMailer->sendEmails($formDataRefrerence);

		// Finish.
		return \rest_ensure_response(
			$this->getIntegrationApiOutput(
				$response,
				$this->labels->getLabel($response['message'], $formId)
			)
		);
	}
}
