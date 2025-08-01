<?php

/**
 * PermissionDeniedException
 *
 * @package EightshiftForms\Exception
 */

declare(strict_types=1);

namespace EightshiftForms\Exception;

use EightshiftFormsVendor\EightshiftLibs\Exception\GeneralExceptionInterface;
use EightshiftFormsVendor\EightshiftLibs\Rest\Routes\AbstractRoute;
use Exception;

/**
 * PermissionDeniedException class.
 */
final class PermissionDeniedException extends Exception implements GeneralExceptionInterface
{
	/**
	 * Internal data.
	 *
	 * @var array<int|string, mixed>
	 */
	private $data = [];

	/**
	 * Internal debug data.
	 *
	 * @var array<int|string, mixed>
	 */
	private $debug = [];

	/**
	 * Throws error if user has no permission.
	 *
	 * @param array<int|string, mixed> $debug Debug data.
	 * @param array<int|string, mixed> $data Data that is wrong.
	 */
	public function __construct(
		array $debug = [],
		array $data = []
	) {
		$this->data = $data;
		$this->debug = $debug;
		parent::__construct(\__('You don\'t have enough permissions to perform this action!', 'eightshift-forms'), AbstractRoute::API_RESPONSE_CODE_UNAUTHORIZED);
	}

	/**
	 * Get exception data
	 *
	 * @return array<int|string, mixed>
	 */
	public function getData(): array
	{
		return $this->data;
	}

	/**
	 * Get exception debug data
	 *
	 * @return array<int|string, mixed>
	 */
	public function getDebug(): array
	{
		return $this->debug;
	}
}
