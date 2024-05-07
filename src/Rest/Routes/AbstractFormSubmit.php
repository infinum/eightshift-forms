<?php

/**
 * The class register route for public/admin form submiting endpoint
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsUploadHelper;
use EightshiftForms\Captcha\SettingsCaptcha;
use EightshiftForms\Captcha\CaptchaInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftForms\Entries\EntriesHelper;
use EightshiftForms\Entries\SettingsEntries;
use EightshiftForms\Labels\LabelsInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftForms\Security\SecurityInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftForms\Validation\ValidationPatternsInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftForms\Validation\ValidatorInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsEncryption;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Rest\Routes\AbstractUtilsBaseRoute;
use WP_REST_Request;

/**
 * Class AbstractFormSubmit
 */
abstract class AbstractFormSubmit extends AbstractUtilsBaseRoute
{
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
	 * Instance variable of FormSubmitMailerInterface data.
	 *
	 * @var FormSubmitMailerInterface
	 */
	public $formSubmitMailer;

	/**
	 * Instance variable of LabelsInterface data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Instance variable of CaptchaInterface data.
	 *
	 * @var CaptchaInterface
	 */
	protected $captcha;

	/**
	 * Instance variable of SecurityInterface data.
	 *
	 * @var SecurityInterface
	 */
	protected $security;

	/**
	 * Route types.
	 */
	protected const ROUTE_TYPE_DEFAULT = 'default';
	protected const ROUTE_TYPE_FILE = 'file';
	protected const ROUTE_TYPE_SETTINGS = 'settings';
	protected const ROUTE_TYPE_STEP_VALIDATION = 'step-validation';

	/**
	 * Validation errors constants.
	 *
	 * @var string
	 */
	protected const VALIDATION_ERROR_SEND_FALLBACK = 'validationErrorSendFallback';
	protected const VALIDATION_ERROR_CODE = 'validationErrorCode';
	protected const VALIDATION_ERROR_DATA = 'validationErrorData';
	protected const VALIDATION_ERROR_MSG = 'validationErrorMsg';
	protected const VALIDATION_ERROR_OUTPUT = 'validationOutput';
	protected const VALIDATION_ERROR_IS_SPAM = 'validationIsSpam';


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
			$formDetails = $this->getFormDetailsApi($request);

			// In case the form has missing itemId, type, formId, etc it is not configured correctly or it could be a unauthorized request.
			if (!$this->getValidator()->validateFormManadatoryProperies($formDetails)) {
				throw new UnverifiedRequestException(
					$this->getValidatorLabels()->getLabel('validationMissingMandatoryParams'),
					[
						self::VALIDATION_ERROR_SEND_FALLBACK => true,
						self::VALIDATION_ERROR_CODE => 'validationMissingMandatoryParams',
					]
				);
			}

