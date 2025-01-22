<?php

/**
 * The class register route for public form submiting endpoint - Mailerlite
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Mailerlite
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Mailerlite;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Mailerlite\SettingsMailerlite;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;

/**
 * Class FormSubmitMailerliteRoute
 */
class FormSubmitMailerliteRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsMailerlite::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for Mailerlite data.
	 *
	 * @var ClientInterface
	 */
	protected $mailerliteClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param SecurityInterface $security Inject security methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject formSubmitMailer methods.
	 * @param EnrichmentInterface $enrichment Inject enrichment methods.
	 * @param ClientInterface $mailerliteClient Inject mailerliteClient methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		SecurityInterface $security,
		FormSubmitMailerInterface $formSubmitMailer,
		EnrichmentInterface $enrichment,
		ClientInterface $mailerliteClient
	) {
		parent::__construct($validator, $labels, $captcha, $security, $formSubmitMailer, $enrichment);
		$this->mailerliteClient = $mailerliteClient;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/' . UtilsConfig::ROUTE_PREFIX_FORM_SUBMIT . '/' . self::ROUTE_SLUG;
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
		$formId = $formDetails[UtilsConfig::FD_FORM_ID];

		// Send application to Mailerlite.
		$response = $this->mailerliteClient->postApplication(
			$formDetails[UtilsConfig::FD_ITEM_ID],
			$formDetails[UtilsConfig::FD_PARAMS],
			$formDetails[UtilsConfig::FD_FILES],
			$formId
		);

		$formDetails[UtilsConfig::FD_RESPONSE_OUTPUT_DATA] = $response;

		// Finish.
		return \rest_ensure_response(
			$this->getIntegrationCommonSubmitAction($formDetails)
		);
	}
}
