<?php

/**
 * The class register route for public form submiting endpoint - Mailer
 *
 * @package EightshiftForms\Rest\Route\Integrations\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Mailer;

use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Integrations\Mailer\MailerInterface;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class FormSubmitMailerRoute
 */
class FormSubmitMailerRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/' . AbstractBaseRoute::ROUTE_PREFIX_FORM_SUBMIT . '-mailer/';

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
	 * @param ValidationPatternsInterface $validationPatterns Inject ValidationPatternsInterface which holds validation methods.
	 * @param MailerInterface $mailer Inject MailerInterface which holds mailer methods.
	 * @param LabelsInterface $labels Inject LabelsInterface which holds labels data.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		MailerInterface $mailer,
		LabelsInterface $labels
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
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
		$formId = $formDataRefrerence['formId'];
		$params = $formDataRefrerence['params'];
		$files = $formDataRefrerence['files'];
		$senderEmail = $formDataRefrerence['senderEmail'];

		// Check if Mailer data is set and valid.
		$isSettingsValid = \apply_filters(SettingsMailer::FILTER_SETTINGS_IS_VALID_NAME, $formId);

		// Bailout if settings are not ok.
		if (!$isSettingsValid) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					$this->labels->getLabel('mailerErrorSettingsMissing', $formId),
				)
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

			return \rest_ensure_response(
				$this->getApiErrorOutput(
					$this->labels->getLabel('mailerErrorEmailSend', $formId),
				)
			);
		}

		// Check if Mailer data is set and valid.
		$isConfirmationValid = \apply_filters(SettingsMailer::FILTER_SETTINGS_IS_VALID_CONFIRMATION_NAME, $formId);

		if ($isConfirmationValid && $senderEmail) {
			// Send email.
			$mailerConfirmation = $this->mailer->sendFormEmail(
				$formId,
				$senderEmail,
				$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_SENDER_SUBJECT_KEY, $formId),
				$this->getSettingsValue(SettingsMailer::SETTINGS_MAILER_SENDER_TEMPLATE_KEY, $formId),
				$files,
				$params
			);

			// If email fails.
			if (!$mailerConfirmation) {
				// Always delete the files from the disk.
				if ($files) {
					$this->deleteFiles($files);
				}

				return \rest_ensure_response(
					$this->getApiErrorOutput(
						$this->labels->getLabel('mailerErrorEmailConfirmationSend', $formId),
					)
				);
			}
		}

		// Always delete the files from the disk.
		if ($files) {
			$this->deleteFiles($files);
		}

		// Finish.
		return \rest_ensure_response(
			$this->getApiSuccessOutput(
				$this->labels->getLabel('mailerSuccess', $formId),
			)
		);
	}
}
