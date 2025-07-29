<?php

/**
 * The class register route for public/admin form simple submitting endpoint like captcha, internal endpoints, etc.
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Exception\ForbiddenException;
use EightshiftForms\Exception\PermissionDeniedException;
use EightshiftForms\Exception\RequestLimitException;
use EightshiftForms\Exception\ValidationFailedException;
use EightshiftForms\Labels\LabelsInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftForms\Validation\ValidatorInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use EightshiftForms\Config\Config;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use EightshiftFormsVendor\EightshiftLibs\Rest\Routes\AbstractRoute;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class AbstractSimpleFormSubmit
 */
abstract class AbstractSimpleFormSubmit extends AbstractBaseRoute
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
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject formSubmitMailer methods.
	 *
	 * @return void
	 */
	public function __construct(
		ValidatorInterface $validator,
		LabelsInterface $labels,
		FormSubmitMailerInterface $formSubmitMailer,
	) {
		$this->validator = $validator;
		$this->labels = $labels;
		$this->formSubmitMailer = $formSubmitMailer;
	}

	/**
	 * Method that returns rest response
	 *
	 * @param WP_REST_Request $request Data got from endpoint url.
	 *
	 * @throws ValidationFailedException Validation failed.
	 * @throws RequestLimitException Request limit exceeded.
	 * @throws ForbiddenException Forbidden.
	 * @throws BadRequestException Bad request.
	 * @throws PermissionDeniedException Permission denied.
	 *
	 * @return WP_REST_Response
	 */
	public function routeCallback(WP_REST_Request $request)
	{
		// Try catch request.
		try {
			// If route is used for admin only, check if user has permission. (generally used for settings).
			if ($this->isRouteAdminProtected() && !$this->checkPermission(Config::CAP_SETTINGS)) {
				throw new PermissionDeniedException(
					[
						AbstractBaseRoute::R_DEBUG_KEY => 'permissionDenied',
					]
				);
			}

			// Prepare all data.
			$params = $this->prepareSimpleApiParams($request, $this->getMethods());

			// Validate mandatory params.
			if (!$this->getValidator()->validateMandatoryParams($params, $this->getMandatoryParams())) {
				throw new ValidationFailedException(
					$this->getValidatorLabels()->getLabel('validationMissingMandatoryParams'),
					[
						AbstractBaseRoute::R_DEBUG_KEY => 'validationMissingMandatoryParams',
					]
				);
			}

			// Do action.
			$return = $this->submitAction($params);

			return \rest_ensure_response(
				Helpers::getApiResponse(
					$return[AbstractBaseRoute::R_MSG] ?? $this->labels->getLabel('submitFallbackSuccess'),
					$return[AbstractBaseRoute::R_CODE] ?? AbstractRoute::API_RESPONSE_CODE_OK,
					AbstractRoute::STATUS_SUCCESS,
					$this->getResponseDataOutput(
						$return[AbstractBaseRoute::R_DATA] ?? [],
						$return[AbstractBaseRoute::R_DEBUG] ?? [],
						$request
					)
				)
			);
		} catch (ValidationFailedException | RequestLimitException | ForbiddenException | BadRequestException | PermissionDeniedException $e) {
			$return = [
				AbstractBaseRoute::R_MSG => $e->getMessage(),
				AbstractBaseRoute::R_CODE => $e->getCode(),
				AbstractBaseRoute::R_STATUS => AbstractRoute::STATUS_ERROR,
				AbstractBaseRoute::R_DATA => $this->getResponseDataOutput(
					$e->getData(),
					$e->getDebug(),
					$request
				),
			];

			// Return validation failed response.
			return \rest_ensure_response(
				Helpers::getApiResponse(
					$return[AbstractBaseRoute::R_MSG] ?: $this->labels->getLabel('submitFallbackError'),
					$return[AbstractBaseRoute::R_CODE] ?: AbstractRoute::API_RESPONSE_CODE_BAD_REQUEST,
					$return[AbstractBaseRoute::R_STATUS],
					$return[AbstractBaseRoute::R_DATA] ?: []
				)
			);
		}
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
	 * Returns form submit mailer class.
	 *
	 * @return FormSubmitMailerInterface
	 */
	protected function getFormSubmitMailer()
	{
		return $this->formSubmitMailer;
	}

	/**
	 * Get mandatory params.
	 *
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(): array
	{
		return [];
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $params Prepared params.
	 *
	 * @throws ValidationFailedException Validation failed.
	 * @throws RequestLimitException Request limit exceeded.
	 * @throws ForbiddenException Forbidden.
	 * @throws BadRequestException Bad request.
	 * @throws PermissionDeniedException Permission denied.
	 *
	 * @return array<string, mixed>
	 */
	abstract protected function submitAction(array $params): array;
}
