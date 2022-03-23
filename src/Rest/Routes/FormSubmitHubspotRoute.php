<?php

/**
 * The class register route for public form submiting endpoint - HubSpot
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Helpers\UploadHelper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\Clearbit\SettingsClearbit;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Hubspot\HubspotClientInterface;
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class FormSubmitHubspotRoute
 */
class FormSubmitHubspotRoute extends AbstractFormSubmit
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
	 * Instance variable for Hubspot data.
	 *
	 * @var HubspotClientInterface
	 */
	protected $hubspotClient;

	/**
	 * Instance variable for Clearbit data.
	 *
	 * @var ClientInterface
	 */
	protected $clearbitClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 * @param LabelsInterface $labels Inject LabelsInterface which holds labels data.
	 * @param HubspotClientInterface $hubspotClient Inject HubSpot which holds HubSpot connect data.
	 * @param ClientInterface $clearbitClient Inject Clearbit which holds clearbit connect data.
	 */
	public function __construct(
		ValidatorInterface $validator,
		LabelsInterface $labels,
		HubspotClientInterface $hubspotClient,
		ClientInterface $clearbitClient
	) {
		$this->validator = $validator;
		$this->labels = $labels;
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
		return '/form-submit-hubspot';
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
		// Check if Hubspot data is set and valid.
		$isSettingsValid = \apply_filters(SettingsHubspot::FILTER_SETTINGS_IS_VALID_NAME, $formId);

		// Bailout if settings are not ok.
		if (!$isSettingsValid) {
			return \rest_ensure_response([
				'status' => 'error',
				'code' => 400,
				'message' => $this->labels->getLabel('hubspotErrorSettingsMissing', $formId),
			]);
		}

		// Send application to Hubspot.
		// $response = $this->hubspotClient->postApplication(
		// 	$this->getSettingsValue(SettingsHubspot::SETTINGS_HUBSPOT_ITEM_ID_KEY, $formId),
		// 	$params,
		// 	$files,
		// 	$formId
		// );

		// Check if Hubspot is using Clearbit.
		$useClearbit = \apply_filters(SettingsClearbit::FILTER_SETTINGS_IS_VALID_NAME, $formId, SettingsHubspot::SETTINGS_TYPE_KEY);

		if ($useClearbit) {
			$response = $this->clearbitClient->postApplication(
				$this->getSettingsValue(Filters::ALL[SettingsClearbit::SETTINGS_TYPE_KEY]['integration'][SettingsHubspot::SETTINGS_TYPE_KEY]['email'], $formId),
				$params,
				[],
				$formId
			);

			// error_log( print_r( ( $response ), true ) );
		}

		// Always delete the files from the disk.
		if ($files) {
			$this->deleteFiles($files);
		}

		// Finish.
		return \rest_ensure_response([
			'code' => $response['code'],
			'status' => $response['status'],
			'message' => $this->labels->getLabel($response['message'], $formId),
		]);
	}
}