			switch ($this->routeGetType()) {
				case self::ROUTE_TYPE_FILE:
					// Validate files.
					if (!UtilsDeveloperHelper::isDeveloperSkipFormValidationActive()) {
						$validate = $this->getValidator()->validateFiles($formDetails);

						if ($validate) {
							throw new UnverifiedRequestException(
								\esc_html__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
								[
									self::VALIDATION_ERROR_OUTPUT => $validate,
									self::VALIDATION_ERROR_CODE => 'validationFileUploadMissingRequiredParams',
								]
							);
						}
					}

					$uploadFile = UtilsUploadHelper::uploadFile($formDetails[UtilsConfig::FD_FILES_UPLOAD]);
					$uploadError = $uploadFile['errorOutput'] ?? '';
					$uploadFileId = $formDetails[UtilsConfig::FD_FILES_UPLOAD]['id'] ?? '';

					// Upload files to temp folder.
					$formDetails[UtilsConfig::FD_FILES_UPLOAD] = $uploadFile;

					$isUploadError = UtilsUploadHelper::isUploadError($uploadError);

					if ($isUploadError) {
						throw new UnverifiedRequestException(
							\esc_html__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
							[
								self::VALIDATION_ERROR_OUTPUT => [
									$uploadFileId => $this->getValidatorLabels()->getLabel('validationFileUpload'),
								],
								self::VALIDATION_ERROR_SEND_FALLBACK => true,
								self::VALIDATION_ERROR_CODE => 'validationFileUploadProcessError',
							]
						);
					}
					break;
				case self::ROUTE_TYPE_SETTINGS:
					// Validate params.
					$validate = $this->getValidator()->validateParams($formDetails, false);

					if ($validate) {
						throw new UnverifiedRequestException(
							\esc_html__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
							[
								self::VALIDATION_ERROR_OUTPUT => $validate,
								self::VALIDATION_ERROR_CODE => 'validationSettingsMissingRequiredParams',
							]
						);
					}
					break;
				case self::ROUTE_TYPE_STEP_VALIDATION:
					// Validate params.
					if (!UtilsDeveloperHelper::isDeveloperSkipFormValidationActive()) {
						$validate = $this->getValidator()->validateParams($formDetails, false);

						if ($validate) {
							throw new UnverifiedRequestException(
								\esc_html__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
								[
									self::VALIDATION_ERROR_OUTPUT => $validate,
									self::VALIDATION_ERROR_CODE => 'validationStepMissingRequiredParams',
								]
							);
						}
					}
					break;
				default:
					// Skip any validation if direct import.
					if (isset($formDetails[UtilsConfig::FD_DIRECT_IMPORT])) {
						break;
					}

					// Validate allowed number of requests.
					// We don't want to limit any custom requests like files, settings, steps, etc.
					if (!$this->getSecurity()->isRequestValid()) {
						throw new UnverifiedRequestException(
							$this->getValidatorLabels()->getLabel('validationSecurity'),
							[
								self::VALIDATION_ERROR_SEND_FALLBACK => true,
								self::VALIDATION_ERROR_CODE => 'validationSecurity',
							]
						);
					}

					// Validate params.
					if (!UtilsDeveloperHelper::isDeveloperSkipFormValidationActive()) {
						$validate = $this->getValidator()->validateParams($formDetails);

						if ($validate) {
							throw new UnverifiedRequestException(
								\esc_html__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
								[
									self::VALIDATION_ERROR_OUTPUT => $validate,
									self::VALIDATION_ERROR_CODE => 'validationDefaultMissingRequiredParams',
								]
							);
						}
					}

					// Validate captcha.
					if (\apply_filters(SettingsCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
						$captchaParams = $formDetails[UtilsConfig::FD_CAPTCHA] ?? [];

						if (!$captchaParams) {
							throw new UnverifiedRequestException(
								\esc_html__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
								[
									self::VALIDATION_ERROR_OUTPUT => $captchaParams,
									self::VALIDATION_ERROR_CODE => 'validationDefaultCaptcha',
								]
							);
						}

						$captcha = $this->getCaptcha()->check(
							$captchaParams['token'],
							$captchaParams['action'],
							$captchaParams['isEnterprise'] === 'true'
						);

						if ($captcha['status'] === UtilsConfig::STATUS_ERROR) {
							$isSpam = $captcha['data']['isSpam'] ?? false;

							if (!$isSpam) {
								// Send fallback email if there is an issue with reCaptcha.
								$this->getFormSubmitMailer()->sendFallbackProcessingEmail(
									$formDetails,
									// translators: %s is the form ID.
									\sprintf(\__('reCaptcha error form: %s', 'eightshift-forms'), $formDetails[UtilsConfig::FD_FORM_ID] ?? ''),
									'<p>' . \esc_html__('It seems like there was an issue with forms reCaptcha. Here is all the data for debugging purposes.', 'eightshift-forms') . '</p>',
									[
										self::VALIDATION_ERROR_DATA => $captcha,
									]
								);
							}

							return \rest_ensure_response($captcha);
						}
					}
					break;
			}

			// Do Action.
			return $this->submitAction($formDetails);
		} catch (UnverifiedRequestException $e) {
			if (isset($e->getData()[self::VALIDATION_ERROR_SEND_FALLBACK]) && $e->getData()[self::VALIDATION_ERROR_SEND_FALLBACK]) {
				// Send fallback email.
				$this->getFormSubmitMailer()->sendFallbackProcessingEmail(
					$formDetails,
					'',
					'',
					[
						self::VALIDATION_ERROR_CODE => \esc_html($e->getData()[self::VALIDATION_ERROR_CODE] ?? ''),
						self::VALIDATION_ERROR_MSG => \esc_html($e->getMessage()),
						self::VALIDATION_ERROR_OUTPUT => $e->getData()[self::VALIDATION_ERROR_OUTPUT] ?? '',
						self::VALIDATION_ERROR_DATA => $e->getData()[self::VALIDATION_ERROR_DATA] ?? '',
					]
				);
			}

			// Die if any of the validation fails.
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					$e->getMessage(),
					[
						UtilsHelper::getStateResponseOutputKey('validation') => $e->getData()[self::VALIDATION_ERROR_OUTPUT] ?? [],
					],
					[
						'exception' => $e,
						'request' => $request,
						'formDetails' => $formDetails,
						self::VALIDATION_ERROR_CODE => \esc_html($e->getData()[self::VALIDATION_ERROR_CODE] ?? ''),
						self::VALIDATION_ERROR_MSG => \esc_html($e->getMessage()),
						self::VALIDATION_ERROR_OUTPUT => $e->getData()[self::VALIDATION_ERROR_OUTPUT] ?? '',
						self::VALIDATION_ERROR_DATA => $e->getData()[self::VALIDATION_ERROR_DATA] ?? '',
					]
				)
			);
		}
	}

	/**
	 * Get integration common submit action
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 * @param mixed $callbackStart Callback start of the function.
	 *
	 * @return array<string, mixed>
	 */
	protected function getIntegrationCommonSubmitAction(array $formDetails, $callbackStart = null): array
	{
		// Pre response filter for addon data.
		$filterName = UtilsHooksHelper::getFilterName(['block', 'form', 'preResponseAddonData']);
		if (\has_filter($filterName)) {
			$filterDetails = \apply_filters($filterName, [], $formDetails);

			if ($filterDetails) {
				$formDetails[UtilsConfig::FD_ADDON] = $filterDetails;
			}
		}

		$formId = $formDetails[UtilsConfig::FD_FORM_ID] ?? '';
		$response = $formDetails[UtilsConfig::FD_RESPONSE_OUTPUT_DATA] ?? [];
		$validation = $response[UtilsConfig::IARD_VALIDATION] ?? [];

		$disableFallbackEmail = false;

		// Output integrations validation issues.
		if ($validation) {
			$response[UtilsConfig::IARD_VALIDATION] = $this->validator->getValidationLabelItems($validation, $formId);
			$disableFallbackEmail = true;
		}

		// Run any function callback if it is set.
		if (\is_callable($callbackStart)) {
			\call_user_func($callbackStart);
		}

		// Skip fallback email if integration is disabled.
		if (!$response[UtilsConfig::IARD_IS_DISABLED] && $response[UtilsConfig::IARD_STATUS] === UtilsConfig::STATUS_ERROR) {
			// Prevent fallback email if we have validation errors parsed.
			if (!$disableFallbackEmail) {
				// Send fallback email.
				$this->getFormSubmitMailer()->sendFallbackIntegrationEmail($formDetails);
			}
		}

		$labelsOutput = $this->labels->getLabel($response[UtilsConfig::IARD_MSG], $formId);
		$responseOutput = $response;

		// Output fake success and send fallback email.
		if ($response[UtilsConfig::IARD_IS_DISABLED] && !$validation) {
			$this->getFormSubmitMailer()->sendFallbackIntegrationEmail($formDetails);

			$fakeResponse = UtilsApiHelper::getIntegrationSuccessInternalOutput($response);

			$labelsOutput = $this->labels->getLabel($fakeResponse[UtilsConfig::IARD_MSG], $formId);
			$responseOutput = $fakeResponse;
		}

		$formDetails[UtilsConfig::FD_RESPONSE_OUTPUT_DATA] = $responseOutput;

		return $this->getIntegrationCommonSubmitOutput(
			$formDetails,
			$labelsOutput
		);
	}

	/**
	 * Output for getIntegrationCommonSubmitOutput method.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 * @param string $msg Message to output.
	 *
	 * @return array<string, array<mixed>|int|string>
	 */
	protected function getIntegrationCommonSubmitOutput(array $formDetails, string $msg): array
	{
		$response = $formDetails[UtilsConfig::FD_RESPONSE_OUTPUT_DATA] ?? [];
		$status = $response[UtilsConfig::IARD_STATUS] ?? UtilsConfig::STATUS_ERROR;
		$formId = $formDetails[UtilsConfig::FD_FORM_ID] ?? '';

		$additionalOutput = [];

		if (isset($response[UtilsConfig::IARD_VALIDATION])) {
			$additionalOutput[UtilsHelper::getStateResponseOutputKey('validation')] = $response[UtilsConfig::IARD_VALIDATION];
		}

		if ($status === UtilsConfig::STATUS_SUCCESS) {
			// Order of this filter is important as you can use filters in the getApiPublicAdditionalDataOutput helper.
			if (\apply_filters(SettingsEntries::FILTER_SETTINGS_IS_VALID_NAME, $formId)) {
				$entryId = EntriesHelper::setEntryByFormDataRef($formDetails);
				$formDetails[UtilsConfig::FD_ENTRY_ID] = $entryId ? (string) $entryId : '';
			}

			// Pre response filter for success redirect data.
			$filterName = UtilsHooksHelper::getFilterName(['block', 'form', 'preResponseSuccessRedirectData']);
			if (\has_filter($filterName)) {
				$filterDetails = \apply_filters($filterName, [], $formDetails);

				if ($filterDetails) {
					$formDetails[UtilsConfig::FD_SUCCESS_REDIRECT] = UtilsEncryption::encryptor(\wp_json_encode($filterDetails));
				}
			}

			// Send email if it is configured in the backend.
			if ($response[UtilsConfig::IARD_STATUS] === UtilsConfig::STATUS_SUCCESS) {
				$this->getFormSubmitMailer()->sendEmails($formDetails);
			}

			// Return result output items as a response key.
			$filterName = UtilsHooksHelper::getFilterName(['block', 'form', 'resultOutputItems']);
			if (\has_filter($filterName)) {
				$additionalOutput[UtilsHelper::getStateResponseOutputKey('resultOutputItems')] = \apply_filters($filterName, [], $formDetails, $formId) ?? [];
			}

			// Output result output parts as a response key.
			$filterName = UtilsHooksHelper::getFilterName(['block', 'form', 'resultOutputParts']);
			if (\has_filter($filterName)) {
				$additionalOutput[UtilsHelper::getStateResponseOutputKey('resultOutputParts')] = \apply_filters($filterName, [], $formDetails, $formId) ?? [];
			}

			$additionalOutput = \array_merge(
				$additionalOutput,
				UtilsApiHelper::getApiPublicAdditionalDataOutput($formDetails)
			);

			return UtilsApiHelper::getApiSuccessPublicOutput(
				$msg,
				$additionalOutput,
				$response
			);
		}

		return UtilsApiHelper::getApiErrorPublicOutput(
			$msg,
			$additionalOutput,
			$response
		);
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
	 * @return ValidatorInterface
	 */
	protected function getValidator()
	{
		return $this->validator;
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
	 * Returns validator labels class.
	 *
	 * @return LabelsInterface
	 */
	protected function getValidatorLabels()
	{
		return $this->labels;
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
	 * Returns security class.
	 *
	 * @return SecurityInterface
	 */
	protected function getSecurity()
	{
		return $this->security;
	}

	/**
	 * Returns securicty class.
	 *
	 * @return FormSubmitMailerInterface
	 */
	protected function getFormSubmitMailer()
	{
		return $this->formSubmitMailer;
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return mixed
	 */
	abstract protected function submitAction(array $formDetails);
}
