<?php

/**
 * The class register route for public form submiting endpoint - Moments
 *
 * @package EightshiftForms\Rest\Rout\Integrations\Momentses
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Moments;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Moments\MomentsEventsInterface;
use EightshiftForms\Integrations\Moments\SettingsMoments;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;

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
	 * @param ValidatorInterface $validator Inject validation methods.
	 * @param ValidationPatternsInterface $validationPatterns Inject validation patterns methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param SecurityInterface $security Inject security methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject FormSubmitMailerInterface which holds mailer methods.
	 * @param ClientInterface $momentsClient Inject Moments which holds Moments connect data.
	 * @param MomentsEventsInterface $momentsEvents Inject Moments which holds Moments events data.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		SecurityInterface $security,
		FormSubmitMailerInterface $formSubmitMailer,
		ClientInterface $momentsClient,
		MomentsEventsInterface $momentsEvents
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
		$this->labels = $labels;
		$this->captcha = $captcha;
		$this->security = $security;
		$this->formSubmitMailer = $formSubmitMailer;
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

		// Send application to Moments.
		$response = $this->momentsClient->postApplication(
			$formDetails[UtilsConfig::FD_ITEM_ID],
			$formDetails[UtilsConfig::FD_PARAMS],
			$formDetails[UtilsConfig::FD_FILES],
			$formId
		);

		$formDetails[UtilsConfig::FD_RESPONSE_OUTPUT_DATA] = $response;

		// Send event if needed.
		$this->sendEvent($formDetails);

		// Finish.
		return \rest_ensure_response(
			$this->getIntegrationCommonSubmitAction($formDetails)
		);
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
		$formId = $formDetails[UtilsConfig::FD_FORM_ID];
		$type = $formDetails[UtilsConfig::FD_TYPE] ?? '';

		$isUsed = UtilsSettingsHelper::isSettingCheckboxChecked(SettingsMoments::SETTINGS_MOMENTS_USE_EVENTS_KEY, SettingsMoments::SETTINGS_MOMENTS_USE_EVENTS_KEY, $formId);

		if (!$isUsed) {
			return;
		}

		$emailKey = UtilsSettingsHelper::getSettingValue(SettingsMoments::SETTINGS_MOMENTS_EVENTS_EMAIL_FIELD_KEY, $formId);
		$eventName = UtilsSettingsHelper::getSettingValue(SettingsMoments::SETTINGS_MOMENTS_EVENTS_EVENT_NAME_KEY, $formId);
		$map = UtilsSettingsHelper::getSettingValueGroup(SettingsMoments::SETTINGS_MOMENTS_EVENTS_MAP_KEY, $formId);

		if (!$emailKey || !$eventName) {
			return;
		}

		// Post event if needed.
		$response = $this->momentsEvents->postEvent(
			$formDetails[UtilsConfig::FD_PARAMS],
			$emailKey,
			$eventName,
			$map,
			$formId
		);

		if ($response[UtilsConfig::IARD_CODE] >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $response[UtilsConfig::IARD_CODE] <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return;
		}

		$this->getFormSubmitMailer()->sendfallbackIntegrationEmail(
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
