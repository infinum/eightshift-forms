<?php

/**
 * The class register route for public form submitting endpoint - Greenhouse
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Greenhouse
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Greenhouse;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftForms\Config\Config;

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
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param SecurityInterface $security Inject security methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject formSubmitMailer methods.
	 * @param EnrichmentInterface $enrichment Inject enrichment methods.
	 * @param ClientInterface $greenhouseClient Inject greenhouseClient methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		SecurityInterface $security,
		FormSubmitMailerInterface $formSubmitMailer,
		EnrichmentInterface $enrichment,
		ClientInterface $greenhouseClient
	) {
		parent::__construct($validator, $labels, $captcha, $security, $formSubmitMailer, $enrichment);
		$this->greenhouseClient = $greenhouseClient;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/' . Config::ROUTE_PREFIX_FORM_SUBMIT . '/' . self::ROUTE_SLUG;
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return mixed
	 */
	protected function submitAction(array $formDetails)
	{
		$formId = $formDetails[Config::FD_FORM_ID];

		// Send application to Greenhouse.
		$response = $this->greenhouseClient->postApplication(
			$formDetails[Config::FD_ITEM_ID],
			$formDetails[Config::FD_PARAMS],
			$formDetails[Config::FD_FILES],
			$formId
		);

		$formDetails[Config::FD_RESPONSE_OUTPUT_DATA] = $response;

		// Finish.
		return \rest_ensure_response(
			$this->getIntegrationCommonSubmitAction($formDetails)
		);
	}
}
