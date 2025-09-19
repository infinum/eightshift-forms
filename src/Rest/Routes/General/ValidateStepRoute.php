<?php

/**
 * The class register route for public form submitting endpoint - validating step
 *
 * @package EightshiftForms\Rest\Routes\General;
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\General;

use EightshiftForms\Config\Config;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractIntegrationFormSubmit;
use EightshiftForms\Troubleshooting\SettingsFallback;

/**
 * Class ValidateStepRoute
 */
class ValidateStepRoute extends AbstractIntegrationFormSubmit
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
	 * Check if the route is admin protected.
	 *
	 * @return boolean
	 */
	protected function isRouteAdminProtected(): bool
	{
		return false;
	}

	/**
	 * Check if the route should check captcha.
	 *
	 * @return boolean
	 */
	protected function shouldCheckCaptcha(): bool
	{
		return false;
	}

	/**
	 * Check if the route should check security.
	 *
	 * @return boolean
	 */
	protected function shouldCheckSecurity(): bool
	{
		return false;
	}

	/**
	 * Check if the route should check enrichment.
	 *
	 * @return boolean
	 */
	protected function shouldCheckEnrichment(): bool
	{
		return false;
	}

	/**
	 * Check if the route should check country.
	 *
	 * @return boolean
	 */
	protected function shouldCheckCountry(): bool
	{
		return false;
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
	 * @throws BadRequestException If validation steps are missing.
	 *
	 * @return mixed
	 */
	protected function submitAction(array $formDetails)
	{
		$currentStep = $formDetails[Config::FD_API_STEPS]['current'] ?? '';

		if (!$currentStep) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->getLabels()->getLabel('validationStepsCurrentStepProblem'),
				[
					AbstractBaseRoute::R_DEBUG => $formDetails,
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_VALIDATION_STEPS_CURRENT_STEP_PROBLEM,
				],
			);
			// phpcs:enable
		}

		$submittedNames = $formDetails[Config::FD_API_STEPS]['fields'] ?? [];
		if (!$submittedNames) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->getLabels()->getLabel('validationStepsFieldsProblem'),
				[
					AbstractBaseRoute::R_DEBUG => $formDetails,
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_VALIDATION_STEPS_FIELDS_PROBLEM,
				],
			);
			// phpcs:enable
		}

		$steps = $formDetails[Config::FD_STEPS_SETUP]['steps'] ?? [];
		if (!$steps) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->getLabels()->getLabel('validationStepsNextStepProblem'),
				[
					AbstractBaseRoute::R_DEBUG => $formDetails,
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_VALIDATION_STEPS_NEXT_STEP_PROBLEM,
				],
			);
			// phpcs:enable
		}

		$multiflow = $formDetails[Config::FD_STEPS_SETUP]['multiflow'] ?? [];

		$nextStep = '';
		$progressBarItems = 0;
		$disableNextButton = false;

		if ($multiflow) {
			$type = 'multiflow';

			$params = $formDetails[Config::FD_PARAMS] ?? [];

			if (!$params) {
				// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
				throw new BadRequestException(
					$this->getLabels()->getLabel('validationStepsParametersProblem'),
					[
						AbstractBaseRoute::R_DEBUG => $formDetails,
						AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_VALIDATION_STEPS_PARAMETERS_PROBLEM,
					],
				);
				// phpcs:enable
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

		return [
			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('validationStepsSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG => $formDetails,
				AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_VALIDATION_STEPS_SUCCESS,
			],
			AbstractBaseRoute::R_DATA => [
				UtilsHelper::getStateResponseOutputKey('stepType') => $type,
				UtilsHelper::getStateResponseOutputKey('stepNextStep') => $nextStep,
				UtilsHelper::getStateResponseOutputKey('stepProgressBarItems') => $progressBarItems,
				UtilsHelper::getStateResponseOutputKey('stepIsDisableNextButton') => $disableNextButton,
			],
		];
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
	 * Check if conditions are met to go to next step.
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
			return $carry || (bool) \array_reduce($validItem, function ($subCarry, $item) {
				return $subCarry && (bool) $item;
			}, true);
		}, false);
	}
}
