<?php

/**
 * The class register route for public form submiting endpoint - HubSpot
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Hubspot
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Hubspot;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\Clearbit\ClearbitClientInterface;
use EightshiftForms\Integrations\Clearbit\SettingsClearbit;
use EightshiftForms\Integrations\Hubspot\HubspotClientInterface;
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\Validator;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class FormSubmitHubspotRoute
 */
class FormSubmitHubspotRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/' . AbstractBaseRoute::ROUTE_PREFIX_FORM_SUBMIT . '-hubspot/';

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
	 * Instance variable for Hubspot data.
	 *
	 * @var HubspotClientInterface
	 */
	protected $hubspotClient;

	/**
	 * Instance variable for Clearbit data.
	 *
	 * @var ClearbitClientInterface
	 */
	protected $clearbitClient;

	/**
	 * Instance variable of FormSubmitMailerInterface data.
	 *
	 * @var FormSubmitMailerInterface
	 */
	public $formSubmitMailer;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 * @param ValidationPatternsInterface $validationPatterns Inject ValidationPatternsInterface which holds validation methods.
	 * @param LabelsInterface $labels Inject LabelsInterface which holds labels data.
	 * @param HubspotClientInterface $hubspotClient Inject HubSpot which holds HubSpot connect data.
	 * @param ClearbitClientInterface $clearbitClient Inject Clearbit which holds clearbit connect data.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject FormSubmitMailerInterface which holds mailer methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		LabelsInterface $labels,
		HubspotClientInterface $hubspotClient,
		ClearbitClientInterface $clearbitClient,
		FormSubmitMailerInterface $formSubmitMailer
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
		$this->labels = $labels;
		$this->hubspotClient = $hubspotClient;
		$this->clearbitClient = $clearbitClient;
		$this->formSubmitMailer = $formSubmitMailer;
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
		$itemId = $formDataRefrerence['itemId'];
		$formId = $formDataRefrerence['formId'];
		$params = $formDataRefrerence['params'];
		$files = $formDataRefrerence['files'];

		// Send application to Hubspot.
		$response = $this->hubspotClient->postApplication(
			$itemId,
			$params,
			$files,
			$formId
		);

		// Output integrations validation issues.
		if (isset($response[Validator::VALIDATOR_OUTPUT_KEY])) {
			$response[Validator::VALIDATOR_OUTPUT_KEY] = $this->validator->getValidationLabelItems($response[Validator::VALIDATOR_OUTPUT_KEY], $formId);
		}

		// Check if Hubspot is using Clearbit.
		$useClearbit = \apply_filters(SettingsClearbit::FILTER_SETTINGS_IS_VALID_NAME, $formId, SettingsHubspot::SETTINGS_TYPE_KEY);

		if (!$response['isDisabled'] && $useClearbit) {
			$email = Helper::getEmailParamsField($params);

			if ($email) {
				// Get Clearbit data.
				$clearbitResponse = $this->clearbitClient->getApplication(
					$email,
					$params,
					$this->getOptionValueGroup(Filters::ALL[SettingsClearbit::SETTINGS_TYPE_KEY]['integration'][SettingsHubspot::SETTINGS_TYPE_KEY]['map']),
					$itemId,
					$formId
				);

				// If Clearbit data is ok send data to Hubspot.
				if ($clearbitResponse['code'] >= 200 && $clearbitResponse['code'] <= 299) {
					$this->hubspotClient->postContactProperty(
						$clearbitResponse['email'] ?? '',
						$clearbitResponse['data'] ?? []
					);
				} else {
					// Send fallback email.
					$this->formSubmitMailer->sendFallbackEmail($clearbitResponse);
				}
			}
		}

		// Skip fallback email if integration is disabled.
		if (!$response['isDisabled'] && $response['status'] === AbstractBaseRoute::STATUS_ERROR) {
			// Send fallback email.
			$this->formSubmitMailer->sendFallbackEmail($response);
		}

		// Send email if it is configured in the backend.
		if ($response['status'] === AbstractBaseRoute::STATUS_SUCCESS) {
			$this->formSubmitMailer->sendEmails($formDataRefrerence);
		}

		// Output fake success and send fallback email.
		if ($response['isDisabled'] && !$response[Validator::VALIDATOR_OUTPUT_KEY] ?? []) {
			$this->formSubmitMailer->sendFallbackEmail($response);

			$fakeResponse = $this->getIntegrationApiSuccessOutput($response);

			return \rest_ensure_response(
				$this->getIntegrationApiOutput(
					$fakeResponse,
					$this->labels->getLabel($fakeResponse['message'], $formId),
				)
			);
		}

		// Always delete the files from the disk.
		if ($files) {
			$this->deleteFiles($files);
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
	}
}
