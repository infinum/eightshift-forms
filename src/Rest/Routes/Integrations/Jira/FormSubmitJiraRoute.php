<?php

/**
 * The class register route for public form submiting endpoint - Jira
 *
 * @package EightshiftForms\Rest\Route\Integrations\Jira
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Jira;

use EightshiftForms\Integrations\Jira\JiraClientInterface;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Integrations\Jira\SettingsJira;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\Validator;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class FormSubmitJiraRoute
 */
class FormSubmitJiraRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/' . AbstractBaseRoute::ROUTE_PREFIX_FORM_SUBMIT . '-jira/';

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
	 * Instance variable for Jira data.
	 *
	 * @var JiraClientInterface
	 */
	protected $jiraClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 * @param ValidationPatternsInterface $validationPatterns Inject ValidationPatternsInterface which holds validation methods.
	 * @param LabelsInterface $labels Inject LabelsInterface which holds labels data.
	 * @param JiraClientInterface $jiraClient Inject Jira which holds Jira connect data.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		LabelsInterface $labels,
		JiraClientInterface $jiraClient
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
		$this->labels = $labels;
		$this->jiraClient = $jiraClient;
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

		// Send application to Hubspot.
		$response = $this->jiraClient->postIssue(
			$params,
			$formId
		);

		error_log( print_r( ( $response ), true ) );
		
		if ($response['status'] === AbstractBaseRoute::STATUS_ERROR) {
			// Send fallback email.
			// $this->mailer->fallbackEmail($response);
		}

		// Finish.
		return \rest_ensure_response(
			$this->getIntegrationApiOutput(
				$response,
				$this->labels->getLabel($response['message'], $formId),
				[
					Validator::VALIDATOR_OUTPUT_KEY
				]
			)
		);
		

	// 	$files = $formDataRefrerence['files'];
	// 	$senderEmail = $formDataRefrerence['senderEmail'];

	// 	// Check if Jira data is set and valid.
	// 	$isSettingsValid = \apply_filters(SettingsJira::FILTER_SETTINGS_IS_VALID_NAME, $formId);

	// 	// Bailout if settings are not ok.
	// 	if (!$isSettingsValid) {
	// 		return \rest_ensure_response(
	// 			$this->getApiErrorOutput(
	// 				$this->labels->getLabel('jiraErrorSettingsMissing', $formId),
	// 			)
	// 		);
	// 	}

	// 	// Send email.
	// 	$response = $this->jira->sendFormEmail(
	// 		$formId,
	// 		$this->getSettingsValue(SettingsJira::SETTINGS_JIRA_TO_KEY, $formId),
	// 		$this->getSettingsValue(SettingsJira::SETTINGS_JIRA_SUBJECT_KEY, $formId),
	// 		$this->getSettingsValue(SettingsJira::SETTINGS_JIRA_TEMPLATE_KEY, $formId),
	// 		$files,
	// 		$params
	// 	);

	// 	// If email fails.
	// 	if (!$response) {
	// 		// Always delete the files from the disk.
	// 		if ($files) {
	// 			$this->deleteFiles($files);
	// 		}

	// 		return \rest_ensure_response(
	// 			$this->getApiErrorOutput(
	// 				$this->labels->getLabel('jiraErrorEmailSend', $formId),
	// 			)
	// 		);
	// 	}

	// 	// Check if Jira data is set and valid.
	// 	$isConfirmationValid = \apply_filters(SettingsJira::FILTER_SETTINGS_IS_VALID_CONFIRMATION_NAME, $formId);

	// 	if ($isConfirmationValid && $senderEmail) {
	// 		// Send email.
	// 		$jiraConfirmation = $this->jira->sendFormEmail(
	// 			$formId,
	// 			$senderEmail,
	// 			$this->getSettingsValue(SettingsJira::SETTINGS_JIRA_SENDER_SUBJECT_KEY, $formId),
	// 			$this->getSettingsValue(SettingsJira::SETTINGS_JIRA_SENDER_TEMPLATE_KEY, $formId),
	// 			$files,
	// 			$params
	// 		);

	// 		// If email fails.
	// 		if (!$jiraConfirmation) {
	// 			// Always delete the files from the disk.
	// 			if ($files) {
	// 				$this->deleteFiles($files);
	// 			}

	// 			return \rest_ensure_response(
	// 				$this->getApiErrorOutput(
	// 					$this->labels->getLabel('jiraErrorEmailConfirmationSend', $formId),
	// 				)
	// 			);
	// 		}
	// 	}

	// 	// Always delete the files from the disk.
	// 	if ($files) {
	// 		$this->deleteFiles($files);
	// 	}

	// 	// Finish.
	// 	return \rest_ensure_response(
	// 		$this->getApiSuccessOutput(
	// 			$this->labels->getLabel('jiraSuccess', $formId),
	// 		)
	// 	);
	}
}
