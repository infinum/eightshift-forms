<?php

/**
 * Integration items route for Pardot (form handlers dropdown).
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Pardot
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Pardot;

use EightshiftForms\Integrations\Pardot\PardotClientInterface;
use EightshiftForms\Integrations\Pardot\SettingsPardot;
use EightshiftForms\Config\Config;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidatorInterface;
use Override;

/**
 * Class IntegrationItemsPardotRoute
 */
class IntegrationItemsPardotRoute extends AbstractSimpleFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsPardot::SETTINGS_TYPE_KEY;

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
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param PardotClientInterface $pardotClient Inject Pardot client.
	 */
	public function __construct(
		SecurityInterface $security,
		ValidatorInterface $validator,
		LabelsInterface $labels,
		protected PardotClientInterface $pardotClient
	) {
		$this->security = $security;
		$this->validator = $validator;
		$this->labels = $labels;
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
	 * @throws BadRequestException If Pardot is not configured.
	 *
	 * @return array<string, mixed>
	 */
	protected function submitAction(array $params): array
	{
		if (!\apply_filters(SettingsPardot::FILTER_SETTINGS_GLOBAL_NAME, false)) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->getLabels()->getLabel('globalNotConfigured'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => 'integrationItemsGlobalNotConfigured',
				]
			);
			// phpcs:enable
		}

		$items = $this->pardotClient->getItems();

		if ($items === []) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->getLabels()->getLabel('integrationItemsMissing'),
				[
					AbstractBaseRoute::R_DEBUG => $items,
					AbstractBaseRoute::R_DEBUG_KEY => 'integrationItemsMissingItems',
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
			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('integrationItemsSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG => $items,
				AbstractBaseRoute::R_DEBUG_KEY => 'integrationItemsSuccess',
			],
			AbstractBaseRoute::R_DATA => [
				UtilsHelper::getStateResponseOutputKey('editorIntegrationItems') => $items,
			],
		];
	}
}
