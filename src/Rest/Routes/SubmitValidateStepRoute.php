<?php

/**
 * The class register route for public form submiting endpoint - validating step
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;

/**
 * Class SubmitValidateStepRoute
 */
class SubmitValidateStepRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'validate-step';

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject validation methods.
	 * @param ValidationPatternsInterface $validationPatterns Inject validation patterns methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param SecurityInterface $security Inject security methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		SecurityInterface $security
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
		$this->labels = $labels;
		$this->captcha = $captcha;
		$this->security = $security;
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
	 * Detect what type of route it is.
	 *
	 * @return string
	 */
	protected function routeGetType(): string
	{
		return self::ROUTE_TYPE_STEP_VALIDATION;
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
		$debug = [
			'formDetails' => $formDetails,
		];

		$currentStep = $formDetails[UtilsConfig::FD_API_STEPS]['current'] ?? '';
		if (!$currentStep) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					\esc_html__('It looks like there is some problem with current step, please try again.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$submittedNames = $formDetails[UtilsConfig::FD_API_STEPS]['fields'] ?? [];
		if (!$submittedNames) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					\esc_html__('It looks like there is some problem with current step, please try again.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$steps = $formDetails[UtilsConfig::FD_STEPS_SETUP]['steps'] ?? [];
		if (!$steps) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					\esc_html__('It looks like there is some problem with next step, please try again.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$multiflow = $formDetails[UtilsConfig::FD_STEPS_SETUP]['multiflow'] ?? [];

		$nextStep = '';
		$progressBarItems = 0;
		$disableNextButton = false;

		if ($multiflow) {
			$type = 'multiflow';

			$params = $formDetails[UtilsConfig::FD_PARAMS] ?? [];

			if (!$params) {
				return \rest_ensure_response(
					UtilsApiHelper::getApiErrorPublicOutput(
						\esc_html__('It looks like there is some problem with parameters sent, please try again.', 'eightshift-forms'),
						[],
						$debug
					)
				);
			}

			foreach ($multiflow as $flow) {
				$flowNext = isset($flow[0]) ? \strval($flow[0]) : '';
				$flowCurrent = isset($flow[1]) ? \strval($flow[1]) : '';
				$flowConditions = $flow[2] ?? [];
				$flowProgressBarItems = $flow[3] ?? 0;
				$flowDisableNextButton = $flow[4] ?? false;

				if (!$flowNext || !$flowCurrent || !$flowConditions) {
					continue;
				}

				if ($currentStep !== $flowCurrent) {
					continue;
				}

				if ($this->checkFlowConditions($flowConditions, $params)) {
					$nextStep = $flowNext;
					$progressBarItems = $flowProgressBarItems;
					$disableNextButton = $flowDisableNextButton;
				}
			}

			// If nothing is valid go to normal next step.
			if (!$nextStep) {
				$nextStep = $this->getNextStepRegular($steps, $currentStep);
			}
		} else {
			$nextStep = $this->getNextStepRegular($steps, $currentStep);
			$type = 'multistep';
		}

		return \rest_ensure_response(
			UtilsApiHelper::getApiSuccessPublicOutput(
				\esc_html__('Step validation is successful, you may continue.', 'eightshift-forms'),
				[
					UtilsHelper::getStateResponseOutputKey('stepType') => $type,
					UtilsHelper::getStateResponseOutputKey('stepNextStep') => $nextStep,
					UtilsHelper::getStateResponseOutputKey('stepProgressBarItems') => $progressBarItems,
					UtilsHelper::getStateResponseOutputKey('stepIsDisableNextButton') => $disableNextButton,
				],
				$debug
			)
		);
	}

	/**
	 * Get next step ID in the regular (steps) flow.
	 *
	 * @param array<int, string> $steps Available steps.
	 * @param string $currentStep Current step ID.
	 *
	 * @return string
	 */
	private function getNextStepRegular(array $steps, string $currentStep): string
	{
		// Make sure all keys are strings.
		$keys = \array_filter(\array_values(\array_map(
			static function ($value) {
				return \strval($value);
			},
			\array_keys($steps)
		)));

		return (string) $keys[\array_search($currentStep, $keys, true) + 1];
	}

	/**
	 * Check if conditons are met to go to next step.
	 *
	 * @param array<int, mixed> $flowConditions Flow conditions that we need to check.
	 * @param array<string, mixed> $params Params array.
	 *
	 * @return boolean
	 */
	private function checkFlowConditions(array $flowConditions, array $params): bool
	{
		$output = [];

		foreach ($flowConditions as $index => $conditions) {
			$output[$index] = [];

			foreach ($conditions as $innerIndex => $condition) {
				$output[$index][$innerIndex] = false;

				$name = $condition[0] ?? '';
				$operator = $condition[1] ?? '';
				$value = $condition[2] ?? '';

				if (!$name || !$operator) {
					continue;
				}

				$paramValue = $params[$name]['value'] ?? '';

				if ($paramValue === $value) {
					$output[$index][$innerIndex] = true;
				}
			}
		}

		return \array_reduce($output, function ($carry, $validItem) {
			return $carry || (bool) \array_reduce($validItem, function ($subcarry, $item) {
					return $subcarry && (bool) $item;
			}, true);
		}, false);
	}
}
