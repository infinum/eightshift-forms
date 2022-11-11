<?php

/**
 * The class register route for public form submiting endpoint - custom
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Validation\ValidatorInterface;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\ApiHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

/**
 * Class FormSubmitCustomRoute
 */
class FormSubmitCustomRoute extends AbstractFormSubmit
{
	/**
	 * Use api helper trait.
	 */
	use ApiHelper;

	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	protected $validator;

	/**
	 * Instance variable of LabelsInterface data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 * @param LabelsInterface $labels Inject LabelsInterface which holds labels data.
	 */
	public function __construct(
		ValidatorInterface $validator,
		LabelsInterface $labels
	) {
		$this->validator = $validator;
		$this->labels = $labels;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/form-submit-custom';
	}

	/**
	 * Implement submit action.
	 *
	 * @param string $formId Form ID.
	 * @param array<string, mixed> $params Params array.
	 * @param array<string, array<int, array<string, mixed>>> $files Files array.
	 *
	 * @return mixed
	 */
	public function submitAction(string $formId, array $params = [], $files = [])
	{
		$body = [];

		$formAction = $params[self::CUSTOM_FORM_PARAMS['action']]['value'];
		$formActionExternal = $params[self::CUSTOM_FORM_PARAMS['actionExternal']]['value'];

		// If form action is not set or empty.
		if (!$formAction) {
			return \rest_ensure_response([
				'status' => 'error',
				'code' => 400,
				'message' => $this->labels->getLabel('customNoAction', $formId),
			]);
		}

		if ($formActionExternal) {
			return \rest_ensure_response([
				'status' => 'redirect',
				'code' => 301,
				'message' => $this->labels->getLabel('customSuccessRedirect', $formId),
			]);
		}

		// Remove unnecessary internal params before continue.
		$customFields = \array_flip(Components::flattenArray(AbstractBaseRoute::CUSTOM_FORM_PARAMS));

		// Format body parameters to a key/value array.
		foreach ($params as $key => $param) {
			$name = $param['name'] ?? '';
			$value = $param['value'] ?? '';

			if (!$name || !$value) {
				continue;
			}

			if (isset($customFields[$key])) {
				continue;
			}

			$body[$name] = $value;
		}

		// Create a custom form action request.
		$customResponse = \wp_remote_post(
			$formAction,
			[
				'headers' => [
					'Content-Type' => 'application/x-www-form-urlencoded',
				],
				'body' => \http_build_query($body),
			]
		);

		$customResponseCode = \wp_remote_retrieve_response_code($customResponse);

		// If custom action request fails we'll return the generic error message.
		if ($customResponseCode > 399) {
			return \rest_ensure_response([
				'status' => 'error',
				'code' => $customResponseCode,
				'message' => $this->labels->getLabel('customError', $formId),
			]);
		}

		// If form action is valid we'll return the generic success message.
		return \rest_ensure_response([
			'status' => 'success',
			'code' => 200,
			'message' => $this->labels->getLabel('customSuccess', $formId),
		]);
	}
}
