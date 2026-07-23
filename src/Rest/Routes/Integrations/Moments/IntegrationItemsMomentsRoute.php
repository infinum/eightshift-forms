<?php

/**
 * The class to provide integration items from cache.
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Moments
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Moments;

use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Moments\SettingsMoments;
use EightshiftForms\Config\Config;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Labels\Labels;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidatorInterface;
use Override;

/**
 * Class IntegrationItemsMomentsRoute
 */
class IntegrationItemsMomentsRoute extends AbstractSimpleFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsMoments::SETTINGS_TYPE_KEY;

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/' . Config::ROUTE_PREFIX_INTEGRATION_ITEMS . '/' . self::ROUTE_SLUG;
	}

	/**
	 * Create a new instance that injects classes
	 *
	 * @param SecurityInterface $security Inject security methods.
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param ClientInterface $momentsClient Inject Moments which holds Moments connect data.
	 */
	public function __construct(
		SecurityInterface $security,
		ValidatorInterface $validator,
		protected ClientInterface $momentsClient
	) {
		$this->security = $security;
		$this->validator = $validator;
	}

	/**
	 * Returns allowed methods for this route.
	 */
	#[Override]
	protected function getMethods(): string
	{
		return static::READABLE;
	}

	/**
	 * Check if the route is admin protected.
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
		return [];
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $params Prepared params.
	 *
	 * @throws BadRequestException If integration items are missing.
	 *
	 * @return array<string, mixed>
	 */
	protected function submitAction(array $params): array
	{
		// Check if global settings is valid.
		if (!\apply_filters(SettingsMoments::FILTER_SETTINGS_GLOBAL_NAME, false)) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				Labels::getLabel(Labels::LABEL_SETTINGS_GLOBAL_NOT_CONFIGURED),
				[
					AbstractBaseRoute::R_DEBUG_KEY => Labels::LABEL_SETTINGS_GLOBAL_NOT_CONFIGURED,
				]
			);
			// phpcs:enable
		}

		$items = $this->momentsClient->getItems();

		if ($items === []) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				Labels::getLabel(Labels::LABEL_INTEGRATION_ITEMS_MISSING),
				[
					AbstractBaseRoute::R_DEBUG => $items,
					AbstractBaseRoute::R_DEBUG_KEY => Labels::LABEL_INTEGRATION_ITEMS_MISSING,
				]
			);
			// phpcs:enable
		}

		$items = \array_filter(\array_values(\array_map(
			static function (array $item) {
				$id = $item['id'] ?? '';

				if ($id) {
					return [
						'label' => $item['title'] ?? \__('No title', 'eightshift-forms'),
						'value' => $id,
					];
				}
			},
			$items
		)));

		return [
			AbstractBaseRoute::R_MSG => Labels::getLabel(Labels::LABEL_INTEGRATION_ITEMS_SUCCESS),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG => $items,
				AbstractBaseRoute::R_DEBUG_KEY => Labels::LABEL_INTEGRATION_ITEMS_SUCCESS,
			],
			AbstractBaseRoute::R_DATA => [
				UtilsHelper::getStateResponseOutputKey('editorIntegrationItems') => $items,
			],
		];
	}
}
