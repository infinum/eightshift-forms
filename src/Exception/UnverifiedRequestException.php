<?php

/**
 * UnverifiedRequestException
 *
 * @package EightshiftForms\Exception
 */

declare(strict_types=1);

namespace EightshiftForms\Exception;

use EightshiftFormsVendor\EightshiftLibs\Exception\GeneralExceptionInterface;
use InvalidArgumentException;

/**
 * UnverifiedRequestException class.
 */
final class UnverifiedRequestException extends InvalidArgumentException implements GeneralExceptionInterface
{
	/**
	 * Throws error if request is not verified.
	 *
	 * @param string $message Message to show.
	 * @param array<int|string, mixed> $data Data that is wrong.
	 */
	public function __construct(
		string $message,
		/**
		 * Internal data.
		 */
		private readonly array $data = []
	) {
		parent::__construct($message);
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
