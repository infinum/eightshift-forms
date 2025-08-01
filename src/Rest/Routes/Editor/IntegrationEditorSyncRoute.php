<?php

/**
 * The class to provide form builder json to block editor for integrations.
 *
 * @package EightshiftForms\Rest\Routes\Editor
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Editor;

use EightshiftForms\Integrations\IntegrationSyncInterface;
use EightshiftForms\Config\Config;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class IntegrationEditorSyncRoute
 */
class IntegrationEditorSyncRoute extends AbstractSimpleFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'sync';

	/**
	 * Instance variable for HubSpot form data.
	 *
	 * @var IntegrationSyncInterface
	 */
	protected $integrationSyncDiff;

	/**
	 * Create a new instance.
	 *
	 * @param SecurityInterface $security Inject security methods.
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param IntegrationSyncInterface $integrationSyncDiff Inject IntegrationSyncDiff which holds sync data.
	 */
	public function __construct(
		SecurityInterface $security,
		ValidatorInterface $validator,
		LabelsInterface $labels,
		IntegrationSyncInterface $integrationSyncDiff
	) {
		$this->security = $security;
		$this->validator = $validator;
		$this->labels = $labels;
		$this->integrationSyncDiff = $integrationSyncDiff;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/' . Config::ROUTE_PREFIX_INTEGRATION_EDITOR . '/' . self::ROUTE_SLUG;
	}

	/**
	 * Returns allowed methods for this route.
	 *
	 * @return string
	 */
	protected function getMethods(): string
	{
		return static::READABLE;
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
	 * @param array<string, mixed> $params Params passed from the request.
	 *
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(array $params): array
	{
		return [
			'id' => 'string',
		];
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $params Prepared params.
	 *
	 * @return array<string, mixed>
	 */
	protected function submitAction(array $params): array
	{
		$syncForm = $this->integrationSyncDiff->syncFormEditor($params['id'] ?? '', true);

		$status = $syncForm['status'] ?? '';
		$message = $syncForm['message'] ?? '';

		if ($status === Config::STATUS_ERROR) {
			throw new BadRequestException(
				$message,
				[
					AbstractBaseRoute::R_DEBUG => $syncForm,
					AbstractBaseRoute::R_DEBUG_KEY => 'syncFormError',
				]
			);
		}

		return [
			AbstractBaseRoute::R_MSG => $message,
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG => $syncForm,
				AbstractBaseRoute::R_DEBUG_KEY => 'syncFormSuccess',
			],
			AbstractBaseRoute::R_DATA => [
				UtilsHelper::getStateResponseOutputKey('editorSyncForm') => $syncForm,
			],
		];
	}
}
