<?php

/**
 * The class register route for public/admin form submitting endpoint
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Helpers\UploadHelpers;
use EightshiftForms\Captcha\SettingsCaptcha;
use EightshiftForms\Captcha\CaptchaInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Entries\EntriesHelper;
use EightshiftForms\Entries\SettingsEntries;
use EightshiftForms\General\SettingsGeneral;
use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Hooks\FiltersOutputMock;
use EightshiftForms\Integrations\Calculator\SettingsCalculator;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Labels\LabelsInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftForms\Security\SecurityInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftForms\Validation\ValidatorInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\DeveloperHelpers;
use EightshiftForms\Helpers\EncryptionHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Helpers\HooksHelpers;
use EightshiftForms\Helpers\SettingsHelpers;
use WP_REST_Request;

/**
 * Class AbstractFormSubmit
 */
abstract class AbstractFormSubmit extends AbstractBaseRoute
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
			$permission = $this->checkUserPermission();
			if ($permission) {
				return \rest_ensure_response($permission);
			}
		}

		// Prepare all data.
		$formDetails = $this->getFormDetailsApi($request);

		// Try catch request.
		try {
			// In case the form has missing itemId, type, formId, etc it is not configured correctly or it could be a unauthorized request.
			if (!$this->getValidator()->validateFormMandatoryProperties($formDetails)) {
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
					if (!DeveloperHelpers::isDeveloperSkipFormValidationActive()) {
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

					$uploadFile = UploadHelpers::uploadFile($formDetails[Config::FD_FILES_UPLOAD]);
					$uploadError = $uploadFile['errorOutput'] ?? '';
					$uploadFileId = $formDetails[Config::FD_FILES_UPLOAD]['id'] ?? '';

					// Upload files to temp folder.
					$formDetails[Config::FD_FILES_UPLOAD] = $uploadFile;

					$isUploadError = UploadHelpers::isUploadError($uploadError);

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
					if (!DeveloperHelpers::isDeveloperSkipFormValidationActive()) {
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
					if (isset($formDetails[Config::FD_DIRECT_IMPORT])) {
						break;
					}

					// Validate allowed number of requests.
					// We don't want to limit any custom requests like files, settings, steps, etc.
					if (!$this->getSecurity()->isRequestValid($formDetails[Config::FD_TYPE])) {
						throw new UnverifiedRequestException(
							$this->getValidatorLabels()->getLabel('validationSecurity'),
							[
								self::VALIDATION_ERROR_SEND_FALLBACK => true,
								self::VALIDATION_ERROR_CODE => 'validationSecurity',
							]
						);
					}

					// Validate params.
					if (!DeveloperHelpers::isDeveloperSkipFormValidationActive()) {
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
						if (
							$formDetails[Config::FD_TYPE] !== SettingsCalculator::SETTINGS_TYPE_KEY &&
							!DeveloperHelpers::isDeveloperSkipCaptchaActive()
						) {
							$captchaParams = $formDetails[Config::FD_CAPTCHA] ?? [];

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

							if ($captcha['status'] === Config::STATUS_ERROR) {
								$isSpam = $captcha['data']['isSpam'] ?? false;

								if (!$isSpam) {
									// Send fallback email if there is an issue with reCaptcha.
									$this->getFormSubmitMailer()->sendFallbackProcessingEmail(
										$formDetails,
										// translators: %s is the form ID.
										\sprintf(\__('reCaptcha error form: %s', 'eightshift-forms'), $formDetails[Config::FD_FORM_ID] ?? ''),
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
					if ($this->validator->validateSubmitOnlyLoggedIn($formDetails[Config::FD_FORM_ID] ?? '')) {
						// Validate submit only logged in.
						return \rest_ensure_response(
							ApiHelpers::getApiErrorPublicOutput(
								$this->labels->getLabel('validationSubmitLoggedIn', $formDetails[Config::FD_FORM_ID] ?? ''),
							)
						);
					} else {
						// Validate submit only once.
						if ($this->validator->validateSubmitOnlyOnce($formDetails[Config::FD_FORM_ID] ?? '')) {
							return \rest_ensure_response(
								ApiHelpers::getApiErrorPublicOutput(
									$this->labels->getLabel('validationSubmitOnce', $formDetails[Config::FD_FORM_ID] ?? ''),
								)
							);
						}
					}

					// Map enrichment data.
					$formDetails[Config::FD_PARAMS] = $this->enrichment->mapEnrichmentFields($formDetails[Config::FD_PARAMS]);

					// Filter params.
					$filterName = HooksHelpers::getFilterName(['integrations', $formDetails[Config::FD_TYPE], 'prePostParams']);
					if (\has_filter($filterName)) {
						$formDetails[Config::FD_PARAMS] = \apply_filters($filterName, $formDetails[Config::FD_PARAMS], $formDetails[Config::FD_FORM_ID]) ?? [];
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
				ApiHelpers::getApiErrorPublicOutput(
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
		$formId = $formDetails[Config::FD_FORM_ID] ?? '';
		$response = $formDetails[Config::FD_RESPONSE_OUTPUT_DATA] ?? [];
		$validation = $response[Config::IARD_VALIDATION] ?? [];
		$status = $response[Config::IARD_STATUS] ?? Config::STATUS_ERROR;

		$disableFallbackEmail = false;

		// Output integrations validation issues.
		if ($validation) {
			$response[Config::IARD_VALIDATION] = $this->validator->getValidationLabelItems($validation, $formId);
			$disableFallbackEmail = true;
		}

		// Skip fallback email if integration is disabled.
		if (!$response[Config::IARD_IS_DISABLED] && $response[Config::IARD_STATUS] === Config::STATUS_ERROR) {
			// Prevent fallback email if we have validation errors parsed.
			if (!$disableFallbackEmail) {
				// Send fallback email.
				$this->getFormSubmitMailer()->sendFallbackIntegrationEmail($formDetails);
			}
		}

		$labelsOutput = $this->labels->getLabel($response[Config::IARD_MSG], $formId);
		$responseOutput = $response;

		// Output fake success and send fallback email.
		if ($response[Config::IARD_IS_DISABLED] && !$validation) {
			$this->getFormSubmitMailer()->sendFallbackIntegrationEmail($formDetails);

			$fakeResponse = ApiHelpers::getIntegrationSuccessInternalOutput($response);

			$labelsOutput = $this->labels->getLabel($fakeResponse[Config::IARD_MSG], $formId);
			$responseOutput = $fakeResponse;
		}

		$formDetails[Config::FD_RESPONSE_OUTPUT_DATA] = $responseOutput;

		if ($status === Config::STATUS_SUCCESS) {
			$successAdditionalData = $this->getIntegrationResponseSuccessOutputAdditionalData($formDetails);

			// Send email if it is configured in the backend.
			if ($response[Config::IARD_STATUS] === Config::STATUS_SUCCESS) {
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

			return ApiHelpers::getApiSuccessPublicOutput(
				$labelsOutput,
				\array_merge(
					$successAdditionalData['public'],
					$successAdditionalData['additional']
				),
				$response
			);
		}

		return ApiHelpers::getApiErrorPublicOutput(
			$labelsOutput,
			\array_merge(
				$this->getIntegrationResponseErrorOutputAdditionalData($formDetails),
				((isset($response[Config::IARD_VALIDATION])) ? [
					UtilsHelper::getStateResponseOutputKey('validation') => $response[Config::IARD_VALIDATION],
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
		$formId = $formDetails[Config::FD_FORM_ID] ?? '';
		$type = $formDetails[Config::FD_TYPE] ?? '';

		$output = [];

		// Tracking event name.
		$trackingEventName = FiltersOutputMock::getTrackingEventNameFilterValue($type, $formId)['data'];
		if ($trackingEventName) {
			$output[UtilsHelper::getStateResponseOutputKey('trackingEventName')] = $trackingEventName;
		}

		// Provide additional data to tracking attr.
		$trackingAdditionalData = FiltersOutputMock::getTrackingAdditionalDataFilterValue($type, $formId)['data'];
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
		$formId = $formDetails[Config::FD_FORM_ID] ?? '';
		$type = $formDetails[Config::FD_TYPE] ?? '';

		$output = [
			'private' => [
				// Set increment and add it to the output.
				UtilsHelper::getStateResponseOutputKey('incrementId') => FormsHelper::setIncrement($formId),

				// Add form ID to the output.
				UtilsHelper::getStateResponseOutputKey('formId') => $formId,
			],
			'public' => [],
		];

		// ORDER OF THE FUNCTIONS ARE IMPORTANT!

		// Set entries.
		$output = $this->setIntegrationResponseEntry($output, $formDetails);

		// Set hide options.
		$output = $this->setIntegrationResponseHideOptions($output, $formDetails);

		// Add success redirect variation.
		$output = $this->setIntegrationResponseVariation($output, $formDetails);

		// Success redirect url.
		$output = $this->setIntegrationResponseSuccessRedirectUrl($output, $formDetails);

		// Update created entry with additional values.
		$output = $this->setIntegrationResponseEntryUpdate($output, $formDetails);

		$finalOutput = [
			'private' => $output['private'],
			'public' => $output['public'],
			'additional' => $this->getIntegrationResponseAnyOutputAdditionalData($formDetails),
		];

		// Filter params.
		$filterName = HooksHelpers::getFilterName(['integrations', $type, 'beforeSuccessResponse']);
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

		$allowedTags = \apply_filters(Config::FILTER_SETTINGS_DATA, [])[SettingsMailer::SETTINGS_TYPE_KEY]['emailTemplateTags'] ?? [];

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
	 * Returns security class.
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
	 * Process custom result output data.
	 *
	 * @param array<string, mixed> $data Data from secure data.
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, mixed>
	 */
	private function processCustomResultOutputData(array $data, array $formDetails): array
	{
		$params = $formDetails[Config::FD_PARAMS] ?? [];
		$formId = $formDetails[Config::FD_FORM_ID] ?? '';
		$type = $formDetails[Config::FD_TYPE] ?? '';

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
				if (FormsHelper::getParamValue($fieldName, $params) === $fieldValue) {
					$outputFiles[] = $file;
					continue;
				}
			}

			$output['d'] = $outputFiles;
		}

		$filterName = HooksHelpers::getFilterName(['integrations', $type, 'afterCustomResultOutputProcess']);
		if (\has_filter($filterName)) {
			return \apply_filters($filterName, $output, $formDetails, $formId);
		}

		return $output;
	}

	/**
	 * Set integration response - entry.
	 *
	 * @param array<string, mixed> $output Output data.
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, mixed>
	 */
	private function setIntegrationResponseEntry(array $output, array $formDetails): array
	{
		$formId = $formDetails[Config::FD_FORM_ID] ?? '';

		if (!\apply_filters(SettingsEntries::FILTER_SETTINGS_IS_VALID_NAME, $formId)) {
			return $output;
		}

		$entryId = EntriesHelper::setEntryByFormDataRef($formDetails);
		if (!$entryId) {
			return $output;
		}

		$output['private'][UtilsHelper::getStateResponseOutputKey('entry')] = (string) $entryId;

		return $output;
	}

	/**
	 * Set integration response - entry update.
	 *
	 * @param array<string, mixed> $output Output data.
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, mixed>
	 */
	private function setIntegrationResponseEntryUpdate(array $output, array $formDetails): array
	{
		$formId = $formDetails[Config::FD_FORM_ID] ?? '';

		if (!\apply_filters(SettingsEntries::FILTER_SETTINGS_IS_VALID_NAME, $formId)) {
			return $output;
		}

		if (!isset($output['private'][UtilsHelper::getStateResponseOutputKey('entry')])) {
			return $output;
		}

		$entryData = EntriesHelper::getEntry($output['private'][UtilsHelper::getStateResponseOutputKey('entry')]);

		if (!$entryData) {
			return $output;
		}

		$entryNewData = $entryData['entryValue'] ?? [];

		if (
			SettingsHelpers::isSettingCheckboxChecked(SettingsEntries::SETTINGS_ENTRIES_SAVE_ADDITIONAL_VALUES_REDIRECT_URL_KEY, SettingsEntries::SETTINGS_ENTRIES_SAVE_ADDITIONAL_VALUES_KEY, $formId) &&
			isset($output['public'][UtilsHelper::getStateResponseOutputKey('successRedirectUrl')])
		) {
			$entryNewData[UtilsHelper::getStateResponseOutputKey('successRedirectUrl')] = $output['public'][UtilsHelper::getStateResponseOutputKey('successRedirectUrl')];
		}

		if (
			SettingsHelpers::isSettingCheckboxChecked(SettingsEntries::SETTINGS_ENTRIES_SAVE_ADDITIONAL_VALUES_VARIATIONS_KEY, SettingsEntries::SETTINGS_ENTRIES_SAVE_ADDITIONAL_VALUES_KEY, $formId) &&
			isset($output['public'][UtilsHelper::getStateResponseOutputKey('variation')])
		) {
			$entryNewData[UtilsHelper::getStateResponseOutputKey('variation')] = $output['public'][UtilsHelper::getStateResponseOutputKey('variation')];
		}

		if (
			SettingsHelpers::isSettingCheckboxChecked(SettingsEntries::SETTINGS_ENTRIES_SAVE_ADDITIONAL_VALUES_INCREMENT_ID_KEY, SettingsEntries::SETTINGS_ENTRIES_SAVE_ADDITIONAL_VALUES_KEY, $formId) &&
			isset($output['private'][UtilsHelper::getStateResponseOutputKey('incrementId')])
		) {
			$entryNewData[UtilsHelper::getStateResponseOutputKey('incrementId')] = $output['private'][UtilsHelper::getStateResponseOutputKey('incrementId')];
		}

		EntriesHelper::updateEntry($entryNewData, $output['private'][UtilsHelper::getStateResponseOutputKey('entry')]);

		return $output;
	}

	/**
	 * Set integration response - hide options.
	 *
	 * @param array<string, mixed> $output Output data.
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, mixed>
	 */
	private function setIntegrationResponseHideOptions(array $output, array $formDetails): array
	{
		$formId = $formDetails[Config::FD_FORM_ID] ?? '';

		// Hide global message on success.
		$hideGlobalMsgOnSuccess = SettingsHelpers::isSettingCheckboxChecked(SettingsGeneral::SETTINGS_HIDE_GLOBAL_MSG_ON_SUCCESS_KEY, SettingsGeneral::SETTINGS_HIDE_GLOBAL_MSG_ON_SUCCESS_KEY, $formId);
		if ($hideGlobalMsgOnSuccess) {
			$output['public'][UtilsHelper::getStateResponseOutputKey('hideGlobalMsgOnSuccess')] = $hideGlobalMsgOnSuccess;
		}

		// Hide form on success.
		$hideFormOnSuccess = SettingsHelpers::isSettingCheckboxChecked(SettingsGeneral::SETTINGS_HIDE_FORM_ON_SUCCESS_KEY, SettingsGeneral::SETTINGS_HIDE_FORM_ON_SUCCESS_KEY, $formId);
		if ($hideFormOnSuccess) {
			$output['public'][UtilsHelper::getStateResponseOutputKey('hideFormOnSuccess')] = $hideFormOnSuccess;
		}

		return $output;
	}

	/**
	 * Set integration response - variation.
	 *
	 * @param array<string, mixed> $output Output data.
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, mixed>
	 */
	private function setIntegrationResponseVariation(array $output, array $formDetails): array
	{
		$formId = $formDetails[Config::FD_FORM_ID] ?? '';
		$type = $formDetails[Config::FD_TYPE] ?? '';

		$variation = FiltersOutputMock::getVariationFilterValue($type, $formId, $formDetails)['data'];
		if (!$variation) {
			return $output;
		}

		$output['public'][UtilsHelper::getStateResponseOutputKey('variation')] = $variation;

		return $output;
	}

	/**
	 * Set integration response - success redirect url.
	 *
	 * @param array<string, mixed> $output Output data.
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, mixed>
	 */
	private function setIntegrationResponseSuccessRedirectUrl(array $output, array $formDetails): array
	{
		$formId = $formDetails[Config::FD_FORM_ID] ?? '';
		$type = $formDetails[Config::FD_TYPE] ?? '';

		$successRedirectUrl = FiltersOutputMock::getSuccessRedirectUrlFilterValue($type, $formId)['data'];
		if (!$successRedirectUrl) {
			return $output;
		}

		$redirectDataOutput = [];

		// Replace {field_name} with the actual value.
		foreach (\array_merge($formDetails[Config::FD_PARAMS], $formDetails[Config::FD_FILES]) as $param) {
			$fieldName = $param['name'] ?? '';
			$fieldValue = $param['value'] ?? '';
			$fieldType = $param['type'] ?? '';

			if ($fieldName === UtilsHelper::getStateParam('skippedParams')) {
				continue;
			}

			if ($fieldType === 'file') {
				$fieldValue = \array_map(
					static function (string $file) {
						$filename = \pathinfo($file, \PATHINFO_FILENAME);
						$extension = \pathinfo($file, \PATHINFO_EXTENSION);
						return "{$filename}.{$extension}";
					},
					$fieldValue
				);
			}

			if (\is_array($fieldValue)) {
				$fieldValue = \implode(', ', $fieldValue);
			}

			$successRedirectUrl = \str_replace("{" . $fieldName . "}", (string) $fieldValue, $successRedirectUrl);
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
		if ($formDetails[Config::FD_SECURE_DATA]) {
			$secureData = \json_decode(EncryptionHelpers::decryptor($formDetails[Config::FD_SECURE_DATA]) ?: '', true);

			// Redirect custom result output feature.
			$formsUseCustomResultOutputFeatureFilterName = HooksHelpers::getFilterName(['block', 'forms', 'useCustomResultOutputFeature']);
			if (\apply_filters($formsUseCustomResultOutputFeatureFilterName, false)) {
				$redirectDataOutput[UtilsHelper::getStateSuccessRedirectUrlKey('customResultOutput')] = $this->processCustomResultOutputData($secureData, $formDetails);
			}
		}

		// Redirect base url.
		$output['public'][UtilsHelper::getStateResponseOutputKey('successRedirectBaseUrl')] = $successRedirectUrl;

		// Redirect full url.
		$output['public'][UtilsHelper::getStateResponseOutputKey('successRedirectUrl')] = \add_query_arg(
			$redirectDataOutput ? [
				UtilsHelper::getStateSuccessRedirectUrlKey('data') => EncryptionHelpers::encryptor(\wp_json_encode($redirectDataOutput)),
			] : [],
			$successRedirectUrl
		);

		return $output;
	}
}
