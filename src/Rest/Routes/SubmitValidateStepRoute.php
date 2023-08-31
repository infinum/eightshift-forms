<?php

/**
 * The class register route for public form submiting endpoint - validating step
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class SubmitValidateStepRoute
 */
class SubmitValidateStepRoute extends AbstractFormSubmit
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'validate-step';

	/**
	 * Instance variable of LabelsInterface data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

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
	 * Instance variable of CaptchaInterface data.
	 *
	 * @var CaptchaInterface
	 */
	protected $captcha;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 * @param ValidationPatternsInterface $validationPatterns Inject ValidationPatternsInterface which holds validation methods.
	 * @param LabelsInterface $labels Inject LabelsInterface which holds labels data.
	 * @param CaptchaInterface $captcha Inject CaptchaInterface which holds captcha data.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		LabelsInterface $labels,
		CaptchaInterface $captcha
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
		$this->labels = $labels;
		$this->captcha = $captcha;
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
	 * Returns validator labels class.
	 *
	 * @return LabelsInterface
	 */
	protected function getValidatorLabels()
	{
		return $this->labels;
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
	 * Returns captcha class.
	 *
	 * @return CaptchaInterface
	 */
	protected function getCaptcha()
	{
		return $this->captcha;
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
	 * @param array<string, mixed> $formDataReference Form reference got from abstract helper.
	 *
	 * @return mixed
	 */
	protected function submitAction(array $formDataReference)
	{
		$debug = [
			'formDataReference' => $formDataReference,
		];

		$currentStep = $formDataReference['apiSteps']['current'] ?? '';
		if (!$currentStep) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					\esc_html__('It looks like there is some problem with current step, please try again.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$submittedNames = $formDataReference['apiSteps']['fields'] ?? [];
		if (!$submittedNames) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					\esc_html__('It looks like there is some problem with current step, please try again.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$steps = $formDataReference['stepsSetup']['steps'] ?? [];
		if (!$steps) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					\esc_html__('It looks like there is some problem with next step, please try again.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		$multiflow = $formDataReference['stepsSetup']['multiflow'] ?? [];

		$nextStep = '';
		$progressBarItems = 0;

		if ($multiflow) {
			$type = 'multiflow';

			$params = $formDataReference['params'] ?? [];

			if (!$params) {
				return \rest_ensure_response(
					$this->getApiErrorOutput(
						\esc_html__('It looks like there is some problem with parameters sent, please try again.', 'eightshift-forms'),
						[],
						$debug
					)
				);
			}

			foreach ($multiflow as $flow) {
				$flowNext = $flow[0] ?? '';
				$flowCurrent = $flow[1] ?? '';
				$flowConditions = $flow[2] ?? [];
				$flowProgressBarItems = $flow[3] ?? 0;

				if (!$flowNext || !$flowCurrent || !$flowConditions) {
					continue;
				}

				if ($currentStep !== $flowCurrent) {
					continue;
				}

				if ($this->checkFlowConditions($flowConditions, $params)) {
					$nextStep = $flowNext;
					$progressBarItems = $flowProgressBarItems;
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
			$this->getApiSuccessOutput(
				\esc_html__('Step validation is success, you may continue.', 'eightshift-forms'),
				[
					'type' => $type,
					'nextStep' => $nextStep,
					'progressBarItems' => $progressBarItems,
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
		$keys = \array_keys($steps);
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
