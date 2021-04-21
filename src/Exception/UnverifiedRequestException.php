<?php

/**
 * File missing data in filter exception
 *
 * @package EightshiftForms\Exception
 */

declare(strict_types=1);

namespace EightshiftForms\Exception;

use EightshiftLibs\Exception\GeneralExceptionInterface;

/**
 * UnverifiedRequestException class.
 */
class UnverifiedRequestException extends \RuntimeException implements GeneralExceptionInterface
{

  /**
   * Data to expose.
   *
   * @var array
   */
	private $data = [];

  /**
   * Constructs object
   *
   * @param array $data Rest response array to expose.
   */
	public function __construct(array $data = [])
	{
		$this->data = $data;
		parent::__construct('UnverifiedRequestException');
	}

  /**
   * $this->data getter.
   *
   * @return array
   */
	public function getData(): array
	{
		return $this->data;
	}
}
