<?php

/**
 * The class register route for public/admin form submiting endpoint
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Helpers\UploadHelper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Hooks\Variables;
use WP_REST_Request;

/**
 * Class AbstractFormSubmit
 */
abstract class AbstractFormSubmit extends AbstractBaseRoute
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
	 * Get callback arguments array
	 *
	 * @return array<string, mixed> Either an array of options for the endpoint, or an array of arrays for multiple methods.
	 */
	protected function getCallbackArguments(): array
	{
		return [
			'methods' => $this->getMethods(),
			'callback' => [$this, 'routeCallback'],
			'permission_callback' => [$this, 'permissionCallback'],
		];
	}

	/**
	 * Method that returns rest response
	 *
	 * @param WP_REST_Request $request Data got from endpoint url.
	 *
	 * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function routeCallback(WP_REST_Request $request)
	{
		$files = [];

		// Try catch request.
		try {
			$params = $this->prepareParams($request->get_body_params());

			// Get form ID.
			$formId = $this->getFormId($params);

			// Determine form type.
			$formType = $this->getFormType($params);

			// Get form fields for validation.
			$formData = isset(Filters::ALL[$formType]['fields']) ? \apply_filters(Filters::ALL[$formType]['fields'], $formId) : [];

			// Validate request.
			if (!Variables::skipFormValidation()) {
				$this->verifyRequest(
					$params,
					$request->get_file_params(),
					$formId,
					$formData
				);
			}

			// Remove unecesery internal params before continue.
			$params = $this->removeUneceseryParams($params);

			// Upload files to temp folder.
			$files = $this->uploadFiles($request->get_file_params());

			// Do Action.
			return $this->submitAction($formId, $params, $files);
		} catch (UnverifiedRequestException $e) {
			// Die if any of the validation fails.
			return \rest_ensure_response(
				[
					'code' => 400,
					'status' => 'error_validation',
					'message' => $e->getMessage(),
					'validation' => $e->getData(),
				]
			);
		}
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
	abstract public function submitAction(string $formId, array $params = [], $files = []);
}
