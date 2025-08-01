<?php

/**
 * The class register route for public form submitting endpoint - Calculator
 *
 * @package EightshiftForms\Rest\Route\Integrations\Calculator
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Calculator;

use EightshiftForms\Integrations\Calculator\SettingsCalculator;
use EightshiftForms\Rest\Routes\AbstractIntegrationFormSubmit;
use EightshiftForms\Config\Config;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Troubleshooting\SettingsFallback;

/**
 * Class FormSubmitCalculatorRoute
 */
class FormSubmitCalculatorRoute extends AbstractIntegrationFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsCalculator::SETTINGS_TYPE_KEY;

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
	 * @param array<string, mixed> $params Params passed from the request.
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
		if (!\apply_filters(SettingsCalculator::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			throw new BadRequestException(
				$this->getLabels()->getLabel('calculatorMissingConfig'),
				[
					AbstractBaseRoute::R_DEBUG => $formDetails,
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_CALCULATOR_MISSING_CONFIG,
				],
			);
		}

		$formId = $formDetails[Config::FD_FORM_ID];

		// Set validation submit once.
		$this->getValidator()->setValidationSubmitOnce($formId);

		// Located before the sendEmail method so we can utilize common email response tags.
		$successAdditionalData = $this->getIntegrationResponseSuccessOutputAdditionalData($formDetails);

		// Send only if explicitly enabled in the settings.
		if (SettingsHelpers::isSettingCheckboxChecked(SettingsMailer::SETTINGS_MAILER_SETTINGS_USE_KEY, SettingsMailer::SETTINGS_MAILER_SETTINGS_USE_KEY, $formId)) {
			$this->getMailer()->sendEmails(
				$formDetails,
				$this->getCommonEmailResponseTags(
					\array_merge(
						$successAdditionalData['public'],
						$successAdditionalData['private']
					),
					$formDetails
				)
			);
		}

		return [
			AbstractBaseRoute::R_MSG => $this->labels->getLabel('calculatorSuccess', $formId),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG => $formDetails,
				AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_CALCULATOR_SUCCESS,
			],
			AbstractBaseRoute::R_DATA => \array_merge(
				$successAdditionalData['public'],
				$successAdditionalData['additional']
			),
		];
	}
}
