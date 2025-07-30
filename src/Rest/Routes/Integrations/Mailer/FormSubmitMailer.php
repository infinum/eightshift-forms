<?php

/**
 * The class used to send all emails that is used in multiple integrations.
 *
 * @package EightshiftForms\Rest\Route\Integrations\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Mailer;

use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Integrations\Mailer\MailerInterface;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\SettingsHelpers;

/**
 * Class FormSubmitMailer
 */
class FormSubmitMailer implements FormSubmitMailerInterface
{
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

	/**
	 * Instance variable of SecurityInterface data.
	 *
	 * @var SecurityInterface
	 */
	protected $security;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param MailerInterface $mailer Inject MailerInterface which holds mailer methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param SecurityInterface $security Inject security methods.
	 */
	public function __construct(
		MailerInterface $mailer,
		LabelsInterface $labels,
		SecurityInterface $security
	) {
		$this->mailer = $mailer;
		$this->labels = $labels;
		$this->security = $security;
	}

	/**
	 * Send emails method.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 * @param array<string, mixed> $responseTags Response tags.
	 *
	 * @return array<string, array<mixed>|int|string>
	 */
	public function sendEmails(array $formDetails, array $responseTags = []): array
	{
		$formId = $formDetails[Config::FD_FORM_ID];

		$debug = [
			'formDetails' => $formDetails,
		];

		// Check if Mailer data is set and valid.
		$isSettingsValid = \apply_filters(SettingsMailer::FILTER_SETTINGS_IS_VALID_NAME, $formId);

		// Bailout if settings are not ok.
		if (!$isSettingsValid) {
			return [
				'status' => Config::STATUS_ERROR,
				'label' => 'mailerErrorSettingsMissing',
				'debug' => $debug,
			];
		}

		// This data is set here because $formDetails can me modified in the previous filters.
		$params = $formDetails[Config::FD_PARAMS];
		$files = $formDetails[Config::FD_FILES];

		// Send email.
		$response = $this->mailer->sendFormEmail(
			$formId,
			SettingsHelpers::getSettingValue(SettingsMailer::SETTINGS_MAILER_TO_KEY, $formId),
			SettingsHelpers::getSettingValue(SettingsMailer::SETTINGS_MAILER_SUBJECT_KEY, $formId),
			SettingsHelpers::getSettingValue(SettingsMailer::SETTINGS_MAILER_TEMPLATE_KEY, $formId),
			$files,
			$params,
			$responseTags,
			[
				'settings' => SettingsHelpers::getSettingValueGroup(SettingsMailer::SETTINGS_MAILER_TO_ADVANCED_KEY, $formId),
				'shouldAppend' => SettingsHelpers::isSettingCheckboxChecked(SettingsMailer::SETTINGS_MAILER_TO_ADVANCED_APPEND_KEY, SettingsMailer::SETTINGS_MAILER_TO_ADVANCED_APPEND_KEY, $formId),
			]
		);

		// If email fails.
		if (!$response) {
			return [
				'status' => Config::STATUS_ERROR,
				'label' => 'mailerErrorEmailSend',
				'debug' => $debug,
			];
		}

		$this->sendConfirmationEmail($formId, $params, $files, $responseTags);

		return [
			'status' => Config::STATUS_SUCCESS,
			'label' => 'mailerSuccess',
			'debug' => $debug,
		];
	}

	/**
	 * Send troubleshooting email.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 * @param array<string, mixed> $data Data to send in the email.
	 * @param string $subject Email subject.
	 * @param string $body Email body.
	 *
	 * @return boolean
	 */
	public function sendTroubleshootingEmail(array $formDetails, array $data, string $subject = '', string $body = ''): bool
	{
		return $this->mailer->sendTroubleshootingEmail($formDetails, $data, $subject, $body);
	}

	/**
	 * Send fallback email
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 * @param string $customSubject Custom subject for the email.
	 * @param string $customMsg Custom message for the email.
	 * @param array<string, mixed> $customData Custom data for the email.
	 *
	 * @return boolean
	 */
	public function sendFallbackIntegrationEmail(
		array $formDetails,
		string $customSubject = '',
		string $customMsg = '',
		array $customData = []
	): bool {
		$customData['debug'] = [
			'requestIP' => $this->security->getIpAddress('anonymize'),
		];

		return $this->mailer->fallbackIntegrationEmail(
			$formDetails,
			$customSubject,
			$customMsg,
			$customData
		);
	}

	/**
	 * Send fallback email - Processing.
	 * This function is used in AbstractIntegrationFormSubmit for processing validation issues.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 * @param string $customSubject Custom subject for the email.
	 * @param string $customMsg Custom message for the email.
	 * @param array<string, mixed> $customData Custom data for the email.
	 *
	 * @return boolean
	 */
	public function sendFallbackProcessingEmail(
		array $formDetails,
		string $customSubject = '',
		string $customMsg = '',
		array $customData = []
	): bool {
		$customData['debug'] = [
			'requestIP' => $this->security->getIpAddress('anonymize'),
		];

		return $this->mailer->fallbackProcessingEmail(
			$formDetails,
			$customSubject,
			$customMsg,
			$customData
		);
	}

	/**
	 * Send confirmation email.
	 *
	 * @param string $formId Form ID.
	 * @param array<mixed> $params Params array.
	 * @param array<mixed> $files Files array.
	 * @param array<string, mixed> $responseTags Response tags.
	 *
	 * @return boolean
	 */
	private function sendConfirmationEmail(string $formId, array $params, array $files, array $responseTags = []): bool
	{
		// Check if Mailer data is set and valid.
		$isConfirmationValid = \apply_filters(SettingsMailer::FILTER_SETTINGS_IS_VALID_CONFIRMATION_NAME, $formId);

		if (!$isConfirmationValid) {
			return false;
		}

		$senderEmail = FormsHelper::getParamValue(SettingsHelpers::getSettingValue(SettingsMailer::SETTINGS_MAILER_EMAIL_FIELD_KEY, $formId), $params);

		if (!$senderEmail) {
			return false;
		}

		// Send email.
		return $this->mailer->sendFormEmail(
			$formId,
			$senderEmail,
			SettingsHelpers::getSettingValue(SettingsMailer::SETTINGS_MAILER_SENDER_SUBJECT_KEY, $formId),
			SettingsHelpers::getSettingValue(SettingsMailer::SETTINGS_MAILER_SENDER_TEMPLATE_KEY, $formId),
			$files,
			$params,
			$responseTags
		);
	}
}
