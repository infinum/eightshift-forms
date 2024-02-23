<?php

/**
 * The class used to send all emails that is used in multiple integrations.
 *
 * @package EightshiftForms\Rest\Route\Integrations\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Mailer;

use EightshiftForms\Entries\EntriesHelper;
use EightshiftForms\Entries\SettingsEntries;
use EightshiftForms\Hooks\FiltersOuputMock;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Integrations\Mailer\MailerInterface;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsEncryption;
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

		if ($useSuccessAction) {
			// Save entries.
			if (\apply_filters(SettingsEntries::FILTER_SETTINGS_IS_VALID_NAME, $formId)) {
				$entryId = EntriesHelper::setEntryByFormDataRef($formDetails);
				$formDetails[UtilsConfig::FD_ENTRY_ID] = $entryId ? (string) $entryId : '';
			}

			// Pre response filter for success redirect data.
			$filterName = UtilsHooksHelper::getFilterName(['block', 'form', 'preResponseSuccessRedirectData']);
			if (\has_filter($filterName)) {
				$filterDetails = \apply_filters($filterName, [], $formDetails);

				if ($filterDetails) {
					$formDetails[UtilsConfig::FD_SUCCESS_REDIRECT] = UtilsEncryption::encryptor(\wp_json_encode($filterDetails));
				}
			}
		}

		// This data is set here because $formDetails can me modified in the previous filters.
		$params = $formDetails[UtilsConfig::FD_PARAMS];
		$files = $formDetails[UtilsConfig::FD_FILES];

		// Send email.
		$response = $this->mailer->sendFormEmail(
			$formId,
			UtilsSettingsHelper::getSettingValue(SettingsMailer::SETTINGS_MAILER_TO_KEY, $formId),
			UtilsSettingsHelper::getSettingValue(SettingsMailer::SETTINGS_MAILER_SUBJECT_KEY, $formId),
			UtilsSettingsHelper::getSettingValue(SettingsMailer::SETTINGS_MAILER_TEMPLATE_KEY, $formId),
			$files,
			$params,
			$this->prepareEmailResponseTags($formDetails)
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

		// Finish.
		return UtilsApiHelper::getApiSuccessPublicOutput(
			$this->labels->getLabel('mailerSuccess', $formId),
			UtilsApiHelper::getApiPublicAdditionalDataOutput($formDetails),
			$debug
		);
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
		return $this->mailer->fallbackIntegrationEmail(
			$formDetails,
			$customSubject,
			$customMsg,
			$customData
		);
	}

	/**
	 * Send fallback email - Processing.
	 * This function is used in AbstractFormSubmit for processing validation issues.
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

	/**
	 * Prepare all email response tags.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, mixed>
	 */
	private function prepareEmailResponseTags(array $formDetails): array
	{
		$output = [];

		// Output all the response tags.
		$responseTags = $formDetails[UtilsConfig::FD_EMAIL_RESPONSE_TAGS] ?? [];
		if ($responseTags) {
			$output = $responseTags;
		}

		$formType = $formDetails[UtilsConfig::FD_TYPE] ?? '';
		$formId = $formDetails[UtilsConfig::FD_FORM_ID] ?? '';

		// Success redirect.
		$successRedirectUrl = FiltersOuputMock::getSuccessRedirectUrlFilterValue($formType, $formId)['data'] ?? '';
		if ($successRedirectUrl) {
			// Add success redirect data, usualy got from the add-on plugin or filters.
			$successRedirect = $formDetails[UtilsConfig::FD_SUCCESS_REDIRECT] ?? '';
			if ($successRedirect) {
				$successRedirectUrl = \add_query_arg(
					[
						'es-data' => $successRedirect,
					],
					$successRedirectUrl
				);
			}

			// Add variation data, this filter will not take in effect if the success redirect variation isn't set in the block editor.
			$successRedirectVariation = FiltersOuputMock::getSuccessRedirectVariationFilterValue($formType, $formId)['data'] ?? '';
			if ($successRedirectVariation) {
				$successRedirectUrl = \add_query_arg(
					[
						'es-variation' => UtilsEncryption::encryptor($successRedirectVariation),
					],
					$successRedirectUrl
				);
			}

			// Output mailer response tag.
			$output["mailerSuccessRedirectUrl"] = $successRedirectUrl;
		}

		return $output;
	}
}
