<?php

/**
 * The class register route for public form submiting endpoint - Greenhouse
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Greenhouse
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Greenhouse;

use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Mailer\MailerInterface;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class FormSubmitGreenhouseRoute
 */
class FormSubmitGreenhouseRoute extends AbstractFormSubmit
{
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
	 * Instance variable of ClientInterface data.
	 *
	 * @var ClientInterface
	 */
	protected $greenhouseClient;

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
	 * @param ClientInterface $greenhouseClient Inject ClientInterface which holds Greenhouse connect data.
	 * @param MailerInterface $mailer Inject MailerInterface which holds mailer methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		LabelsInterface $labels,
		ClientInterface $greenhouseClient,
		MailerInterface $mailer
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
		$this->labels = $labels;
		$this->greenhouseClient = $greenhouseClient;
		$this->mailer = $mailer;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/form-submit-greenhouse';
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
	 * @param string $formId Form ID.
	 * @param array<string, mixed> $params Params array.
	 * @param array<string, array<int, array<string, mixed>>> $files Files array.
	 *
	 * @return mixed
	 */
	protected function submitAction(array $formDataRefrerence)
	{
		// Send application to Greenhouse.
		$response = $this->greenhouseClient->postApplication(
			$formDataRefrerence['itemId'],
			$formDataRefrerence['params'],
			$formDataRefrerence['files'],
			$formDataRefrerence['formId']
		);

		if ($response['status'] === 'error') {
			// Send fallback email.
			$this->mailer->fallbackEmail($response['data'] ?? []);
		}

		// Always delete the files from the disk.
		if ($formDataRefrerence['files']) {
			$this->deleteFiles($formDataRefrerence['files']);
		}

		// Finish.
		return \rest_ensure_response([
			'code' => $response['code'],
			'status' => $response['status'],
			'message' => $this->labels->getLabel($response['message'], $formDataRefrerence['formId']),
		]);
	}
}
