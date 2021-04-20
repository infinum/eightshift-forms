<?php

/**
 * InvalidBuckarooResponseException class.
 *
 * @package EightshiftForms\Integrations\Buckaroo\Exceptions
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Buckaroo\Exceptions;

use EightshiftLibs\Exception\GeneralExceptionInterface;

/**
 * InvalidBuckarooResponseException class.
 */
class InvalidBuckarooResponseException extends \RuntimeException implements GeneralExceptionInterface
{

  /**
   * Message to throw.
   *
   * @var string
   */
	private $error_message = '';

  /**
   * Constructs object
   *
   * @param string $error_message Message to throw.
   */
	public function __construct(string $error_message)
	{
		$this->error_message = $error_message;
		parent::__construct('InvalidBuckarooResponseException');
	}

  /**
   * $this->error_message getter.
   *
   * @return string
   */
	public function get_error_message(): string
	{
		return $this->error_message;
	}
}
