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
					[]
				);
			}

			// Validate allowed number of requests.
			if ($this->routeGetType() !== self::ROUTE_TYPE_SETTINGS) {
				if (!$this->getSecurity()->isRequestValid()) {
					throw new UnverifiedRequestException(
						$this->getValidatorLabels()->getLabel('validationSecurity'),
						[]
					);
				}
			}

			switch ($this->routeGetType()) {
				case self::ROUTE_TYPE_FILE:
					// Validate files.
					if (!UtilsDeveloperHelper::isDeveloperSkipFormValidationActive()) {
						$validate = $this->getValidator()->validateFiles($formDetails);

						if ($validate) {
							throw new UnverifiedRequestException(
								\esc_html__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
								$validate
							);
						}
					}

					$uploadFile = UtilsUploadHelper::uploadFile($formDetails[UtilsConfig::FD_FILES_UPLOAD]);
					$uploadError = $uploadFile['errorOutput'] ?? '';
					$uploadFileId = $formDetails[UtilsConfig::FD_FILES_UPLOAD]['id'] ?? '';

					// Upload files to temp folder.
					$formDetails[UtilsConfig::FD_FILES_UPLOAD] = $uploadFile;

					if (UtilsUploadHelper::isUploadError($uploadError)) {
						throw new UnverifiedRequestException(
							\esc_html__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
							[
								$uploadFileId => $this->getValidatorLabels()->getLabel('validationFileUpload'),
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
							$validate
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
								$validate
							);
						}
					}
					break;
				default:
					// Skip any validation if direct import.
					if (isset($formDetails[UtilsConfig::FD_DIRECT_IMPORT])) {
						break;
					}

					// Validate params.
					if (!UtilsDeveloperHelper::isDeveloperSkipFormValidationActive()) {
						$validate = $this->getValidator()->validateParams($formDetails);

						if ($validate) {
							throw new UnverifiedRequestException(
								\esc_html__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
								$validate
							);
						}
					}

					// Validate captcha.
					if (\apply_filters(SettingsCaptcha::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
						$captchaParams = $formDetails[UtilsConfig::FD_CAPTCHA] ?? [];

						if (!$captchaParams) {
							throw new UnverifiedRequestException(
								\esc_html__('Missing one or more required parameters to process the request.', 'eightshift-forms'),
								$captchaParams
							);
						}

						$captcha = $this->getCaptcha()->check(
							$captchaParams['token'] ?? '',
							$captchaParams['action'] ?? '',
							(bool) $captchaParams['isEnterprise'] ?: false // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
						);

						if ($captcha['status'] === UtilsConfig::STATUS_ERROR) {
							return \rest_ensure_response($captcha);
						}
					}
					break;
			}

			// Do Action.
			return $this->submitAction($formDetails);
		} catch (UnverifiedRequestException $e) {
			// Die if any of the validation fails.
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorOutput(
					$e->getMessage(),
					[
						UtilsHelper::getStateResponseOutputKey('validation') => $e->getData(),
					],
					[
						'exception' => $e,
						'request' => $request,
						'formDetails' => $formDetails,
					]
				)
			);
		}
	}

	/**
	 * Get integration common submit action
	 *
	 * @param array<string, mixed> $response Response data.
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 * @param string $formId Form ID.
	 * @param mixed $callbackAdditional Additional callback.
	 *
	 * @return array<string, mixed>
	 */
	protected function getIntegrationCommonSubmitAction(
		array $response,
		array $formDetails,
		string $formId,
		$callbackAdditional = null
	): array {
		$validation = $response[UtilsHelper::getStateResponseOutputKey('validation')] ?? [];
		$disableFallbackEmail = false;

		$postId = $formDetails[UtilsConfig::FD_POST_ID];
		$additionaDataOutput = [];

		// Pre response filter for addon data.
		$filterName = UtilsHooksHelper::getFilterName(['block', 'form', 'preResponseAddonData']);
		if (\has_filter($filterName)) {
			$additionaDataOutput[UtilsHelper::getStateResponseOutputKey('addon')] = \apply_filters($filterName, $formDetails[UtilsConfig::FD_ADDON_DATA], $formDetails);
		}

		// Pre response filter for success redirect data.
		$filterName = UtilsHooksHelper::getFilterName(['block', 'form', 'preResponseSuccessRedirectData']);
		if (\has_filter($filterName)) {
			$additionaDataOutput[UtilsHelper::getStateResponseOutputKey('successRedirectData')] = UtilsEncryption::encryptor(\wp_json_encode(\apply_filters($filterName, [], $formDetails)));
		}

		// Output integrations validation issues.
		if ($validation) {
			$response[UtilsHelper::getStateResponseOutputKey('validation')] = $this->validator->getValidationLabelItems($validation, $formId);
			$disableFallbackEmail = true;
		}

		// Run any function callback if it is set.
		if (\is_callable($callbackAdditional)) {
			\call_user_func($callbackAdditional);
		}

		// Skip fallback email if integration is disabled.
		if (!$response['isDisabled'] && $response['status'] === UtilsConfig::STATUS_ERROR) {
			// Prevent fallback email if we have validation errors parsed.
			if (!$disableFallbackEmail) {
				// Send fallback email.
				$this->getFormSubmitMailer()->sendFallbackEmail(
					\array_merge(
						$response,
						[
							// Attach postID to the response because it is not available in the client's response.
							'postId' => $postId,
						]
					)
				);
			}
		}

		// Send email if it is configured in the backend.
		if ($response['status'] === UtilsConfig::STATUS_SUCCESS) {
			$this->getFormSubmitMailer()->sendEmails($formDetails, $additionaDataOutput);
		}

		$labelsOutput = $this->labels->getLabel($response['message'], $formId);
		$responseOutput = $response;

		// Output fake success and send fallback email.
		if ($response['isDisabled'] && !$validation) {
			$this->getFormSubmitMailer()->sendFallbackEmail(
				\array_merge(
					$response,
					[
						// Attach postID to the response because it is not available in the client's response.
						'postId' => $postId,
					]
				)
			);

			$fakeResponse = UtilsApiHelper::getIntegrationApiSuccessOutput($response);

			$labelsOutput = $this->labels->getLabel($fakeResponse['message'], $formId);
			$responseOutput = $fakeResponse;
		}

		// Save entries to DB.
		if (\apply_filters(SettingsEntries::FILTER_SETTINGS_IS_VALID_NAME, $formId)) {
			EntriesHelper::setEntryByFormDataRef($formDetails, $formId);
		}

		return UtilsApiHelper::getIntegrationApiOutput(
			\array_merge($responseOutput, $additionaDataOutput),
			$labelsOutput
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
