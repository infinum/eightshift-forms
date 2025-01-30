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
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Entries\EntriesHelper;
use EightshiftForms\Entries\SettingsEntries;
use EightshiftForms\General\SettingsGeneral;
use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Hooks\FiltersOuputMock;
use EightshiftForms\Integrations\Calculator\SettingsCalculator;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Labels\LabelsInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftForms\Security\SecurityInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftForms\Validation\ValidatorInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsEncryption;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
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
	 * Instance variable of enrichment data.
	 *
	 * @var EnrichmentInterface
	 */
	protected EnrichmentInterface $enrichment;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param SecurityInterface $security Inject security methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject formSubmitMailer methods.
	 * @param EnrichmentInterface $enrichment Inject enrichment methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		SecurityInterface $security,
		FormSubmitMailerInterface $formSubmitMailer,
		EnrichmentInterface $enrichment
	) {
		$this->validator = $validator;
		$this->labels = $labels;
		$this->captcha = $captcha;
		$this->security = $security;
		$this->formSubmitMailer = $formSubmitMailer;
		$this->enrichment = $enrichment;
	}

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
		// If route is used for admin only, check if user has permission. (generally used for settings).
		if ($this->isRouteAdminProtected()) {
			$premission = $this->checkUserPermission();
			if ($premission) {
				return \rest_ensure_response($premission);
			}
		}

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
					if (!$this->getSecurity()->isRequestValid($formDetails[UtilsConfig::FD_TYPE])) {
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
						$shouldValidateCaptcha = [
							$formDetails[UtilsConfig::FD_TYPE] !== SettingsCalculator::SETTINGS_TYPE_KEY,
							!UtilsDeveloperHelper::isDeveloperSkipCaptchaActive(),
						];

						if (!\in_array(false, $shouldValidateCaptcha, true)) {
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
										'<p>' . \esc_html__('It seems like there was an issue with forms reCaptcha. Here is all the available data for debugging purposes.', 'eightshift-forms') . '</p>',
										[
											self::VALIDATION_ERROR_DATA => $captcha,
										]
									);
								}

								return \rest_ensure_response($captcha);
							}
						}
					}

					// Validate submit only logged in.
					if ($this->validator->validateSubmitOnlyLoggedIn($formDetails[UtilsConfig::FD_FORM_ID] ?? '')) {
						// Validate submit only logged in.
						return \rest_ensure_response(
							UtilsApiHelper::getApiErrorPublicOutput(
								$this->labels->getLabel('validationSubmitLoggedIn', $formDetails[UtilsConfig::FD_FORM_ID] ?? ''),
							)
						);
					} else {
						// Validate submit only once.
						if ($this->validator->validateSubmitOnlyOnce($formDetails[UtilsConfig::FD_FORM_ID] ?? '')) {
							return \rest_ensure_response(
								UtilsApiHelper::getApiErrorPublicOutput(
									$this->labels->getLabel('validationSubmitOnce', $formDetails[UtilsConfig::FD_FORM_ID] ?? ''),
								)
							);
						}
					}

					// Map enrichment data.
					$formDetails[UtilsConfig::FD_PARAMS] = $this->enrichment->mapEnrichmentFields($formDetails[UtilsConfig::FD_PARAMS]);

					// Filter params.
					$filterName = UtilsHooksHelper::getFilterName(['integrations', $formDetails[UtilsConfig::FD_TYPE], 'prePostParams']);
					if (\has_filter($filterName)) {
						$formDetails[UtilsConfig::FD_PARAMS] = \apply_filters($filterName, $formDetails[UtilsConfig::FD_PARAMS], $formDetails[UtilsConfig::FD_FORM_ID]) ?? [];
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
						self::VALIDATION_ERROR_CODE => \esc_html($e->getData()[self::VALIDATION_ERROR_CODE] ?? ''),
						self::VALIDATION_ERROR_MSG => \esc_html($e->getMessage()),
						self::VALIDATION_ERROR_OUTPUT => $e->getData()[self::VALIDATION_ERROR_OUTPUT] ?? '',
						self::VALIDATION_ERROR_DATA => $e->getData()[self::VALIDATION_ERROR_DATA] ?? '',
						'formDetails' => $formDetails,
					]
				)
			);
		}
	}

	/**
	 * Get integration common submit action
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, mixed>
	 */
	protected function getIntegrationCommonSubmitAction(array $formDetails): array
	{
		$formId = $formDetails[UtilsConfig::FD_FORM_ID] ?? '';
		$response = $formDetails[UtilsConfig::FD_RESPONSE_OUTPUT_DATA] ?? [];
		$validation = $response[UtilsConfig::IARD_VALIDATION] ?? [];
		$status = $response[UtilsConfig::IARD_STATUS] ?? UtilsConfig::STATUS_ERROR;

		$disableFallbackEmail = false;

		// Output integrations validation issues.
		if ($validation) {
			$response[UtilsConfig::IARD_VALIDATION] = $this->validator->getValidationLabelItems($validation, $formId);
			$disableFallbackEmail = true;
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

		if ($status === UtilsConfig::STATUS_SUCCESS) {
			$successAdditionalData = $this->getIntegrationResponseSuccessOutputAdditionalData($formDetails);

			// Send email if it is configured in the backend.
			if ($response[UtilsConfig::IARD_STATUS] === UtilsConfig::STATUS_SUCCESS) {
				$this->getFormSubmitMailer()->sendEmails(
					$formDetails,
					$this->getCombinedEmailResponseTags(
						$formDetails,
						\array_merge(
							$successAdditionalData['public'],
							$successAdditionalData['private'],
						)
					)
				);
			}

			// Callback functions.
			$this->callIntegrationResponseSuccessCallback($formDetails, $successAdditionalData);

			// Set validation submit once.
			$this->validator->setValidationSubmitOnce($formId);

			return UtilsApiHelper::getApiSuccessPublicOutput(
				$labelsOutput,
				\array_merge(
					$successAdditionalData['public'],
					$successAdditionalData['additional']
				),
				$response
			);
		}

		return UtilsApiHelper::getApiErrorPublicOutput(
			$labelsOutput,
			\array_merge(
				$this->getIntegrationResponseErrorOutputAdditionalData($formDetails),
				((isset($response[UtilsConfig::IARD_VALIDATION])) ? [
					UtilsHelper::getStateResponseOutputKey('validation') => $response[UtilsConfig::IARD_VALIDATION],
				] : []),
			),
			$response
		);
	}

	/**
	 * Get integration response output additional data on error or success.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, mixed>
	 */
	protected function getIntegrationResponseAnyOutputAdditionalData(array $formDetails): array
	{
		$formId = $formDetails[UtilsConfig::FD_FORM_ID] ?? '';
		$type = $formDetails[UtilsConfig::FD_TYPE] ?? '';

		$output = [];

		// Tracking event name.
		$trackingEventName = FiltersOuputMock::getTrackingEventNameFilterValue($type, $formId)['data'];
		if ($trackingEventName) {
			$output[UtilsHelper::getStateResponseOutputKey('trackingEventName')] = $trackingEventName;
		}

		// Provide additional data to tracking attr.
		$trackingAdditionalData = FiltersOuputMock::getTrackingAditionalDataFilterValue($type, $formId)['data'];
		if ($trackingAdditionalData) {
			$output[UtilsHelper::getStateResponseOutputKey('trackingAdditionalData')] = $trackingAdditionalData;
		}

		return $output;
	}

	/**
	 * Get integration response output additional data on success.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, mixed>
	 */
	protected function getIntegrationResponseSuccessOutputAdditionalData(array $formDetails): array
	{
		$formId = $formDetails[UtilsConfig::FD_FORM_ID] ?? '';
		$type = $formDetails[UtilsConfig::FD_TYPE] ?? '';

		$output = [
			'private' => [],
			'public' => [],
		];

		// Set increment and add it to the output.
		$output['private'][UtilsHelper::getStateResponseOutputKey('incrementId')] = FormsHelper::setIncrement($formId);

		// Set entries.
		$useEntries = \apply_filters(SettingsEntries::FILTER_SETTINGS_IS_VALID_NAME, $formId);
		if ($useEntries) {
			$entryId = EntriesHelper::setEntryByFormDataRef($formDetails);
			if ($entryId) {
				$output['private'][UtilsHelper::getStateResponseOutputKey('entry')] = (string) $entryId;
			}
		}

		// Hide global message on success.
		$hideGlobalMsgOnSuccess = UtilsSettingsHelper::isSettingCheckboxChecked(SettingsGeneral::SETTINGS_HIDE_GLOBAL_MSG_ON_SUCCESS_KEY, SettingsGeneral::SETTINGS_HIDE_GLOBAL_MSG_ON_SUCCESS_KEY, $formId);
		if ($hideGlobalMsgOnSuccess) {
			$output['public'][UtilsHelper::getStateResponseOutputKey('hideGlobalMsgOnSuccess')] = $hideGlobalMsgOnSuccess;
		}

		// Hide form on success.
		$hideFormOnSuccess = UtilsSettingsHelper::isSettingCheckboxChecked(SettingsGeneral::SETTINGS_HIDE_FORM_ON_SUCCESS_KEY, SettingsGeneral::SETTINGS_HIDE_FORM_ON_SUCCESS_KEY, $formId);
		if ($hideFormOnSuccess) {
			$output['public'][UtilsHelper::getStateResponseOutputKey('hideFormOnSuccess')] = $hideFormOnSuccess;
		}

		// Add success redirect variation.
		$variation = FiltersOuputMock::getVariationFilterValue($type, $formId, $formDetails)['data'];
		if ($variation) {
			$output['public'][UtilsHelper::getStateResponseOutputKey('variation')] = $variation;
		}

		// Success redirect url.
		$successRedirectUrl = FiltersOuputMock::getSuccessRedirectUrlFilterValue($type, $formId)['data'];
		if ($successRedirectUrl) {
			$redirectDataOutput = [];

			// Replace {field_name} with the actual value.
			foreach (\array_merge($formDetails[UtilsConfig::FD_PARAMS], $formDetails[UtilsConfig::FD_FILES]) as $param) {
				$name = $param['name'] ?? '';
				$value = $param['value'] ?? '';
				$type = $param['type'] ?? '';

				if ($name === UtilsHelper::getStateParam('skippedParams')) {
					continue;
				}

				if ($type === 'file') {
					$value = \array_map(
						static function (string $file) {
							$filename = \pathinfo($file, \PATHINFO_FILENAME);
							$extension = \pathinfo($file, \PATHINFO_EXTENSION);
							return "{$filename}.{$extension}";
						},
						$value
					);
				}

				if (\is_array($value)) {
					$value = \implode(', ', $value);
				}

				$successRedirectUrl = \str_replace("{" . $name . "}", (string) $value, $successRedirectUrl);
			}

			// Redirect variation.
			if (isset($output['public'][UtilsHelper::getStateResponseOutputKey('variation')])) {
				$redirectDataOutput[UtilsHelper::getStateSuccessRedirectUrlKey('variation')] = $output['public'][UtilsHelper::getStateResponseOutputKey('variation')];
			}

			// Redirect entry id.
			if (isset($output['private'][UtilsHelper::getStateResponseOutputKey('entry')])) {
				$redirectDataOutput[UtilsHelper::getStateSuccessRedirectUrlKey('entry')] = $output['private'][UtilsHelper::getStateResponseOutputKey('entry')];
			}

			// Redirect secure data.
			if ($formDetails[UtilsConfig::FD_SECURE_DATA]) {
				$secureData = \json_decode(UtilsEncryption::decryptor($formDetails[UtilsConfig::FD_SECURE_DATA]) ?: '', true);

				// Legacy data.
				$redirectDataOutput['es-legacy'] = $this->processLegacyData($secureData['l'] ?? [], $formDetails[UtilsConfig::FD_PARAMS_RAW], $formId);

				// Redirect custom result output feature.
				$formsUseCustomResultOutputFeatureFilterName = UtilsHooksHelper::getFilterName(['block', 'forms', 'useCustomResultOutputFeature']);
				if (\apply_filters($formsUseCustomResultOutputFeatureFilterName, false)) {
					$redirectDataOutput[UtilsHelper::getStateSuccessRedirectUrlKey('customResultOutput')] = $this->processCustomResultOutputData($secureData, $formDetails);
				}
			} else {
				// Legacy data.
				$legacyVariationData = UtilsSettingsHelper::getSettingValue(SettingsGeneral::SETTINGS_GENERAL_SUCCESS_REDIRECT_VARIATION_KEY, $formId);
				if ($legacyVariationData) {
					$redirectDataOutput['es-legacy']['v'] = $legacyVariationData;
				}
			}

			// Redirect base url.
			$output['public'][UtilsHelper::getStateResponseOutputKey('successRedirectBaseUrl')] = $successRedirectUrl;

			// Redirect full url.
			$output['public'][UtilsHelper::getStateResponseOutputKey('successRedirectUrl')] = \add_query_arg(
				$redirectDataOutput ? [
					UtilsHelper::getStateSuccessRedirectUrlKey('data') => UtilsEncryption::encryptor(\wp_json_encode($redirectDataOutput)),
				] : [],
				$successRedirectUrl
			);
		}

		// Update created entry with additional values.
		if ($useEntries && isset($output['private'][UtilsHelper::getStateResponseOutputKey('entry')])) {
			$entryData = EntriesHelper::getEntry($output['private'][UtilsHelper::getStateResponseOutputKey('entry')]);

			if ($entryData) {
				$entryNewData = $entryData['entryValue'] ?? [];
				if (
					UtilsSettingsHelper::isSettingCheckboxChecked(SettingsEntries::SETTINGS_ENTRIES_SAVE_ADDITONAL_VALUES_REDIRECT_URL_KEY, SettingsEntries::SETTINGS_ENTRIES_SAVE_ADDITONAL_VALUES_KEY, $formId) &&
					isset($output['public'][UtilsHelper::getStateResponseOutputKey('successRedirectUrl')])
				) {
					$entryNewData[UtilsHelper::getStateResponseOutputKey('successRedirectUrl')] = $output['public'][UtilsHelper::getStateResponseOutputKey('successRedirectUrl')];
				}

				if (
					UtilsSettingsHelper::isSettingCheckboxChecked(SettingsEntries::SETTINGS_ENTRIES_SAVE_ADDITONAL_VALUES_VARIATIONS_KEY, SettingsEntries::SETTINGS_ENTRIES_SAVE_ADDITONAL_VALUES_KEY, $formId) &&
					isset($output['public'][UtilsHelper::getStateResponseOutputKey('variation')])
				) {
					$entryNewData[UtilsHelper::getStateResponseOutputKey('variation')] = $output['public'][UtilsHelper::getStateResponseOutputKey('variation')];
				}

				if (
					UtilsSettingsHelper::isSettingCheckboxChecked(SettingsEntries::SETTINGS_ENTRIES_SAVE_ADDITONAL_VALUES_INCREMENT_ID_KEY, SettingsEntries::SETTINGS_ENTRIES_SAVE_ADDITONAL_VALUES_KEY, $formId) &&
					isset($output['private'][UtilsHelper::getStateResponseOutputKey('incrementId')])
				) {
					$entryNewData[UtilsHelper::getStateResponseOutputKey('incrementId')] = $output['private'][UtilsHelper::getStateResponseOutputKey('incrementId')];
				}

				EntriesHelper::updateEntry($entryNewData, $output['private'][UtilsHelper::getStateResponseOutputKey('entry')]);
			}
		}

		// Add form ID to the output.
		$output['private'][UtilsHelper::getStateResponseOutputKey('formId')] = $formId;

		$finalOutput = [
			'private' => $output['private'],
			'public' => $output['public'],
			'additional' => $this->getIntegrationResponseAnyOutputAdditionalData($formDetails),
		];

		// Filter params.
		$filterName = UtilsHooksHelper::getFilterName(['integrations', $type, 'beforeSuccessResponse']);
		if (\has_filter($filterName)) {
			return \apply_filters($filterName, $finalOutput, $formDetails, $formId);
		}

		return $finalOutput;
	}

	/**
	 * Get integration response output additional data on error.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, mixed>
	 */
	protected function getIntegrationResponseErrorOutputAdditionalData(array $formDetails): array
	{
		return $this->getIntegrationResponseAnyOutputAdditionalData($formDetails);
	}

	/**
	 * Call integration response success callback.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 * @param array<string, mixed> $successAdditionalData Data passed from the `getIntegrationResponseSuccessOutputAdditionalData` function.
	 *
	 * @return void
	 */
	protected function callIntegrationResponseSuccessCallback(array $formDetails, array $successAdditionalData): void
	{
		return;
	}

	/**
	 * Prepare email response tags from the API response.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, string>
	 */
	protected function getEmailResponseTags(array $formDetails): array
	{
		return [];
	}

	/**
	 * Prepare all email response tags.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 * @param array<string, mixed> $data Data passed from the `getIntegrationResponseSuccessOutputAdditionalData` function.
	 *
	 * @return array<string, mixed>
	 */
	protected function getCombinedEmailResponseTags(array $formDetails, array $data): array
	{
		return \array_merge(
			$this->getCommonEmailResponseTags($data, $formDetails),
			$this->getEmailResponseTags($formDetails)
		);
	}

	/**
	 * Prepare all email response tags.
	 *
	 * @param array<string, mixed> $data Data passed from the `getIntegrationResponseSuccessOutputAdditionalData` function.
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, mixed>
	 */
	protected function getCommonEmailResponseTags(array $data, array $formDetails): array
	{
		$output = [];

		$allowedTags = \apply_filters(UtilsConfig::FILTER_SETTINGS_DATA, [])[SettingsMailer::SETTINGS_TYPE_KEY]['emailTemplateTags'] ?? [];

		foreach ($allowedTags as $key => $value) {
			switch ($key) {
				case 'mailerSuccessRedirectUrl':
					$output[$key] = $data[UtilsHelper::getStateResponseOutputKey('successRedirectUrl')] ?? '';
					break;
				case 'mailerEntryId':
					$output[$key] = $data[UtilsHelper::getStateResponseOutputKey('entry')] ?? '';
					break;
				case 'mailerPostUrl':
					$output[$key] = \get_the_permalink((int) $formDetails['postId']);
					break;
				case 'mailerPostTitle':
					$output[$key] = \get_the_title((int) $formDetails['postId']);
					break;
				case 'mailerPostId':
					$output[$key] = $formDetails['postId'] ?? '';
					break;
				case 'mailerFormId':
					$output[$key] = $formDetails[UtilsHelper::getStateResponseOutputKey('formId')];
					break;
				case 'mailerFormTitle':
					$output[$key] = \get_the_title((int) $data[UtilsHelper::getStateResponseOutputKey('formId')]);
					break;
				case 'mailerTimestamp':
					$output[$key] = \current_datetime()->format('YmdHis');
					break;
				case 'mailerTimestampHuman':
					$output[$key] = \current_datetime()->format('Y-m-d H:i:s');
					break;
				case 'mailerIncrementId':
					$output[$key] = $data[UtilsHelper::getStateResponseOutputKey('incrementId')] ?? '';
					break;
				case 'mailerEntryUrl':
					$entryId = $data[UtilsHelper::getStateResponseOutputKey('entry')] ?? '';
					$formId = $data[UtilsHelper::getStateResponseOutputKey('formId')] ?? '';

					if ($entryId && $formId) {
						$output[$key] = EntriesHelper::getEntryAdminUrl($entryId, $formId);
					}
					break;
			}
		}

		return $output;
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
	 * Returns enrichment class.
	 *
	 * @return EnrichmentInterface
	 */
	protected function getEnrichment()
	{
		return $this->enrichment;
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
	 * Check if the route is admin protected.
	 *
	 * @return boolean
	 */
	protected function isRouteAdminProtected(): bool
	{
		return false;
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return mixed
	 */
	abstract protected function submitAction(array $formDetails);

	/**
	 * Process legacy data.
	 *
	 * @param array<string, mixed> $data Data from secure data.
	 * @param array<string, mixed> $params Raw params.
	 * @param string $formId Form ID.
	 *
	 * @return array<string, mixed>
	 */
	private function processLegacyData(array $data, array $params, string $formId): array
	{
		$downloads = $data['d'] ?? [];

		$output = [];

		foreach ($downloads as $download) {
			$condition = $download['c'] ?? '';

			// If empty use the download.
			if (!$condition || $condition === 'all') {
				$output[] = $download;
				continue;
			}

			$condition = \explode('=', $condition);

			$fieldName = $condition[0] ?? '';
			$fieldValue = $condition[1] ?? '';

			// If condition is not valid use the download.
			if (!$fieldName || !$fieldValue) {
				$output[] = $download;
				continue;
			}

			// If field condition is met use the download.
			if (isset($params[$fieldName]) && $params[$fieldName] === $fieldValue) {
				$output[] = $download;
				continue;
			}
		}

		if ($output) {
			$data['d'] = $output;
		}

		if (!isset($data['v'])) {
			$data['v'] = UtilsSettingsHelper::getSettingValue(SettingsGeneral::SETTINGS_GENERAL_SUCCESS_REDIRECT_VARIATION_KEY, $formId);
		}

		return $data;
	}

	/**
	 * Process custom result output data.
	 *
	 * @param array<string, mixed> $data Data from secure data.
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, mixed>
	 */
	private function processCustomResultOutputData(array $data, array $formDetails): array
	{
		$params = $formDetails[UtilsConfig::FD_PARAMS_RAW] ?? [];
		$formId = $formDetails[UtilsConfig::FD_FORM_ID] ?? '';
		$type = $formDetails[UtilsConfig::FD_TYPE] ?? '';

		$output = [];

		// Output title.
		if (isset($data['t'])) {
			$output['t'] = $data['t'];
		}

		// Output subtitle.
		if (isset($data['st'])) {
			$output['st'] = $data['st'];
		}

		// Output files.
		$files = $data['d'] ?? [];
		if ($files) {
			$outputFiles = [];
			foreach ($files as $file) {
				$fieldName = $file['cfn'] ?? '';
				$fieldValue = $file['cfv'] ?? '';

				unset($file['cfn'], $file['cfv']);

				// If empty use the file.
				if (!$fieldName || !$fieldValue) {
					$outputFiles[] = $file;
					continue;
				}

				// If field condition is met use the file.
				if (isset($params[$fieldName]) && $params[$fieldName] === $fieldValue) {
					$outputFiles[] = $file;
					continue;
				}
			}

			$output['d'] = $outputFiles;
		}

		$filterName = UtilsHooksHelper::getFilterName(['integrations', $type, 'afterCustomResultOutputProcess']);
		if (\has_filter($filterName)) {
			return \apply_filters($filterName, $output, $formDetails, $formId);
		}

		return $output;
	}
}
