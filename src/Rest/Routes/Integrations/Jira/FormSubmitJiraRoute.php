<?php

/**
 * The class register route for public form submiting endpoint - Jira
 *
 * @package EightshiftForms\Rest\Route\Integrations\Jira
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Jira;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\Jira\JiraClientInterface;
use EightshiftForms\Integrations\Jira\SettingsJira;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class FormSubmitJiraRoute
 */
class FormSubmitJiraRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsJira::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for Jira data.
	 *
	 * @var JiraClientInterface
	 */
	protected $jiraClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject validation methods.
	 * @param ValidationPatternsInterface $validationPatterns Inject validation patterns methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param SecurityInterface $security Inject security methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject FormSubmitMailerInterface which holds mailer methods.
	 * @param JiraClientInterface $jiraClient Inject Jira which holds Jira connect data.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		SecurityInterface $security,
		FormSubmitMailerInterface $formSubmitMailer,
		JiraClientInterface $jiraClient
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
		$this->labels = $labels;
		$this->captcha = $captcha;
		$this->security = $security;
		$this->formSubmitMailer = $formSubmitMailer;
		$this->jiraClient = $jiraClient;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/' . AbstractBaseRoute::ROUTE_PREFIX_FORM_SUBMIT . '/' . self::ROUTE_SLUG;
	}


	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $formDataReference Form reference got from abstract helper.
	 *
	 * @return mixed
	 */
	protected function submitAction(array $formDataReference)
	{

		$formId = $formDataReference['formId'];

		// Send application to Hubspot.
		$response = $this->jiraClient->postApplication(
			$formDataReference['params'],
			[],
			$formId
		);

		$formDataReference['emailResponseTags'] = $this->getEmailResponseTags($response);

		// Finish.
		return \rest_ensure_response(
			$this->getIntegrationCommonSubmitAction(
				$response,
				$formDataReference,
				$formId,
			)
		);
	}

	/**
	 * Prepare email response tags from the API response.
	 *
	 * @param array<mixed> $response Response data to extract data from.
	 *
	 * @return array<string, string>
	 */
	private function getEmailResponseTags(array $response): array
	{
		$body = $response['body'] ?? [];
		$output = [];

		if (!$body) {
			return $output;
		}

		foreach (Filters::ALL[SettingsJira::SETTINGS_TYPE_KEY]['emailTemplateTags'] as $key => $value) {
			$item = $body[$value] ?? '';

			if ($key === 'jiraIssueUrl') {
				$jiraKey = $body['key'] ?? '';

				if ($jiraKey) {
					$output[$key] = $this->jiraClient->getBaseUrlOutputPrefix() . "browse/{$jiraKey}";
				}
			} else {
				$output[$key] = $item;
			}
		}

		return $output;
	}
}
