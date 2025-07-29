<?php

/**
 * The class register route for debug encrypt testing endpoint
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Helpers\EncryptionHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;

/**
 * Class DebugEncryptRoute
 */
class DebugEncryptRoute extends AbstractSimpleFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'debug-encrypt';

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
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(): array
	{
		return [
			'type' => 'string',
			'data' => 'string',
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
		$type = $params['type'] ?? '';
		$data = $params['data'] ?? '';


		if ($type === 'encrypt') {
			$output = EncryptionHelpers::encryptor($data);
		} else {
			$output = EncryptionHelpers::decryptor($data);
		}

		if (!$output) {
			throw new BadRequestException(
				$type === 'encrypt' ? $this->labels->getLabel('encryptFailed') : $this->labels->getLabel('decryptFailed'),
				[
					AbstractBaseRoute::R_DEBUG => $output,
					AbstractBaseRoute::R_DEBUG_KEY => 'encryptFailed',
				]
			);
		}

		// Finish.
		return [
			AbstractBaseRoute::R_MSG => $type === 'encrypt' ? $this->labels->getLabel('encryptSuccess') : $this->labels->getLabel('decryptSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG => $output,
				AbstractBaseRoute::R_DEBUG_KEY => $type === 'encrypt' ? 'encryptSuccess' : 'decryptSuccess',
			],
			AbstractBaseRoute::R_DATA => [
				UtilsHelper::getStateResponseOutputKey('adminEncrypt') => $output,
			],
		];
	}
}
