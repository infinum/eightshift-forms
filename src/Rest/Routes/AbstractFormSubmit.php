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

			// Validate request.
			if (!$this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_SKIP_VALIDATION_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY)) {
				// @phpstan-ignore-next-line.
				if (!$this->isFileUploadRoute()) {
					$validate = $this->getValidator()->validateParams($formDataReference);
				} else {
					$validate = $this->getValidator()->validateFiles($formDataReference);
				}

				if ($validate) {
					throw new UnverifiedRequestException(
						\esc_html__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
						$validate
					);
				}
			}

			if (!$this->isFileUploadRoute()) {
				// Extract hidden params from localStorage set on the frontend.
				$formDataReference['params'] = $this->extractStorageParams($formDataReference['params']);
			}

			// Attach some special keys for specific types.
			if ($formDataReference['type'] === SettingsMailer::SETTINGS_TYPE_CUSTOM_KEY) {
				$formDataReference['action'] = $this->getFormCustomAction($formDataReference['params']);
				$formDataReference['actionExternal'] = $this->getFormCustomActionExternal($formDataReference['params']);
			}

			// Upload files to temp folder.
			$formDataReference['files'] = $this->uploadFile($formDataReference['files']);

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
	 * Detect if route is file upload or a regular submit.
	 *
	 * @return boolean
	 */
	protected function isFileUploadRoute(): bool {
		return false;
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
	 * @param array<string, mixed> $formDataReference Form refference got from abstract helper.
	 *
	 * @return mixed
	 */
	abstract protected function submitAction(array $formDataReference);
}
