<?php

/**
 * The class register route for public form submiting endpoint - Mailer
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Helpers\UploadHelper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Mailer\MailerInterface;
use EightshiftForms\Mailer\SettingsMailer;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class FormSubmitMailerRoute
 */
class FormSubmitMailerRoute extends AbstractFormSubmit
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
	public $validator;

	/**
	 * Instance variable of MailerInterface data.
	 *
	 * @var MailerInterface
	 */
	public $mailer;

	/**
	 * Instance variable of LabelsInterface data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 * @param MailerInterface $mailer Inject MailerInterface which holds mailer methods.
	 * @param LabelsInterface $labels Inject LabelsInterface which holds labels data.
	 */
	public function __construct(
		ValidatorInterface $validator,
		MailerInterface $mailer,
		LabelsInterface $labels
	) {
		$this->validator = $validator;
		$this->mailer = $mailer;
		$this->labels = $labels;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/form-submit-mailer';
	}

	/**
	 * Implement submit action.
	 *
	 * @param string $formId Form ID.
	 * @param array<string, mixed> $params Params array.
	 * @param array<string, mixed> $files Files array.
	 *
	 * @return mixed
	 */
	public function submitAction(string $formId, array $params = [], $files = [])
	{
		$isUsed = (bool) $this->isCheckboxSettingsChecked(SettingsMailer::SETTINGS_MAILER_USE_KEY, SettingsMailer::SETTINGS_MAILER_USE_KEY, $formId);

		// If Mailer system is not used just respond with success.
		if (!$isUsed) {
			return \rest_ensure_response([
				'status' => 'success',
				'code' => 200,
				'message' => $this->labels->getLabel('mailerSuccessNoSend', $formId),
			]);
		}

		// Check if Mailer data is set and valid.
		$isSettingsValid = \apply_filters(SettingsMailer::FILTER_SETTINGS_IS_VALID_NAME, $formId);

		// Bailout if settings are not ok.
		if (!$isSettingsValid) {
			return \rest_ensure_response([
				'status' => 'error',
				'code' => 400,
				'message' => $this->labels->getLabel('mailerErrorSettingsMissing', $formId),
			]);
		}

		// Send email.
		$mailer = $this->mailer->sendFormEmail(
			$formId,
			$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_TO_KEY, $formId),
			$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_SUBJECT_KEY, $formId),
			$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_TEMPLATE_KEY, $formId),
			$files,
			$params
		);

		error_log( print_r( ( $files ), true ) );
		

		// If email fails.
		if (!$mailer) {
			return \rest_ensure_response([
				'status' => 'error',
				'code' => 400,
				'message' => $this->labels->getLabel('mailerErrorEmailSend', $formId),
			]);
		}

		// Find Sender Details.
		$senderDetails = $this->getSenderDetails($params);

		if (isset($senderDetails['sender-email'])) {
			// Send email.
			$mailerConfirmation = $this->mailer->sendFormEmail(
				$formId,
				$senderDetails['sender-email'],
				$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_SENDER_SUBJECT_KEY, $formId),
				$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_SENDER_TEMPLATE_KEY, $formId),
				$files,
				$params
			);

			// If email fails.
			if (!$mailerConfirmation) {
				return \rest_ensure_response([
					'status' => 'error',
					'code' => 400,
					'message' => $this->labels->getLabel('mailerErrorEmailConfirmationSend', $formId),
				]);
			}
		}

		// If email success.
		return \rest_ensure_response([
			'status' => 'success',
			'code' => 200,
			'message' => $this->labels->getLabel('mailerSuccess', $formId),
		]);
	}
}
