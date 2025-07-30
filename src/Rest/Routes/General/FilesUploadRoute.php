<?php

/**
 * The class register route for public form submitting endpoint - files upload.
 *
 * @package EightshiftForms\Rest\Routes\General;
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\General;

use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Config\Config;
use EightshiftForms\Exception\ValidationFailedException;
use EightshiftForms\Helpers\DeveloperHelpers;
use EightshiftForms\Helpers\UploadHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Rest\Routes\AbstractIntegrationFormSubmit;

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
	 *
	 * @return string
	 */
	protected function routeGetType(): string
	{
		return self::ROUTE_TYPE_FILE;
	}

	/**
	 * Check if the route is admin protected.
	 *
	 * @return boolean
	 */
	protected function isRouteAdminProtected(): bool
	{
		return true;
	}

	/**
	 * Get mandatory params.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(array $params): array
	{
		return [
			Config::FD_FORM_ID => 'string',
			Config::FD_POST_ID => 'string',
			Config::FD_ITEM_ID => 'string',
		];
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return mixed
	 */
	protected function submitAction(array $formDetails)
	{
		// Validate files.
		if (!DeveloperHelpers::isDeveloperSkipFormValidationActive()) {
			$validate = $this->getValidator()->validateFiles($formDetails);

			if ($validate) {
				throw new ValidationFailedException(
					$this->getLabels()->getLabel('validationGlobalMissingRequiredParams'),
					[
						self::RESPONSE_OUTPUT_KEY => [
							self::RESPONSE_OUTPUT_VALIDATION_KEY => $validate,
						],
						self::RESPONSE_INTERNAL_KEY => 'validationFileUploadMissingRequiredParams',
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
			throw new ValidationFailedException(
				$this->getLabels()->getLabel('validationGlobalMissingRequiredParams'),
				[
					self::RESPONSE_OUTPUT_KEY => [
						$uploadFileId => $this->getLabels()->getLabel('validationFileUpload'),
					],
					self::RESPONSE_SEND_FALLBACK_KEY => true,
					self::RESPONSE_INTERNAL_KEY => 'validationFileUploadProcessError',
				]
			);
		}

		// Finish.
		return \rest_ensure_response(
			ApiHelpers::getApiSuccessPublicOutput(
				$this->getLabels()->getLabel('validationFileUploadSuccess'),
				[
					UtilsHelper::getStateResponseOutputKey('file') => $formDetails[Config::FD_FILES_UPLOAD]['id'] ?? '',
				],
				[
					'formDetails' => $formDetails,
				]
			)
		);
	}
}
