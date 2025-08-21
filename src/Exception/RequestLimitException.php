<?php

/**
 * RequestLimitException
 *
 * @package EightshiftForms\Exception
 */

declare(strict_types=1);

namespace EightshiftForms\Exception;

use Exception;
use EightshiftFormsVendor\EightshiftLibs\Exception\GeneralExceptionInterface;
use EightshiftFormsVendor\EightshiftLibs\Rest\Routes\AbstractRoute;

/**
 * RequestLimitException class.
 */
final class RequestLimitException extends Exception implements GeneralExceptionInterface
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
	 * Throws error if request limit is exceeded.
	 *
	 * @param string $message Message to show.
	 * @param array<int|string, mixed> $debug Debug data.
	 * @param array<int|string, mixed> $data Data that is wrong.
	 * @param int $code The code.
	 */
	public function __construct(
		string $message,
		array $debug = [],
		array $data = [],
		int $code = AbstractRoute::API_RESPONSE_CODE_TOO_MANY_REQUESTS,
	) {
		$this->data = $data;
		$this->debug = $debug;
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
