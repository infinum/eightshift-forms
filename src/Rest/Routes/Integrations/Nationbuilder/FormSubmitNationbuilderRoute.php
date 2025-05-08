<?php

/**
 * The class register route for public form submitting endpoint - Nationbuilder
 *
 * @package EightshiftForms\Rest\Route\Integrations\Nationbuilder
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Nationbuilder;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Integrations\Nationbuilder\NationbuilderClientInterface;
use EightshiftForms\Integrations\Nationbuilder\SettingsNationbuilder;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftForms\Config\Config;

/**
 * Class FormSubmitNationbuilderRoute
 */
class FormSubmitNationbuilderRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsNationbuilder::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for Nationbuilder data.
	 *
	 * @var NationbuilderClientInterface
	 */
	protected $nationbuilderClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param SecurityInterface $security Inject security methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject formSubmitMailer methods.
	 * @param EnrichmentInterface $enrichment Inject enrichment methods.
	 * @param NationbuilderClientInterface $nationbuilderClient Inject nationbuilderClient methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		SecurityInterface $security,
		FormSubmitMailerInterface $formSubmitMailer,
		EnrichmentInterface $enrichment,
		NationbuilderClientInterface $nationbuilderClient
	) {
		parent::__construct($validator, $labels, $captcha, $security, $formSubmitMailer, $enrichment);
		$this->nationbuilderClient = $nationbuilderClient;
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

		// Send application to Hubspot.
		$response = $this->nationbuilderClient->postApplication(
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

	/**
	 * Prepare email response tags from the API response.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, string>
	 */
	protected function getEmailResponseTags(array $formDetails): array
	{
		$body = $formDetails[Config::FD_RESPONSE_OUTPUT_DATA]['body']['data'] ?? [];
		$output = [];

		if (!$body) {
			return $output;
		}

		foreach (\apply_filters(Config::FILTER_SETTINGS_DATA, [])[SettingsNationbuilder::SETTINGS_TYPE_KEY]['emailTemplateTags'] ?? [] as $key => $value) {
			$output[$key] = $body[$value] ?? '';
		}

		return $output;
	}
}
