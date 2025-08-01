<?php

/**
 * The class register route for public form submitting endpoint - Pipedrive
 *
 * @package EightshiftForms\Rest\Route\Integrations\Pipedrive
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Pipedrive;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Integrations\Pipedrive\PipedriveClientInterface;
use EightshiftForms\Integrations\Pipedrive\SettingsPipedrive;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Integrations\Mailer\MailerInterface;
use EightshiftForms\Rest\Routes\AbstractIntegrationFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftForms\Config\Config;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Exception\DisabledIntegrationException;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Troubleshooting\SettingsFallback;

/**
 * Class FormSubmitPipedriveRoute
 */
class FormSubmitPipedriveRoute extends AbstractIntegrationFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsPipedrive::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for Pipedrive data.
	 *
	 * @var PipedriveClientInterface
	 */
	protected $pipedriveClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param SecurityInterface $security Inject security methods.
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param MailerInterface $mailer Inject mailerInterface methods.
	 * @param EnrichmentInterface $enrichment Inject enrichment methods.
	 * @param PipedriveClientInterface $pipedriveClient Inject pipedriveClient methods.
	 */
	public function __construct(
		SecurityInterface $security,
		ValidatorInterface $validator,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		MailerInterface $mailer,
		EnrichmentInterface $enrichment,
		PipedriveClientInterface $pipedriveClient
	) {
		$this->security = $security;
		$this->validator = $validator;
		$this->labels = $labels;
		$this->captcha = $captcha;
		$this->mailer = $mailer;
		$this->enrichment = $enrichment;
		$this->pipedriveClient = $pipedriveClient;
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
	 * Check if the route is admin protected.
	 *
	 * @return boolean
	 */
	protected function isRouteAdminProtected(): bool
	{
		return true;
	}

	/**
	 * Get mandatory params.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(array $params): array
	{
		return [
			Config::FD_FORM_ID => 'string',
			Config::FD_POST_ID => 'string',
		];
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
		if (SettingsHelpers::isOptionCheckboxChecked(SettingsPipedrive::SETTINGS_PIPEDRIVE_SKIP_INTEGRATION_KEY, SettingsPipedrive::SETTINGS_PIPEDRIVE_SKIP_INTEGRATION_KEY)) {
			$integrationSuccessResponse = $this->getIntegrationResponseSuccessOutput($formDetails);

			throw new DisabledIntegrationException(
				$integrationSuccessResponse[AbstractBaseRoute::R_MSG],
				$integrationSuccessResponse[AbstractBaseRoute::R_DEBUG],
				$integrationSuccessResponse[AbstractBaseRoute::R_DATA]
			);
		}

		$formId = $formDetails[Config::FD_FORM_ID];

		if (!\apply_filters(SettingsPipedrive::FILTER_SETTINGS_IS_VALID_NAME, false, $formId)) {
			throw new BadRequestException(
				$this->getLabels()->getLabel('pipedriveMissingConfig'),
				[
					AbstractBaseRoute::R_DEBUG => $formDetails,
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_PIPEDRIVE_MISSING_CONFIG,
				],
			);
		}

		// Send application to Hubspot.
		$response = $this->pipedriveClient->postApplication($formDetails);

		$formDetails[Config::FD_RESPONSE_OUTPUT_DATA] = $response;

		// Finish.
		return $this->getIntegrationCommonSubmitAction($formDetails);
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

		foreach (\apply_filters(Config::FILTER_SETTINGS_DATA, [])[SettingsPipedrive::SETTINGS_TYPE_KEY]['emailTemplateTags'] ?? [] as $key => $value) {
			$output[$key] = $body[$value] ?? '';
		}

		return $output;
	}
}
