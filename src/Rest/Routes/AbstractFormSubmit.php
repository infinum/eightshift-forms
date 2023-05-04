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
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftForms\Validation\Validator;
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
	 * Route types.
	 */
	protected const ROUTE_TYPE_DEFAULT = 'default';
	protected const ROUTE_TYPE_FILE = 'file';
	protected const ROUTE_TYPE_STEP_VALIDATION = 'step-validation';

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
	 * @throws UnverifiedRequestException Wrong config error.
	 *
	 * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function routeCallback(WP_REST_Request $request)
	{
		// Try catch request.
		try {
			// Prepare all data.
			$formDataReference = $this->getFormDataReference($request);

			$isStepValidation = $this->isStepValidation($formDataReference);

			switch ($this->routeGetType()) {
				case self::ROUTE_TYPE_FILE:
					// Validate files.
					if (!$this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_SKIP_VALIDATION_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY)) {
						$validate = $this->getValidator()->validateFiles($formDataReference);

						if ($validate) {
							throw new UnverifiedRequestException(
								\esc_html__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
								$validate
							);
						}
					}

					// Upload files to temp folder.
					$formDataReference['files'] = $this->uploadFile($formDataReference['files']);
					break;
				default:
					// Validate params.
					if (!$this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_SKIP_VALIDATION_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY)) {
						$validate = $this->getValidator()->validateParams($formDataReference);

						if ($validate) {
							throw new UnverifiedRequestException(
								\esc_html__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
								$validate
							);
						}
					}

					if (!$isStepValidation) {
						// Extract hidden params from localStorage set on the frontend.
						$formDataReference['params'] = $this->extractStorageParams($formDataReference['params']);

						// Attach some special keys for specific types.
						if ($formDataReference['type'] === SettingsMailer::SETTINGS_TYPE_CUSTOM_KEY) {
							$formDataReference['action'] = $this->getFormCustomAction($formDataReference['params']);
							$formDataReference['actionExternal'] = $this->getFormCustomActionExternal($formDataReference['params']);
						}
					}

					break;
			}

			if ($this->isStepValidation($formDataReference)) {
				return \rest_ensure_response(
					$this->getApiSuccessOutput(
						\esc_html__('Step validation is success, you may continue.', 'eightshift-forms'),
						[
							'nextStep' => 'step-1',
						]
					)
				);
			}

			// Do Action.
			return $this->submitAction($formDataReference);
		} catch (UnverifiedRequestException $e) {
			// Die if any of the validation fails.
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					$e->getMessage(),
					[
						Validator::VALIDATOR_OUTPUT_KEY => $e->getData(),
					]
				)
			);
		}
	}

	/**
	 * Detect what type of route it is.
	 *
	 * @return string
	 */
	protected function routeGetType(): string {
		return self::ROUTE_TYPE_DEFAULT;
	}

	/**
	 * Returns validator class.
	 *
	 * @return $this
	 */
	abstract protected function getValidator();

	/**
	 * Returns validator patterns class.
	 *
	 * @return $this
	 */
	abstract protected function getValidatorPatterns();

	/**
	 * Returns validator labels class.
	 *
	 * @return $this
	 */
	abstract protected function getValidatorLabels();

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $formDataReference Form reference got from abstract helper.
	 *
	 * @return mixed
	 */
	abstract protected function submitAction(array $formDataReference);
}
