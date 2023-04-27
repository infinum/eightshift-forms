<?php

/**
 * The class register route for public/admin form submiting endpoint
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Helpers\UploadHelper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Settings\Settings\Settings;
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
			$params = $this->prepareParams($request->get_body_params());
			$files = $request->get_file_params();

			// Get form ID.
			$formId = $this->getFormId($params);
			$formType = $this->getFormType($params);
			$formSettingsType = $this->getFormSettingsType($params);

			if ($formType === Settings::SETTINGS_TYPE_NAME || $formType === Settings::SETTINGS_GLOBAL_TYPE_NAME) {
				$formDataRefrerence = [
					'formId' => $formId,
					'type' => $formType,
					'itemId' => '',
					'innerId' => '',
					'fieldsOnly' => isset(Filters::ALL[$formSettingsType][$formType]) ? \apply_filters(Filters::ALL[$formSettingsType][$formType], $formId) : [],
				];
			} else {
				$formDataRefrerence = Helper::getFormDetailsById($formId);
			}

			$formDataRefrerence['params'] = $params;
			$formDataRefrerence['files'] = $files;

			// Validate request.
			if (!$this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_SKIP_VALIDATION_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY)) {
				// @phpstan-ignore-next-line.
				$validate = $this->getValidator()->validate($formDataRefrerence);
				if ($validate) {
					throw new UnverifiedRequestException(
						\esc_html__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
						$validate
					);
				}
			}

			// Extract hidden params from localStorage set on the frontend.
			$formDataRefrerence['params'] = $this->extractStorageParams($formDataRefrerence['params']);

			// Attach some special keys for specific types.
			if ($formType === SettingsMailer::SETTINGS_TYPE_CUSTOM_KEY) {
				$formDataRefrerence['action'] = $this->getFormCustomAction($formDataRefrerence['params']);
				$formDataRefrerence['actionExternal'] = $this->getFormCustomActionExternal($formDataRefrerence['params']);
			}

			// Upload files to temp folder.
			$formDataRefrerence['files'] = $this->uploadFiles($formDataRefrerence['files']);

			// Do Action.
			return $this->submitAction($formDataRefrerence);
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
	 * @param array<string, mixed> $formDataRefrerence Form refference got from abstract helper.
	 *
	 * @return mixed
	 */
	abstract protected function submitAction(array $formDataRefrerence);
}
