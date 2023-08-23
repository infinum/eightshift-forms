<?php

/**
 * The class register route for public/admin form submiting endpoint
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Helpers\UploadHelper;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftForms\Captcha\SettingsCaptcha;
use EightshiftForms\Settings\FiltersOuputMock;
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
	 * Use general helper trait.
	 */
	use FiltersOuputMock;

	/**
	 * Instance variable of CaptchaInterface data.
	 *
	 * @var CaptchaInterface
	 */
	protected $captcha;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param CaptchaInterface $captcha Inject CaptchaInterface which holds captcha data.
	 */
	public function __construct(CaptchaInterface $captcha)
	{
		$this->captcha = $captcha;
	}

	/**
	 * Route types.
	 */
	protected const ROUTE_TYPE_DEFAULT = 'default';
	protected const ROUTE_TYPE_FILE = 'file';
	protected const ROUTE_TYPE_SETTINGS = 'settings';
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

			switch ($this->routeGetType()) {
				case self::ROUTE_TYPE_FILE:
					// Validate files.
					if (!$this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_SKIP_VALIDATION_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY)) {
						$validate = $this->getValidator()->validateFiles($formDataReference); // @phpstan-ignore-line

						if ($validate) {
							throw new UnverifiedRequestException(
								\esc_html__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
								$validate
							);
						}
					}

					// Upload files to temp folder.
					$formDataReference['filesUpload'] = $this->uploadFile($formDataReference['filesUpload']);
					break;
				case self::ROUTE_TYPE_SETTINGS:
					// Validate params.
					$validate = $this->getValidator()->validateParams($formDataReference); // @phpstan-ignore-line

					if ($validate) {
						throw new UnverifiedRequestException(
							\esc_html__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
							$validate
						);
					}
					break;
				case self::ROUTE_TYPE_STEP_VALIDATION:
					// Validate params.
					if (!$this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_SKIP_VALIDATION_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY)) {
						$validate = $this->getValidator()->validateParams($formDataReference); // @phpstan-ignore-line

						if ($validate) {
							throw new UnverifiedRequestException(
								\esc_html__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
								$validate
							);
						}
					}
					break;
				default:
					// Skip any validation if direct import.
					if (isset($formDataReference['directImport'])) {
						break;
					}

					// Validate params.
					if (!$this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_SKIP_VALIDATION_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY)) {
						$validate = $this->getValidator()->validateParams($formDataReference); // @phpstan-ignore-line

						if ($validate) {
							throw new UnverifiedRequestException(
								\esc_html__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
								$validate
							);
						}
					}

					// Validate captcha.
					if (\apply_filters(SettingsCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
						$captchaParams = $formDataReference['captcha'];

						if (!$captchaParams) {
							throw new UnverifiedRequestException(
								\esc_html__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
								$captchaParams
							);
						}

						$captchaToken = $captchaParams['token'] ?? '';
						$captchaAction = $captchaParams['action'] ?? '';
						$captchaIsEnterprise = $captchaParams['isEnterprise'] ?? false;

						$captcha = $this->captcha->check($captchaToken, $captchaAction, (bool) $captchaIsEnterprise);

						if ($captcha['status'] === AbstractBaseRoute::STATUS_ERROR) {
							return \rest_ensure_response($captcha);
						}
					}
					break;
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
	protected function routeGetType(): string
	{
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
	 * Returns captcha class.
	 *
	 * @return $this
	 */
	abstract protected function getCaptcha();

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
