<?php

/**
 * The class register route for for integration submitting endpoint.
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Captcha\CaptchaInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Entries\EntriesHelper;
use EightshiftForms\Entries\SettingsEntries;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Exception\ForbiddenException;
use EightshiftForms\Exception\PermissionDeniedException;
use EightshiftForms\Exception\RequestLimitException;
use EightshiftForms\Exception\ValidationFailedException;
use EightshiftForms\General\SettingsGeneral;
use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Hooks\FiltersOutputMock;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Labels\LabelsInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftForms\Security\SecurityInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftForms\Validation\ValidatorInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftForms\Config\Config;
use EightshiftForms\Exception\DisabledIntegrationException;
use EightshiftForms\Helpers\DeveloperHelpers;
use EightshiftForms\Helpers\EncryptionHelpers;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\HooksHelpers;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Helpers\UploadHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use EightshiftFormsVendor\EightshiftLibs\Rest\Routes\AbstractRoute;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class AbstractIntegrationFormSubmit
 */
abstract class AbstractIntegrationFormSubmit extends AbstractBaseRoute
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
	 * Instance variable of enrichment data.
	 *
	 * @var EnrichmentInterface
	 */
	protected EnrichmentInterface $enrichment;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param SecurityInterface $security Inject security methods.
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject formSubmitMailer methods.
	 * @param EnrichmentInterface $enrichment Inject enrichment methods.
	 */
	public function __construct(
		SecurityInterface $security,
		ValidatorInterface $validator,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		FormSubmitMailerInterface $formSubmitMailer,
		EnrichmentInterface $enrichment
	) {
		$this->security = $security;
		$this->validator = $validator;
		$this->labels = $labels;
		$this->captcha = $captcha;
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
	protected const VALIDATION_ERROR_CODE = 'validationErrorCode';
	protected const VALIDATION_ERROR_DATA = 'validationErrorData';
	protected const VALIDATION_ERROR_MSG = 'validationErrorMsg';
	protected const VALIDATION_ERROR_OUTPUT = 'validationOutput';
	protected const VALIDATION_ERROR_IS_SPAM = 'validationIsSpam';

	protected const RESPONSE_OUTPUT_KEY = 'responseOutput';
	protected const RESPONSE_OUTPUT_VALIDATION_KEY = 'responseOutputValidation';
	protected const RESPONSE_OUTPUT_CAPTCHA_KEY = 'responseOutputCaptcha';
	protected const RESPONSE_SEND_FALLBACK_KEY = 'responseSendFallback';
	protected const RESPONSE_INTERNAL_KEY = 'responseInternalKey';

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
		try {
			// If route is used for admin only, check if user has permission. (generally used for settings).
			if ($this->isRouteAdminProtected() && !$this->checkPermission(Config::CAP_SETTINGS)) {
				throw new PermissionDeniedException(
					[
						AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_PERMISSION_DENIED,
					]
				);
			}

			// Prepare all data.
			$formDetails = $this->getFormDetailsApi($request);

			// Validate submit only when logged in.
			if ($this->getValidator()->validateSubmitOnlyLoggedIn($formDetails[Config::FD_FORM_ID] ?? '')) {
				throw new ForbiddenException(
					$this->getLabels()->getLabel('validationSubmitLoggedIn', $formDetails[Config::FD_FORM_ID] ?? ''),
					[
						AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_VALIDATION_SUBMIT_LOGGED_IN,
					]
				);
			}

			// Validate submit only once.
			if ($this->getValidator()->validateSubmitOnlyOnce($formDetails[Config::FD_FORM_ID] ?? '')) {
				throw new ForbiddenException(
					$this->getLabels()->getLabel('validationSubmitOnce', $formDetails[Config::FD_FORM_ID] ?? ''),
					[
						AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_VALIDATION_SUBMIT_ONCE,
					]
				);
			}

			// In case the form has missing itemId, type, formId, etc it is not configured correctly or it could be a unauthorized request.
			if (!$this->getValidator()->validateMandatoryParams($formDetails, $this->getMandatoryParams($formDetails))) {
				throw new ValidationFailedException(
					$this->getLabels()->getLabel('validationMissingMandatoryParams'),
					[
						AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_VALIDATION_MISSING_MANDATORY_PARAMS,
					]
				);
			}

			// Validate allowed number of requests.
			if ($this->shouldCheckSecurity()) {
				if (!$this->getSecurity()->isRequestValid($formDetails[Config::FD_TYPE])) {
					throw new RequestLimitException(
						$this->getLabels()->getLabel('validationSecurity'),
						[
							AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_VALIDATION_SECURITY,
						]
					);
				}
			}

			// Validate params.
			if ($this->shouldCheckParamsValidation()) {
				if ($validate = $this->getValidator()->validateParams($formDetails)) {
					throw new ValidationFailedException(
						$this->getLabels()->getLabel('validationGlobalMissingRequiredParams'),
						[
							AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_VALIDATION_PARAMS,
						],
						[
							UtilsHelper::getStateResponseOutputKey('validation') => $validate,
						]
					);
				}
			}

			// Validate captcha.
			if ($this->shouldCheckCaptcha()) {
				$this->getCaptcha()->check(
					$formDetails[Config::FD_CAPTCHA]['token'] ?? '',
					$formDetails[Config::FD_CAPTCHA]['action'] ?? '',
					$formDetails[Config::FD_CAPTCHA]['isEnterprise'] ?? 'false'
				);
			}

			// Map enrichment data.
			if ($this->shouldCheckEnrichment()) {
				$formDetails[Config::FD_PARAMS] = $this->getEnrichment()->mapEnrichmentFields($formDetails[Config::FD_PARAMS]);
			}

			// Add country to the form details.
			if ($this->shouldCheckCountry()) {
				$formDetails[Config::FD_COUNTRY] = $this->getRequestCountryCookie($request);
			}

			// Filter params.
			if ($this->shouldCheckFilterParams()) {
				$filterName = HooksHelpers::getFilterName(['integrations', $formDetails[Config::FD_TYPE], 'prePostParams']);
				if (\has_filter($filterName)) {
					$formDetails[Config::FD_PARAMS] = \apply_filters($filterName, $formDetails[Config::FD_PARAMS], $formDetails[Config::FD_FORM_ID]) ?? [];
				}
			}

			// Do action.
			$output = $this->submitAction($formDetails);

			$return = [
				AbstractBaseRoute::R_MSG => $output[AbstractBaseRoute::R_MSG] ?? $this->getLabels()->getLabel('genericSuccess'),
				AbstractBaseRoute::R_CODE => $output[AbstractBaseRoute::R_CODE] ?? AbstractRoute::API_RESPONSE_CODE_OK,
				AbstractBaseRoute::R_STATUS => AbstractRoute::STATUS_SUCCESS,
				AbstractBaseRoute::R_DATA => $this->getResponseDataOutput(
					$output[AbstractBaseRoute::R_DATA] ?? [],
					$output[AbstractBaseRoute::R_DEBUG] ?? [],
					$request
				),
			];

			return \rest_ensure_response(
				Helpers::getApiResponse(
					$return[AbstractBaseRoute::R_MSG],
					$return[AbstractBaseRoute::R_CODE],
					$return[AbstractBaseRoute::R_STATUS],
					$this->cleanUpDebugOutput($return[AbstractBaseRoute::R_DATA])
				)
			);
		} catch (DisabledIntegrationException $e) {
			$return = [
				AbstractBaseRoute::R_MSG => $e->getMessage() ?: $this->getLabels()->getLabel('genericSuccess'),
				AbstractBaseRoute::R_CODE => $e->getCode() ?: AbstractRoute::API_RESPONSE_CODE_OK,
				AbstractBaseRoute::R_STATUS => AbstractRoute::STATUS_SUCCESS,
				AbstractBaseRoute::R_DATA => $this->getResponseDataOutput(
					$e->getData(),
					$e->getDebug(),
					$request
				),
			];

			if ($this->shouldLogActivity($e->getDebug())) {
				$this->getFormSubmitMailer()->sendTroubleshootingEmail(
					$formDetails,
					$this->getDebugOutputLevel($return),
					// $this->getLabels()->getLabel('submitFallbackError'),
					// $this->getLabels()->getLabel('submitFallbackError')
				);
			}

			return \rest_ensure_response(
				Helpers::getApiResponse(
					$return[AbstractBaseRoute::R_MSG],
					$return[AbstractBaseRoute::R_CODE],
					$return[AbstractBaseRoute::R_STATUS],
					$this->cleanUpDebugOutput($return[AbstractBaseRoute::R_DATA])
				)
			);
		} catch (ValidationFailedException | RequestLimitException | ForbiddenException | BadRequestException | PermissionDeniedException $e) {
			$return = [
				AbstractBaseRoute::R_MSG => $e->getMessage() ?: $this->getLabels()->getLabel('submitFallbackError'),
				AbstractBaseRoute::R_CODE => $e->getCode() ?: AbstractRoute::API_RESPONSE_CODE_BAD_REQUEST,
				AbstractBaseRoute::R_STATUS => AbstractRoute::STATUS_ERROR,
				AbstractBaseRoute::R_DATA => $this->getResponseDataOutput(
					$e->getData(),
					$e->getDebug(),
					$request
				),
			];

			\dump($return);

			// Do action.
			// if ($data[self::RESPONSE_SEND_FALLBACK_KEY] ?? false) {
			// 	// Send fallback email.
			// 	$this->getFormSubmitMailer()->sendFallbackProcessingEmail(
			// 		$formDetails,
			// 		'',
			// 		'',
			// 		[
			// 			self::VALIDATION_ERROR_CODE => \esc_html($return['data'][self::VALIDATION_ERROR_CODE] ?? ''),
			// 			self::VALIDATION_ERROR_MSG => \esc_html($return['message']),
			// 			self::VALIDATION_ERROR_OUTPUT => $return['data'][self::VALIDATION_ERROR_OUTPUT] ?? '',
			// 			self::VALIDATION_ERROR_DATA => $return['data'][self::VALIDATION_ERROR_DATA] ?? '',
			// 		]
			// 	);
			// }

			// Return validation failed response.
			return \rest_ensure_response(
				Helpers::getApiResponse(
					$return[AbstractBaseRoute::R_MSG],
					$return[AbstractBaseRoute::R_CODE],
					$return[AbstractBaseRoute::R_STATUS],
					$this->cleanUpDebugOutput($return[AbstractBaseRoute::R_DATA])
				)
			);
		}
	}

	/**
	 * Check if params validation should be checked.
	 *
	 * @return bool
	 */
	protected function shouldCheckParamsValidation(): bool
	{
		if (DeveloperHelpers::isDeveloperSkipFormValidationActive()) {
			return false;
		}

		return true;
	}

	/**
	 * Check if security should be checked.
	 *
	 * @return bool
	 */
	protected function shouldCheckSecurity(): bool
	{
		return true;
	}

	/**
	 * Check if captcha should be checked.
	 *
	 * @return bool
	 */
	protected function shouldCheckCaptcha(): bool
	{
		if (DeveloperHelpers::isDeveloperSkipCaptchaActive()) {
			return false;
		}

		return true;
	}

	/**
	 * Check if enrichment should be checked.
	 *
	 * @return bool
	 */
	protected function shouldCheckEnrichment(): bool
	{
		return true;
	}

	/**
	 * Check if country should be checked.
	 *
	 * @return bool
	 */
	protected function shouldCheckCountry(): bool
	{
		return true;
	}

	/**
	 * Check if filter params should be checked.
	 *
	 * @return bool
	 */
	protected function shouldCheckFilterParams(): bool
	{
		return true;
	}

	/**
	 * Get IP address.
	 *
	 * @return string
	 */
	protected function getIpAddress(): string
	{
		static $ip = '';

		if (!$ip) {
			$ip = $this->getSecurity()->getIpAddress('hash');
		}

		return $ip;
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

		// Output integrations validation issues.
		if ($validation) {
			$response[Config::IARD_VALIDATION] = $this->getValidator()->getValidationLabelItems($validation, $formId);
		}

		if ($status === Config::STATUS_ERROR) {
			throw new BadRequestException(
				$this->getLabels()->getLabel($response[Config::IARD_MSG], $formId),
				[
					AbstractBaseRoute::R_DEBUG => $formDetails,
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_SUBMIT_INTEGRATION_ERROR,
				],
				array_merge(
					$this->getIntegrationResponseErrorOutputAdditionalData($formDetails),
					((isset($response[Config::IARD_VALIDATION])) ? [
						UtilsHelper::getStateResponseOutputKey('validation') => $response[Config::IARD_VALIDATION],
					] : [])
				)
			);
		}

		return $this->getIntegrationResponseSuccessOutput($formDetails);
	}

	/**
	 * Get integration response output on success.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, mixed>
	 */
	protected function getIntegrationResponseSuccessOutput(array $formDetails): array
	{
		$type = $formDetails[Config::FD_TYPE] ?? '';
		$formId = $formDetails[Config::FD_FORM_ID] ?? '';

		$successAdditionalData = $this->getIntegrationResponseSuccessOutputAdditionalData($formDetails);

		// Send email if it is configured in the backend.
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

		// Callback functions.
		$this->callIntegrationResponseSuccessCallback($formDetails, $successAdditionalData);

		// Set validation submit once.
		$this->getValidator()->setValidationSubmitOnce($formId);

		return [
			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel("{$type}Success"),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG => $formDetails,
				AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_SUBMIT_INTEGRATION_SUCCESS,
				AbstractBaseRoute::R_DEBUG_SUCCESS_ADDITIONAL_DATA => $successAdditionalData,
			],
			AbstractBaseRoute::R_DATA => \array_merge(
				$successAdditionalData['public'],
				$successAdditionalData['additional']
			),
		];
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
	protected function getLabels()
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
	 * Returns enrichment class.
	 *
	 * @return EnrichmentInterface
	 */
	protected function getEnrichment()
	{
		return $this->enrichment;
	}

	/**
	 * Returns form submit mailer class.
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

	/**
	 * Get country from request.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return string
	 */
	protected function getRequestCountryCookie(WP_REST_Request $request): string
	{
		$country = $request->get_header('cookie');
		if (!$country) {
			return '';
		}

		$countries = \explode('; ', $country);

		$country = \array_values(\array_filter($countries, static function (string $country) {
			return \str_contains($country, 'esForms-country');
		}))[0] ?? '';

		if (!$country) {
			return '';
		}

		$country = \explode('=', $country)[1] ?? '';

		if (!$country) {
			return '';
		}

		return $country;
	}

	/**
	 * Prepare file from request for later usage. Attach custom data to file array.
	 *
	 * @param array<string, mixed> $file File array from request.
	 * @param array<string, mixed> $params Params to use.
	 * @return array<string, mixed>
	 */
	protected function prepareFile(array $file, array $params): array
	{
		$file = $file['file'] ?? [];

		if (!$file) {
			return [];
		}

		return \array_merge(
			$file,
			[
				'id' => $params[UtilsHelper::getStateParam('fileId')]['value'] ?? '',
				'fieldName' => $params[UtilsHelper::getStateParam('name')]['value'] ?? '',
			]
		);
	}

	/**
	 * Prepare form details api data.
	 *
	 * @param mixed $request Data got from endpoint url.
	 *
	 * @return array<string, mixed>
	 */
	protected function getFormDetailsApi($request): array
	{
		$output = [];

		// Get params from request.
		$params = $this->prepareApiParams($request);

		// Get form id from params.
		$formId = $params['formId'] ?? '';

		// Get form type from params.
		$type = $params['type'] ?? '';

		// Get form directImport from params.
		if (isset($params['directImport'])) {
			return $this->getFormDetailsApiDirectImport($params);
		}

		// Get form settings for admin from params.
		$formSettingsType = $params['settingsType'] ?? '';

		// Manual populate output it admin settings our build it from form Id.
		if (
			$type === Config::SETTINGS_TYPE_NAME ||
			$type === Config::SETTINGS_GLOBAL_TYPE_NAME ||
			$type === Config::FILE_UPLOAD_ADMIN_TYPE_NAME
		) {
			// This provides filter name for setting.
			$settingsName = \apply_filters(Config::FILTER_SETTINGS_DATA, [])[$formSettingsType][$type] ?? '';

			$output[Config::FD_FORM_ID] = $formId;
			$output[Config::FD_TYPE] = $type;
			$output[Config::FD_ITEM_ID] = '';
			$output[Config::FD_INNER_ID] = '';
			$output[Config::FD_FIELDS_ONLY] = !empty($settingsName) ? \apply_filters($settingsName, $formId) : [];
		} else {
			$formDetails = GeneralHelpers::getFormDetails($formId);

			$output[Config::FD_FORM_ID] = $formId;
			$output[Config::FD_IS_VALID] = $formDetails[Config::FD_IS_VALID] ?? false;
			$output[Config::FD_IS_API_VALID] = $formDetails[Config::FD_IS_API_VALID] ?? false;
			$output[Config::FD_LABEL] = $formDetails[Config::FD_LABEL] ?? '';
			$output[Config::FD_ICON] = $formDetails[Config::FD_ICON] ?? '';
			$output[Config::FD_TYPE] = $formDetails[Config::FD_TYPE] ?? '';
			$output[Config::FD_ITEM_ID] = $formDetails[Config::FD_ITEM_ID] ?? '';
			$output[Config::FD_INNER_ID] = $formDetails[Config::FD_INNER_ID] ?? '';
			$output[Config::FD_FIELDS] = $formDetails[Config::FD_FIELDS] ?? [];
			$output[Config::FD_FIELDS_ONLY] = $formDetails[Config::FD_FIELDS_ONLY] ?? [];
			$output[Config::FD_FIELD_NAMES] = $formDetails[Config::FD_FIELD_NAMES] ?? [];
			$output[Config::FD_STEPS_SETUP] = $formDetails[Config::FD_STEPS_SETUP] ?? [];
		}

		// Populate params.
		$output[Config::FD_PARAMS] = $params['params'] ?? [];

		// Populate files from uploaded ID.
		$output[Config::FD_FILES] = $params['files'] ?? [];

		// Populate files on upload. Only populated on file upload.
		$output[Config::FD_FILES_UPLOAD] = $this->prepareFile($request->get_file_params(), $params['params'] ?? []);

		// Populate action.
		$output[Config::FD_SECURE_DATA] = $params['secureData'] ?? '';

		// Populate action.
		$output[Config::FD_ACTION] = $params['action'] ?? '';

		// Populate action external.
		$output[Config::FD_ACTION_EXTERNAL] = $params['actionExternal'] ?? '';

		// Populate step fields.
		$output[Config::FD_API_STEPS] = $params['apiSteps'] ?? [];

		// Get form captcha from params.
		$output[Config::FD_CAPTCHA] = $params['captcha'] ?? [];

		// Get form post Id from params.
		$output[Config::FD_POST_ID] = $params['postId'] ?? '';

		// Get form storage from params.
		$output[Config::FD_STORAGE] = \json_decode($params['storage'] ?? '', true) ?? [];

		// Set debug original params.
		$output[Config::FD_PARAMS_ORIGINAL] = \sanitize_text_field(\wp_json_encode($this->getRequestParams($request)));

		return $output;
	}

	/**
	 * Prepare form details api data for direct import.
	 *
	 * @param array<string, mixed> $params Params to use.
	 *
	 * @return array<string, mixed>
	 */
	private function getFormDetailsApiDirectImport(array $params): array
	{
		// Get form id from params.
		$formId = $params['formId'] ?? '';

		// Get form type from params.
		$type = $params['type'] ?? '';

		// Get form directImport from params.
		$output[Config::FD_DIRECT_IMPORT] = true;
		$output[Config::FD_TYPE] = $type;
		$output[Config::FD_FORM_ID] = $formId;
		$output[Config::FD_ITEM_ID] = $params['itemId'] ?? '';
		$output[Config::FD_INNER_ID] = $params['innerId'] ?? '';
		$output[Config::FD_POST_ID] = $params['postId'] ?? '';
		$output[Config::FD_PARAMS] = $params['params'] ?? [];
		$output[Config::FD_FILES] = $params['files'] ?? [];

		return $output;
	}

	/**
	 * Convert JS FormData object to usable data in php.
	 *
	 * @param WP_REST_Request $request $request Data got from endpoint url.
	 * @param string $type Request type.
	 *
	 * @return array<string, mixed>
	 */
	protected function prepareApiParams(WP_REST_Request $request, string $type = self::CREATABLE): array
	{
		// Get params.
		$params = $this->getRequestParams($request, $type);

		// Bailout if there are no params.
		if (!$params) {
			return [];
		}

		// Skip any manipulations if direct param is set.
		$paramsOutput = \array_map(
			static function ($item) {
				// Check if array then output only value that is not empty.
				if (\is_array($item)) {
					// Loop all items and decode.
					$inner = \array_map(
						static function ($item) {
							return \json_decode(\sanitize_text_field($item), true);
						},
						$item
					);

					// Find all items where value is not empty.
					$innerNotEmpty = \array_values(
						\array_filter(
							$inner,
							static function ($innerItem) {
								return !empty($innerItem['value']);
							}
						)
					);

					// Fallback if everything is empty.
					if (!$innerNotEmpty) {
						return $inner[0];
					}

					// If multiple values this is checkbox.
					if (\count($innerNotEmpty) > 1) {
						$multiple = \array_values(
							\array_map(
								static function ($item) {
									return $item['value'];
								},
								$innerNotEmpty
							)
						);

						// Append values to the first value.
						$innerNotEmpty[0]['value'] = $multiple;

						return $innerNotEmpty[0];
					}

					// If one item then this is probably radio.
					return $innerNotEmpty[0];
				}

				// Try to clean the string.
				// Parts of the code taken from https://developer.wordpress.org/reference/functions/_sanitize_text_fields/.
				$item = \wp_check_invalid_utf8($item);
				$item = \wp_strip_all_tags($item);

				$filtered = \trim($item);

				// Remove percent-encoded characters.
				$found = false;
				while (\preg_match('/%[a-f0-9]{2}/i', $filtered, $match)) {
					$filtered = \str_replace($match[0], '', $filtered);
					$found = true;
				}

				if ($found) {
					// Strip out the whitespace that may now exist after removing percent-encoded characters.
					$filtered = \trim(\preg_replace('/ +/', ' ', $filtered));
				}

				// Decode value.
				return \json_decode($filtered, true);
			},
			$params
		);

		$output = [];

		// These are the required keys for each field.
		$reqKeys = [
			'name' => '',
			'value' => '',
			'type' => '',
			'custom' => '',
			'typeCustom' => '',
		];

		$paramsBroken = false;

		// If this route is for public form prepare all params.
		foreach ($paramsOutput as $key => $value) {
			// Check if all required keys are present and bail out if not.
			if (!\is_array($value) || \array_diff_key($reqKeys, $value)) {
				$paramsBroken = true;
				break;
			}

			switch ($key) {
				// Used for direct import from settings.
				case UtilsHelper::getStateParam('direct'):
					$output['directImport'] = (bool) $value['value'];
					break;
				// Used for direct import from settings.
				case UtilsHelper::getStateParam('itemId'):
					$output['itemId'] = $value['value'];
					break;
				// Used for direct import from settings.
				case UtilsHelper::getStateParam('innerId'):
					$output['innerId'] = $value['value'];
					break;
				case UtilsHelper::getStateParam('formId'):
					$output['formId'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case UtilsHelper::getStateParam('postId'):
					$output['postId'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case UtilsHelper::getStateParam('type'):
					$output['type'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case UtilsHelper::getStateParam('secureData'):
					$output['secureData'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case UtilsHelper::getStateParam('action'):
					$output['action'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case UtilsHelper::getStateParam('captcha'):
					$output['captcha'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case UtilsHelper::getStateParam('actionExternal'):
					$output['actionExternal'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case UtilsHelper::getStateParam('settingsType'):
					$output['settingsType'] = $value['value'];
					$output['params'][$key] = $value;
					break;
				case UtilsHelper::getStateParam('storage'):
					$output['storage'] = $value['value'];
					$value['value'] = (!empty($value['value'])) ? \json_decode($value['value'], true) : [];
					$output['params'][$key] = $value;
					break;
				case UtilsHelper::getStateParam('steps'):
					$output['apiSteps'] = [
						'fields' => $value['value'],
						'current' => $value['custom'],
					];
					break;
				default:
					// All other "normal" fields.
					$fieldType = $value['type'] ?? '';
					$fieldValue = $value['value'] ?? '';
					$fieldName = $value['name'] ?? '';

					if (!$fieldName) {
						break;
					}

					// File.
					if ($fieldType === 'file') {
						$output['files'][$key] = $value;

						if (!$fieldValue) {
							$output['files'][$key]['value'] = [];
						} else {
							if (!\is_array($fieldValue)) {
								$fieldValue = [$fieldValue];
							}

							$output['files'][$key]['value'] = \array_map(
								static function (string $file) {
									return UploadHelpers::getFilePath($file);
								},
								$fieldValue
							);
						}
						break;
					}

					// Rating.
					if ($fieldType === 'rating' && $fieldValue === '0') {
						$value['value'] = '';
					}

					// Checkbox.
					if ($fieldType === 'checkbox') {
						if (!$fieldValue) {
							$value['value'] = [];
						} else {
							$value['value'] = \is_string($fieldValue) ? [$fieldValue] : $fieldValue;
						}
					}

					$output['params'][$key] = $value;

					break;
			}
		}

		// Bail out if we have a broken param.
		if ($paramsBroken) {
			return [];
		}

		return $output;
	}
}
