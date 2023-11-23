<?php

/**
 * The class register route for public form submiting endpoint - Greenhouse
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Greenhouse
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Greenhouse;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Entries\EntriesInterface;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class FormSubmitGreenhouseRoute
 */
class FormSubmitGreenhouseRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsGreenhouse::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable of ClientInterface data.
	 *
	 * @var ClientInterface
	 */
	protected $greenhouseClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject validation methods.
	 * @param ValidationPatternsInterface $validationPatterns Inject validation patterns methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param SecurityInterface $security Inject security methods.
	 * @param EntriesInterface $entries Inject entries methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject FormSubmitMailerInterface which holds mailer methods.
	 * @param ClientInterface $greenhouseClient Inject ClientInterface which holds Greenhouse connect data.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		SecurityInterface $security,
		EntriesInterface $entries,
		FormSubmitMailerInterface $formSubmitMailer,
		ClientInterface $greenhouseClient
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
		$this->labels = $labels;
		$this->captcha = $captcha;
		$this->security = $security;
		$this->entries = $entries;
		$this->formSubmitMailer = $formSubmitMailer;
		$this->greenhouseClient = $greenhouseClient;
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

		// Send application to Greenhouse.
		$response = $this->greenhouseClient->postApplication(
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
