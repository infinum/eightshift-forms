<?php

/**
 * The class register route for public form submitting endpoint - Moments
 *
 * @package EightshiftForms\Rest\Rout\Integrations\Momentses
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Moments;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Moments\MomentsEventsInterface;
use EightshiftForms\Integrations\Moments\SettingsMoments;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\SettingsHelpers;

/**
 * Class FormSubmitMomentsRoute
 */
class FormSubmitMomentsRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsMoments::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for Moments data.
	 *
	 * @var ClientInterface
	 */
	protected $momentsClient;

	/**
	 * Instance variable for Moments events data.
	 *
	 * @var MomentsEventsInterface
	 */
	protected $momentsEvents;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param SecurityInterface $security Inject security methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject formSubmitMailer methods.
	 * @param EnrichmentInterface $enrichment Inject enrichment methods.
	 * @param ClientInterface $momentsClient Inject momentsClient methods.
	 * @param MomentsEventsInterface $momentsEvents Inject momentsEvents methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		SecurityInterface $security,
		FormSubmitMailerInterface $formSubmitMailer,
		EnrichmentInterface $enrichment,
		ClientInterface $momentsClient,
		MomentsEventsInterface $momentsEvents
	) {
		parent::__construct($validator, $labels, $captcha, $security, $formSubmitMailer, $enrichment);
		$this->momentsClient = $momentsClient;
		$this->momentsEvents = $momentsEvents;
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
		// Send application to Moments.
		$response = $this->momentsClient->postApplication($formDetails);

		$formDetails[Config::FD_RESPONSE_OUTPUT_DATA] = $response;

		// Finish.
		return \rest_ensure_response(
			$this->getIntegrationCommonSubmitAction($formDetails)
		);
	}

	/**
	 * Call integration response success callback.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 * @param array<string, mixed> $successAdditionalData Data passed from the `getIntegrationResponseSuccessOutputAdditionalData` function.
	 *
	 * @return void
	 */
	protected function callIntegrationResponseSuccessCallback(array $formDetails, array $successAdditionalData): void
	{
		$this->sendEvent($formDetails);
	}

	/**
	 * Send event to Moments if needed.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return void
	 */
	private function sendEvent(array $formDetails): void
	{
		$formId = $formDetails[Config::FD_FORM_ID];
		$type = $formDetails[Config::FD_TYPE] ?? '';

		$isUsed = SettingsHelpers::isSettingCheckboxChecked(SettingsMoments::SETTINGS_MOMENTS_USE_EVENTS_KEY, SettingsMoments::SETTINGS_MOMENTS_USE_EVENTS_KEY, $formId);

		if (!$isUsed) {
			return;
		}

		$emailKey = SettingsHelpers::getSettingValue(SettingsMoments::SETTINGS_MOMENTS_EVENTS_EMAIL_FIELD_KEY, $formId);
		$eventName = SettingsHelpers::getSettingValue(SettingsMoments::SETTINGS_MOMENTS_EVENTS_EVENT_NAME_KEY, $formId);
		$map = SettingsHelpers::getSettingValueGroup(SettingsMoments::SETTINGS_MOMENTS_EVENTS_MAP_KEY, $formId);

		if (!$emailKey || !$eventName) {
			return;
		}

		// Post event if needed.
		$response = $this->momentsEvents->postEvent(
			$formDetails[Config::FD_PARAMS],
			$emailKey,
			$eventName,
			$map,
			$formId
		);

		if ($response[Config::IARD_CODE] >= Config::API_RESPONSE_CODE_SUCCESS && $response[Config::IARD_CODE] <= Config::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return;
		}

		$this->getFormSubmitMailer()->sendFallbackIntegrationEmail(
			$formDetails,
			// translators: %1$s is the type of the event, %2$s is the form id.
			\sprintf(\__('Failed %1$s event submit on form: %2$s', 'eightshift-forms'), $type, $formId),
			\__('The Moments integration data was sent but there was an error with the custom event. Here is all the data for debugging purposes.', 'eightshift-forms'),
			[
				'eventResponse' => $response
			]
		);
	}
}
