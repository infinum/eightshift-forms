<?php

/**
 * The class register route for public form submiting endpoint - Calculator
 *
 * @package EightshiftForms\Rest\Route\Integrations\Calculator
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Calculator;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Integrations\Calculator\SettingsCalculator;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;

/**
 * Class FormSubmitCalculatorRoute
 */
class FormSubmitCalculatorRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsCalculator::SETTINGS_TYPE_KEY;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject validation methods.
	 * @param ValidationPatternsInterface $validationPatterns Inject validation patterns methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param SecurityInterface $security Inject security methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject FormSubmitMailerInterface which holds mailer methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		SecurityInterface $security,
		FormSubmitMailerInterface $formSubmitMailer
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
		$this->labels = $labels;
		$this->captcha = $captcha;
		$this->security = $security;
		$this->formSubmitMailer = $formSubmitMailer;
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

		$debug = [
			'formDetails' => $formDetails,
		];

		// Filter params.
		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsCalculator::SETTINGS_TYPE_KEY, 'prePostParams']);
		if (\has_filter($filterName)) {
			$formDetails[UtilsConfig::FD_PARAMS] = \apply_filters($filterName, $formDetails[UtilsConfig::FD_PARAMS], $formId) ?? [];
		}

		$additionalOutput = [];

		// Output result output items as a response key.
		$filterName = UtilsHooksHelper::getFilterName(['block', 'form', 'resultOutputItems']);
		if (\has_filter($filterName)) {
			$additionalOutput[UtilsHelper::getStateResponseOutputKey('resultOutputItems')] = \apply_filters($filterName, [], $formDetails, $formId) ?? [];
		}

		// Output result output parts as a response key.
		$filterName = UtilsHooksHelper::getFilterName(['block', 'form', 'resultOutputParts']);
		if (\has_filter($filterName)) {
			$additionalOutput[UtilsHelper::getStateResponseOutputKey('resultOutputParts')] = \apply_filters($filterName, [], $formDetails, $formId) ?? [];
		}

		return \rest_ensure_response(
			UtilsApiHelper::getApiSuccessPublicOutput(
				$this->labels->getLabel('calculatorSuccess', $formId),
				$additionalOutput,
				$debug
			)
		);
	}
}
