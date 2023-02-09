<?php

/**
 * The class register route for public form submiting endpoint - Workable
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Helpers\UploadHelper;
use EightshiftForms\Integrations\Workable\SettingsWorkable;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Mailer\MailerInterface;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class FormSubmitWorkableRoute
 */
class FormSubmitWorkableRoute extends AbstractFormSubmit
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
	 * Instance variable of ClientInterface data.
	 *
	 * @var ClientInterface
	 */
	protected $workableClient;

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
	 * @param ClientInterface $workableClient Inject ClientInterface which holds Workable connect data.
	 * @param MailerInterface $mailer Inject MailerInterface which holds mailer methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		LabelsInterface $labels,
		ClientInterface $workableClient,
		MailerInterface $mailer
	) {
		$this->validator = $validator;
		$this->labels = $labels;
		$this->workableClient = $workableClient;
		$this->mailer = $mailer;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/form-submit-workable';
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

		// Check if Workable data is set and valid.
		$isSettingsValid = \apply_filters(SettingsWorkable::FILTER_SETTINGS_IS_VALID_NAME, $formId);

		// Bailout if settings are not ok.
		if (!$isSettingsValid) {
			return \rest_ensure_response([
				'status' => 'error',
				'code' => 400,
				'message' => $this->labels->getLabel('workableErrorSettingsMissing', $formId),
			]);
		}

		// Send application to Workable.
		$response = $this->workableClient->postApplication(
			$this->getSettingsValue(SettingsWorkable::SETTINGS_WORKABLE_JOB_ID_KEY, $formId),
			$params,
			$files,
			$formId
		);

		if ($response['status'] === 'error') {
			// Send fallback email.
			$this->mailer->fallbackEmail($response['data'] ?? []);
		}

		// Always delete the files from the disk.
		if ($files) {
			$this->deleteFiles($files);
		}

		// Finish.
		return \rest_ensure_response([
			'code' => $response['code'],
			'status' => $response['status'],
			'message' => $this->labels->getLabel($response['message'], $formId),
		]);
	}
}
