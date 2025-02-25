<?php

/**
 * The class register route for public form submiting endpoint - Airtable
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Airtable
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Airtable;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Integrations\Airtable\AirtableClientInterface;
use EightshiftForms\Integrations\Airtable\SettingsAirtable;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;

/**
 * Class FormSubmitAirtableRoute
 */
class FormSubmitAirtableRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsAirtable::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for Airtable data.
	 *
	 * @var AirtableClientInterface
	 */
	protected $airtableClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param SecurityInterface $security Inject security methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject formSubmitMailer methods.
	 * @param EnrichmentInterface $enrichment Inject enrichment methods.
	 * @param AirtableClientInterface $airtableClient Inject airtableClient methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		SecurityInterface $security,
		FormSubmitMailerInterface $formSubmitMailer,
		EnrichmentInterface $enrichment,
		AirtableClientInterface $airtableClient
	) {
		parent::__construct($validator, $labels, $captcha, $security, $formSubmitMailer, $enrichment);
		$this->airtableClient = $airtableClient;
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

		// Send application to Airtable.
		$response = $this->airtableClient->postApplication(
			$formDetails[UtilsConfig::FD_ITEM_ID] . UtilsConfig::DELIMITER . $formDetails[UtilsConfig::FD_INNER_ID],
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
