<?php

/**
 * The class register route for public form submiting endpoint - validating step
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\UtilsHelper;

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

		$currentStep = $formDetails[Config::FD_API_STEPS]['current'] ?? '';
		if (!$currentStep) {
			return \rest_ensure_response(
				ApiHelpers::getApiErrorPublicOutput(
					\esc_html__('It looks like there is some problem with current step, please try again.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$submittedNames = $formDetails[Config::FD_API_STEPS]['fields'] ?? [];
		if (!$submittedNames) {
			return \rest_ensure_response(
				ApiHelpers::getApiErrorPublicOutput(
					\esc_html__('It looks like there is some problem with current step, please try again.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$steps = $formDetails[Config::FD_STEPS_SETUP]['steps'] ?? [];
		if (!$steps) {
			return \rest_ensure_response(
				ApiHelpers::getApiErrorPublicOutput(
					\esc_html__('It looks like there is some problem with next step, please try again.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$multiflow = $formDetails[Config::FD_STEPS_SETUP]['multiflow'] ?? [];

		$nextStep = '';
		$progressBarItems = 0;
		$disableNextButton = false;

		if ($multiflow) {
			$type = 'multiflow';

			$params = $formDetails[Config::FD_PARAMS] ?? [];

			if (!$params) {
				return \rest_ensure_response(
					ApiHelpers::getApiErrorPublicOutput(
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
			ApiHelpers::getApiSuccessPublicOutput(
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
