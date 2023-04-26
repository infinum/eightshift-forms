<?php

/**
 * The class used to send all emails that is used in multiple integrations.
 *
 * @package EightshiftForms\Rest\Route\Integrations\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Mailer;

use EightshiftForms\Helpers\UploadHelper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Integrations\Mailer\MailerInterface;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Rest\ApiHelper;
use EightshiftForms\Settings\SettingsHelper;

/**
 * Class FormSubmitMailer
 */
class FormSubmitMailer implements FormSubmitMailerInterface
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
	 * Use API helper trait.
	 */
	use ApiHelper;

	/**
	 * Instance variable of LabelsInterface data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Instance variable of MailerInterface data.
	 *
	 * @var MailerInterface
	 */
	public $mailer;

	public function __construct(
		MailerInterface $mailer,
		LabelsInterface $labels
	) {
		$this->mailer = $mailer;
		$this->labels = $labels;
	}

	/**
	 * Send emails method.
	 *
	 * @param array<string, mixed> $formDataRefrerence Form refference got from abstract helper.
	 *
	 * @return array<string, array<mixed>|int|string>
	 */
	public function sendEmails(array $formDataRefrerence): array
	{
		$formId = $formDataRefrerence['formId'];
		$params = $formDataRefrerence['params'];
		$files = $formDataRefrerence['files'];

		// Check if Mailer data is set and valid.
		$isSettingsValid = \apply_filters(SettingsMailer::FILTER_SETTINGS_IS_VALID_NAME, $formId);

		// Bailout if settings are not ok.
		if (!$isSettingsValid) {
			return $this->getApiErrorOutput(
				$this->labels->getLabel('mailerErrorSettingsMissing', $formId),
			);
		}

		// Send email.
		$response = $this->mailer->sendFormEmail(
			$formId,
			$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_TO_KEY, $formId),
			$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_SUBJECT_KEY, $formId),
			$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_TEMPLATE_KEY, $formId),
			$files,
			$params
		);

		// If email fails.
		if (!$response) {
			// Always delete the files from the disk.
			if ($files) {
				$this->deleteFiles($files);
			}

			return $this->getApiErrorOutput(
				$this->labels->getLabel('mailerErrorEmailSend', $formId),
			);
		}

		$this->sendConfirmationEmail($formId, $params, $files);

		// Always delete the files from the disk.
		if ($files) {
			$this->deleteFiles($files);
		}

		// Finish.
		return $this->getApiSuccessOutput(
			$this->labels->getLabel('mailerSuccess', $formId),
		);
	}

	/**
	 * Send fallback email.
	 *
	 * @param array<mixed> $data Data to extract data from.
	 *
	 * @return boolean
	 */
	public function sendFallbackEmail(array $response): bool
	{
		return $this->mailer->fallbackEmail($response);
	}

	/**
	 * Send confirmation email.
	 *
	 * @param string $formId Form ID.
	 * @param array $params Params array.
	 * @param array $files Files array.
	 *
	 * @return boolean
	 */
	private function sendConfirmationEmail(string $formId, array $params, array $files): bool
	{
		// Check if Mailer data is set and valid.
		$isConfirmationValid = \apply_filters(SettingsMailer::FILTER_SETTINGS_IS_VALID_CONFIRMATION_NAME, $formId);

		if (!$isConfirmationValid) {
			return false;
		}

		// Send email.
		$a = $this->mailer->sendFormEmail(
			$formId,
			$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_EMAIL_FIELD_KEY, $formId),
			$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_SENDER_SUBJECT_KEY, $formId),
			$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_SENDER_TEMPLATE_KEY, $formId),
			$files,
			$params
		);



		error_log( print_r( ( $formId ), true ) );
		error_log( print_r( ( $this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_EMAIL_FIELD_KEY, $formId) ), true ) );
		error_log( print_r( ( $this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_SENDER_SUBJECT_KEY, $formId) ), true ) );
		error_log( print_r( ( $this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_SENDER_TEMPLATE_KEY, $formId) ), true ) );
		error_log( print_r( ( $files ), true ) );
		error_log( print_r( ( $params ), true ) );
		

		return $a;
	}
}
