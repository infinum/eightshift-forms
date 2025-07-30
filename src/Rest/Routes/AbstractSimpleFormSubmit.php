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
use EightshiftForms\Security\SecurityInterface;
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
	 * @param SecurityInterface $security Inject security methods.
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject formSubmitMailer methods.
	 *
	 * @return void
	 */
	public function __construct(
		SecurityInterface $security,
		ValidatorInterface $validator,
		LabelsInterface $labels,
		FormSubmitMailerInterface $formSubmitMailer,
	) {
		$this->security = $security;
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
			if (!$this->getValidator()->validateMandatoryParams($params, $this->getMandatoryParams($params))) {
				throw new ValidationFailedException(
					$this->getLabels()->getLabel('validationMissingMandatoryParams'),
					[
						AbstractBaseRoute::R_DEBUG_KEY => 'validationMissingMandatoryParams',
					]
				);
			}

			// Do action.
			$output = $this->submitAction($params);

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
	 * Returns validator class.
	 *
	 * @return ValidatorInterface
	 */
	protected function getValidator()
	{
		return $this->validator;
	}

	/**
	 * Returns labels class.
	 *
	 * @return LabelsInterface
	 */
	protected function getLabels()
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
	 * Convert JS FormData object to usable data in php.
	 *
	 * @param WP_REST_Request $request $request Data got from endpoint url.
	 * @param string $type Request type.
	 *
	 * @return array<string, mixed>
	 */
	protected function prepareSimpleApiParams(WP_REST_Request $request, string $type = self::CREATABLE): array
	{
		// Get params.
		$params = $this->getRequestParams($request, $type);

		// Bailout if there are no params.
		if (!$params) {
			return [];
		}

		return \array_map(
			static function ($item) {
				return \sanitize_text_field($item);
			},
			$params
		);
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
