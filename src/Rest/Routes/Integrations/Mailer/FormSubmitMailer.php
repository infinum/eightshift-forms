<?php

/**
 * The class used to send all emails that is used in multiple integrations.
 *
 * @package EightshiftForms\Rest\Route\Integrations\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Mailer;

use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Integrations\Mailer\MailerInterface;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;

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
	 * Create a new instance that injects classes
	 *
	 * @param MailerInterface $mailer Inject MailerInterface which holds mailer methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 */
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
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 * @param boolean $useSuccessAction If success action should be used.
	 *
	 * @return array<string, array<mixed>|int|string>
	 */
	public function sendEmails(array $formDetails, bool $useSuccessAction = false): array
	{
		$formId = $formDetails[UtilsConfig::FD_FORM_ID];
		$params = $formDetails[UtilsConfig::FD_PARAMS];
		$files = $formDetails[UtilsConfig::FD_FILES];
		$responseTags = $formDetails[UtilsConfig::FD_EMAIL_RESPONSE_TAGS] ?? [];

		$debug = [
			'formDetails' => $formDetails,
		];

		// Check if Mailer data is set and valid.
		$isSettingsValid = \apply_filters(SettingsMailer::FILTER_SETTINGS_IS_VALID_NAME, $formId);

		// Bailout if settings are not ok.
		if (!$isSettingsValid) {
			return UtilsApiHelper::getApiErrorPublicOutput(
				$this->labels->getLabel('mailerErrorSettingsMissing', $formId),
				[],
				$debug
			);
		}

		// Send email.
		$response = $this->mailer->sendFormEmail(
			$formId,
			UtilsSettingsHelper::getSettingValue(SettingsMailer::SETTINGS_MAILER_TO_KEY, $formId),
			UtilsSettingsHelper::getSettingValue(SettingsMailer::SETTINGS_MAILER_SUBJECT_KEY, $formId),
			UtilsSettingsHelper::getSettingValue(SettingsMailer::SETTINGS_MAILER_TEMPLATE_KEY, $formId),
			$files,
			$params,
			$responseTags
		);

		// If email fails.
		if (!$response) {
			return UtilsApiHelper::getApiErrorPublicOutput(
				$this->labels->getLabel('mailerErrorEmailSend', $formId),
				[],
				$debug
			);
		}

		$this->sendConfirmationEmail($formId, $params, $files);

		if ($useSuccessAction) {
			$actionName = UtilsHooksHelper::getActionName(['entries', 'saveEntry']);
			if (\has_action($actionName)) {
				\do_action($actionName, $formDetails);
			}
		}

		// Finish.
		return UtilsApiHelper::getApiSuccessPublicOutput(
			$this->labels->getLabel('mailerSuccess', $formId),
			UtilsApiHelper::getApiPublicAdditionalDataOutput($formDetails),
			$debug
		);
	}

	/**
	 * Send fallback email.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return boolean
	 */
	public function sendfallbackIntegrationEmail(array $formDetails): bool
	{
		return $this->mailer->fallbackIntegrationEmail($formDetails);
	}

	/**
	 * Send confirmation email.
	 *
	 * @param string $formId Form ID.
	 * @param array<mixed> $params Params array.
	 * @param array<mixed> $files Files array.
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

		$senderEmail = $params[UtilsSettingsHelper::getSettingValue(SettingsMailer::SETTINGS_MAILER_EMAIL_FIELD_KEY, $formId)]['value'] ?? '';

		if (!$senderEmail) {
			return false;
		}

		// Send email.
		return $this->mailer->sendFormEmail(
			$formId,
			$senderEmail,
			UtilsSettingsHelper::getSettingValue(SettingsMailer::SETTINGS_MAILER_SENDER_SUBJECT_KEY, $formId),
			UtilsSettingsHelper::getSettingValue(SettingsMailer::SETTINGS_MAILER_SENDER_TEMPLATE_KEY, $formId),
			$files,
			$params
		);
	}
}
