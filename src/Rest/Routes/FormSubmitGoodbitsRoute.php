<?php

/**
 * The class register route for public form submiting endpoint - Goodbits
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Helpers\UploadHelper;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Goodbits\SettingsGoodbits;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class FormSubmitGoodbitsRoute
 */
class FormSubmitGoodbitsRoute extends AbstractFormSubmit
{
	/**
	 * Use trait Upload_Helper inside class.
	 */
	use UploadHelper;

	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	public $validator;

	/**
	 * Instance variable of LabelsInterface data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Instance variable for Goodbits data.
	 *
	 * @var ClientInterface
	 */
	protected $goodbitsClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 * @param LabelsInterface $labels Inject LabelsInterface which holds labels data.
	 * @param ClientInterface $goodbitsClient Inject Goodbits which holds Goodbits connect data.
	 */
	public function __construct(
		ValidatorInterface $validator,
		LabelsInterface $labels,
		ClientInterface $goodbitsClient
	) {
		$this->validator = $validator;
		$this->labels = $labels;
		$this->goodbitsClient = $goodbitsClient;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/form-submit-goodbits';
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

		// Check if Goodbits data is set and valid.
		$isSettingsValid = \apply_filters(SettingsGoodbits::FILTER_SETTINGS_IS_VALID_NAME, $formId);

		// Bailout if settings are not ok.
		if (!$isSettingsValid) {
			return \rest_ensure_response([
				'status' => 'error',
				'code' => 400,
				'message' => $this->labels->getLabel('goodbitsErrorSettingsMissing', $formId),
			]);
		}

		// Send application to Goodbits.
		$response = $this->goodbitsClient->postApplication(
			$this->getSettingsValue(SettingsGoodbits::SETTINGS_GOODBITS_LIST_KEY, $formId),
			$params,
			[],
			$formId
		);

		// Finish.
		return \rest_ensure_response([
			'code' => $response['code'],
			'status' => $response['status'],
			'message' => $this->labels->getLabel($response['message'], $formId),
		]);
	}
}
