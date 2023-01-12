<?php

/**
 * The class register route for public form submiting endpoint - Moments
 *
 * @package EightshiftForms\Rest\Rout\Integrations\Momentses
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Moments;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Helpers\UploadHelper;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Mailer\MailerInterface;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class FormSubmitMomentsRoute
 */
class FormSubmitMomentsRoute extends AbstractFormSubmit
{
	/**
	 * Use trait Upload_Helper inside class.
	 */
	use UploadHelper;

	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	protected $validator;

	/**
	 * Instance variable of LabelsInterface data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Instance variable for Moments data.
	 *
	 * @var ClientInterface
	 */
	protected $momentsClient;

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
	 * @param LabelsInterface $labels Inject LabelsInterface which holds labels data.
	 * @param ClientInterface $momentsClient Inject Moments which holds Moments connect data.
	 * @param MailerInterface $mailer Inject MailerInterface which holds mailer methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		LabelsInterface $labels,
		ClientInterface $momentsClient,
		MailerInterface $mailer
	) {
		$this->validator = $validator;
		$this->labels = $labels;
		$this->momentsClient = $momentsClient;
		$this->mailer = $mailer;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/form-submit-moments';
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
	public function submitAction(string $formId, array $params = [], $files = [])
	{
		// Send application to Moments.
		$response = $this->momentsClient->postApplication(
			// $this->getSettingsValue(SettingsMoments::SETTINGS_MOMENTS_LIST_KEY, $formId),
			'',
			// TODO
			$params,
			[],
			$formId
		);

		if ($response['status'] === 'error') {
			// Send fallback email.
			$this->mailer->fallbackEmail($response['data'] ?? []);
		}

		// Finish.
		return \rest_ensure_response([
			'code' => $response['code'],
			'status' => $response['status'],
			'message' => $this->labels->getLabel($response['message'], $formId),
		]);
	}
}
