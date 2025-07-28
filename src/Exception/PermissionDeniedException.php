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
	 * Throws error if user has no permission.
	 *
	 * @param string $message Message to show.
	 * @param array<int|string, mixed> $data Data that is wrong.
	 * @param int $code The code.
	 */
	public function __construct(
		string $message,
		array $data = [],
		int $code = AbstractRoute::API_RESPONSE_CODE_UNAUTHORIZED
	) {
		$this->data = $data;
		parent::__construct($message, $code);
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
}
