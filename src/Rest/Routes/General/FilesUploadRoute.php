<?php

/**
 * The class register route for public form submitting endpoint - files upload.
 *
 * @package EightshiftForms\Rest\Routes\General;
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\General;

use EightshiftForms\Config\Config;
use EightshiftForms\Exception\ValidationFailedException;
use EightshiftForms\Helpers\DeveloperHelpers;
use EightshiftForms\Helpers\UploadHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractIntegrationFormSubmit;
use EightshiftForms\Troubleshooting\SettingsFallback;
use Override;

/**
 * Class FilesUploadRoute
 */
class FilesUploadRoute extends AbstractIntegrationFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'files';

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return self::ROUTE_SLUG;
	}

	/**
	 * Detect what type of route it is.
	 */
	#[Override]
	protected function routeGetType(): string
	{
		return self::ROUTE_TYPE_FILE;
	}

	/**
	 * Check if the route is admin protected.
	 */
	protected function isRouteAdminProtected(): bool
	{
		return false;
	}

	/**
	 * Check if enrichment should be checked.
	 */
	#[Override]
	protected function shouldCheckEnrichment(): bool
	{
		return false;
	}

	/**
	 * Check if country should be checked.
	 */
	#[Override]
	protected function shouldCheckCountry(): bool
	{
		return false;
	}

	/**
	 * Check if the route should check captcha.
	 */
	#[Override]
	protected function shouldCheckCaptcha(): bool
	{
		return false;
	}

	/**
	 * Check if params validation should be checked.
	 */
	#[Override]
	protected function shouldCheckParamsValidation(): bool
	{
		return false;
	}

	/**
	 * Get mandatory params.
	 *
	 * @param array<string, mixed> $params Params passed from the request.
	 *
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(array $params): array
	{
		$type = $params[Config::FD_TYPE] ?? '';

		if ($type === Config::FILE_UPLOAD_ADMIN_TYPE_NAME) {
			return [];
		}

		return [
			Config::FD_FORM_ID => 'string',
			Config::FD_POST_ID => 'string',
			Config::FD_PARAMS => 'array',
			Config::FD_FILES_UPLOAD => 'array',
		];
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @throws ValidationFailedException If files are not valid.
	 *
	 * @return array<string, mixed>
	 */
	protected function submitAction(array $formDetails): array
	{
		// Manual reference for validation is only used in admin as there are no editor form builder to get the correct reference.
		$manualValidationReference = $this->getManualValidationReferenceByFormType($formDetails);

		// Validate files.
		if (!DeveloperHelpers::isDeveloperSkipFormValidationActive() && $validate = $this->getValidator()->validateFiles($formDetails, $manualValidationReference)) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new ValidationFailedException(
				$this->getLabels()->getLabel('validationGlobalMissingRequiredParams'),
				[
					AbstractBaseRoute::R_DEBUG => $formDetails,
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_VALIDATION_FILES,
				],
				[
					UtilsHelper::getStateResponseOutputKey('validation') => $validate,
				]
			);
			// phpcs:enable
		}

		$extraMimes = ($formDetails[Config::FD_TYPE] ?? '') === Config::FILE_UPLOAD_ADMIN_TYPE_NAME
			? Config::FILE_UPLOAD_ADMIN_EXTRA_MIMES
			: [];

		$uploadFile = UploadHelpers::uploadFile($formDetails[Config::FD_FILES_UPLOAD], $extraMimes);
		$uploadError = $uploadFile['errorOutput'] ?? '';
		$uploadFileId = $formDetails[Config::FD_FILES_UPLOAD]['id'] ?? '';

		// Upload files to temp folder.
		$formDetails[Config::FD_FILES_UPLOAD] = $uploadFile;

		if (UploadHelpers::isUploadError((string) $uploadError)) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new ValidationFailedException(
				$this->getLabels()->getLabel('validationGlobalMissingRequiredParams'),
				[
					AbstractBaseRoute::R_DEBUG => $formDetails,
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_FILES_UPLOAD_ERROR,
				],
				[
					UtilsHelper::getStateResponseOutputKey('validation') => [
						$uploadFileId => $this->getLabels()->getLabel('validationFileUpload'),
					],
				]
			);
			// phpcs:enable
		}

		return [
			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('validationFileUploadSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG => $formDetails,
				AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_FILES_UPLOAD_SUCCESS,
			],
			AbstractBaseRoute::R_DATA => [
				UtilsHelper::getStateResponseOutputKey('fileName') => $uploadFile['outputName'] ?? '',
			],
		];
	}

	/**
	 * Get manual validation reference.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, string>
	 */
	private function getManualValidationReferenceByFormType(array $formDetails): array
	{
		if ($formDetails[Config::FD_TYPE] === Config::FILE_UPLOAD_ADMIN_TYPE_NAME) {
			return [
				'accept' => 'json',
			];
		}

		return [];
	}
}
