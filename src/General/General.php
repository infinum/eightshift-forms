<?php

/**
 * File containing an class for general configuration.
 *
 * @package EightshiftForms\General
 */

declare(strict_types=1);

namespace EightshiftForms\General;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Class General
 */
class General implements ServiceInterface
{
	/**
	 * Default timeout for all http requests.
	 *
	 * @var int
	 */
	public const HTTP_REQUEST_TIMEOUT_DEFAULT = 30;

	/**
	 * Register all hooks.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter('http_request_args', [$this, 'getHttpRequestArgs']);
	}

	/**
	 * Return http request args.
	 *
	 * @param array<int, mixed> $args Arguments from core.
	 *
	 * @return array<int, mixed>
	 */
	public function getHttpRequestArgs(array $args): array
	{
		$filterName = UtilsHooksHelper::getFilterName(['general', 'httpRequestTimeout']);

		$args['timeout'] = \apply_filters($filterName, self::HTTP_REQUEST_TIMEOUT_DEFAULT);

		return $args;
	}
}
