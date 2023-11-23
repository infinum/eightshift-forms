<?php

/**
 * The class register route for public form submiting endpoint - HubSpot
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Hubspot
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Hubspot;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\Clearbit\ClearbitClientInterface;
use EightshiftForms\Integrations\Clearbit\SettingsClearbit;
use EightshiftForms\Integrations\Hubspot\HubspotClientInterface;
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class FormSubmitHubspotRoute
 */
class FormSubmitHubspotRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsHubspot::SETTINGS_TYPE_KEY;

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
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject validation methods.
	 * @param ValidationPatternsInterface $validationPatterns Inject validation patterns methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param SecurityInterface $security Inject security methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject FormSubmitMailerInterface which holds mailer methods.
	 * @param HubspotClientInterface $hubspotClient Inject HubSpot which holds HubSpot connect data.
	 * @param ClearbitClientInterface $clearbitClient Inject Clearbit which holds clearbit connect data.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		SecurityInterface $security,
		FormSubmitMailerInterface $formSubmitMailer,
		HubspotClientInterface $hubspotClient,
		ClearbitClientInterface $clearbitClient
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
		$this->labels = $labels;
		$this->captcha = $captcha;
		$this->security = $security;
		$this->formSubmitMailer = $formSubmitMailer;
		$this->hubspotClient = $hubspotClient;
		$this->clearbitClient = $clearbitClient;
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
		$response = $this->hubspotClient->postApplication(
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
				$this->runClearbit($response, $formDataReference) // @phpstan-ignore-line
			)
		);
	}

	/**
	 * Run Clearbit integration.
	 *
	 * @param array<string, mixed> $response Response from Hubspot.
	 * @param array<string, mixed> $formDataReference Form reference got from abstract helper.
	 *
	 * @return void
	 */
	private function runClearbit(array $response, array $formDataReference): void
	{
		$itemId = $formDataReference['itemId'];
		$formId = $formDataReference['formId'];
		$params = $formDataReference['params'];

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
					// Send fallback email if error but ignore for unknown entry.
					if ($clearbitResponse['code'] !== 404) {
						$this->getFormSubmitMailer()->sendFallbackEmail($clearbitResponse);
					}
				}
			}
		}
	}
}
