<?php

/**
 * The class register route for public form submiting endpoint - mailchimp
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Mailchimp
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Mailchimp;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Integrations\Mailchimp\MailchimpClientInterface;
use EightshiftForms\Integrations\Mailchimp\SettingsMailchimp;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class FormSubmitMailchimpRoute
 */
class FormSubmitMailchimpRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsMailchimp::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for Mailchimp data.
	 *
	 * @var MailchimpClientInterface
	 */
	protected $mailchimpClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject validation methods.
	 * @param ValidationPatternsInterface $validationPatterns Inject validation patterns methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param SecurityInterface $security Inject security methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject FormSubmitMailerInterface which holds mailer methods.
	 * @param MailchimpClientInterface $mailchimpClient Inject Mailchimp which holds Mailchimp connect data.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		SecurityInterface $security,
		FormSubmitMailerInterface $formSubmitMailer,
		MailchimpClientInterface $mailchimpClient
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
		$this->labels = $labels;
		$this->captcha = $captcha;
		$this->security = $security;
		$this->formSubmitMailer = $formSubmitMailer;
		$this->mailchimpClient = $mailchimpClient;
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

		// Send application to Mailchimp.
		$response = $this->mailchimpClient->postApplication(
			$formDataReference['itemId'],
			$formDataReference['params'],
			$formDataReference['files'],
			$formId
		);

		// Finish.
		return \rest_ensure_response(
			$this->getIntegrationCommonSubmitAction(
				$response,
				$formDataReference,
				$formId,
			)
		);
	}
}
